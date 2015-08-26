<?php
namespace Pangaea\Item;

use \Pangaea\Date;
use \Pangaea\PangaeaException;
use \Pangaea\RenderableInterface;
use \Pangaea\Xml;
use \Pangaea\Attribute\NameValueAttribute;

class ItemLogistics implements RenderableInterface
{
    /**
     * Unit Cost.
     *
     * @var array
     */
    private $unitCost = [
        'amount' => null,
        'unit'   => null,
    ];

    /**
     * Legacy Distributor ID.
     *
     * @var mixed
     */
    private $legacyDistributorId;

    /**
     * Ship Node Supply.
     *
     * @var array
     */
    private $shipNodeSupply = [
        'mdsfamId'      => null,
        'vendorStockId' => null,
    ];

    /**
     * Preferred Distributors.
     *
     * @var array
     */
    private $preferredDistributors = [];

    /**
     * Valid Unit Cost Currencies.
     *
     * @const
     */
    const UNIT_COST_CURRENCIES = [
      'AED',
      'AFN',
      'ALL',
      'AMD',
      'ANG',
      'AOA',
      'ARS',
      'AUD',
      'AWG',
      'AZN',
      'BAM',
      'BBD',
      'BDT',
      'BGN',
      'BHD',
      'BIF',
      'BMD',
      'BND',
      'BOB',
      'BRL',
      'BSD',
      'BTN',
      'BWP',
      'BYR',
      'BZD',
      'CAD',
      'CDF',
      'CHF',
      'CLP',
      'CNY',
      'COP',
      'CRC',
      'CUP',
      'CVE',
      'CZK',
      'DJF',
      'DKK',
      'DOP',
      'DZD',
      'EGP',
      'ERN',
      'ETB',
      'EUR',
      'FJD',
      'FKP',
      'GBP',
      'GEL',
      'GHS',
      'GIP',
      'GMD',
      'GNF',
      'GTQ',
      'GYD',
      'HKD',
      'HNL',
      'HRK',
      'HTG',
      'HUF',
      'IDR',
      'ILS',
      'INR',
      'IQD',
      'IRR',
      'ISK',
      'JMD',
      'JOD',
      'JPY',
      'KES',
      'KGS',
      'KHR',
      'KMF',
      'KPW',
      'KRW',
      'KWD',
      'KYD',
      'KZT',
      'LAK',
      'LBP',
      'LKR',
      'LRD',
      'LSL',
      'LTL',
      'LVL',
      'LYD',
      'MAD',
      'MDL',
      'MGA',
      'MKD',
      'MMK',
      'MNT',
      'MOP',
      'MRO',
      'MUR',
      'MVR',
      'MWK',
      'MXN',
      'MYR',
      'MZN',
      'NAD',
      'NGN',
      'NIO',
      'NOK',
      'NPR',
      'NZD',
      'OMR',
      'PAB',
      'PEN',
      'PGK',
      'PHP',
      'PKR',
      'PLN',
      'PYG',
      'QAR',
      'RON',
      'RSD',
      'RUB',
      'RUR',
      'RWF',
      'SAR',
      'SBD',
      'SCR',
      'SDG',
      'SEK',
      'SGD',
      'SHP',
      'SLL',
      'SOS',
      'SRD',
      'STD',
      'SYP',
      'SZL',
      'THB',
      'TJS',
      'TMT',
      'TND',
      'TOP',
      'TRY',
      'TTD',
      'TWD',
      'TZS',
      'UAH',
      'UGX',
      'USD',
      'UYU',
      'UZS',
      'VEF',
      'VND',
      'VUV',
      'WST',
      'XAF',
      'XAG',
      'XAU',
      'XBA',
      'XBB',
      'XBC',
      'XBD',
      'XCD',
      'XDR',
      'XFU',
      'XOF',
      'XPD',
      'XPF',
      'XPT',
      'XTS',
      'XXX',
      'YER',
      'ZAR',
      'ZMK',
      'ZWL',
    ];

    /**
     * Set the unit cost.
     *
     * @param float $amount
     * @param string $currency
     */
    public function setUnitCost($amount, $currency)
    {
        $currency = mb_strtoupper($currency);

        if (mb_strlen($currency) === 0) {
            throw new PangaeaException('Unit cost currency cannot be blank');
        }

        if (! in_array($currency, static::UNIT_COST_CURRENCIES)) {
            throw new PangaeaException(sprintf('Invalid unit cost currency "%s"', $currency));
        }

        $this->unitCost['amount']   = (float) $amount;
        $this->unitCost['currency'] = (string) $currency;
    }

    /**
     * Set the legacy distributor ID.
     *
     * @param string $legacyDistributorId
     */
    public function setLegacyDistributorId($legacyDistributorId)
    {
        $this->legacyDistributorId = (string) $legacyDistributorId;
    }

    /**
     * Set the ship node supply.
     *
     * @param string $mdsfamId
     * @param string $vendorStockId
     */
    public function setShipNodeSupply($mdsfamId, $vendorStockId)
    {
        $this->shipNodeSupply['mdsfamId']      = (string) $mdsfamId;
        $this->shipNodeSupply['vendorStockId'] = (string) $vendorStockId;
    }

    /**
     * Add a preferred distributor.
     *
     * @param $legacyDistributorId
     * @param null $from
     * @param null $till
     * @param null $rank
     */
    public function addPreferredDistributor($legacyDistributorId, $from = null, $till = null, $rank = null)
    {
        $this->preferredDistributors[] = [
            'legacyDistributorId' => $legacyDistributorId,
            'effectiveFrom'       => (! Date::isEmpty($from) ? Date::format($from) : null),
            'effectiveTill'       => (! Date::isEmpty($from) ? Date::format($from) : null),
            'rank'                => (! is_null($rank) ? (int) $rank : null),
        ];
    }

    /**
     * Render the preferred distributors XML.
     *
     * @return string.
     */
    private function renderPreferredDistributors()
    {
        if (count($this->preferredDistributors) === 0) {
            return '';
        }

        $distributorsXml = '';

        foreach ($this->preferredDistributors as $distributor) {
            $distributorXml = '';

            foreach ($distributor as $name => $value) {
                if (! is_null($value) && mb_strlen($value) > 0) {
                    $distributorXml .= '<' . $name . '>' . Xml::escape($value) . '</' . $name . '>';
                }
            }

            if (! empty($distributorXml)) {
                $distributorsXml .= '<preferredDistributor>' . $distributorXml . '</preferredDistributor>';
            }
        }

        if (! empty($distributorsXml)) {
            $distributorsXml = '<preferredDistributors>' . $distributorsXml . '</preferredDistributors>';
        }

        return $distributorsXml;
    }

    /**
     * Get an array of attributes that belong in the item's product attributes element.
     * Note: Attributes are only created and returned if there's a non-empty value.
     *
     * @return array
     */
    public function getProductAttributes()
    {
        $map = [
            'supplier_number'       => $this->legacyDistributorId,
            'mds_fam_id'            => $this->shipNodeSupply['mdsfamId'],
            'supplier_stock_number' => $this->shipNodeSupply['vendorStockId'],
        ];

        $attributes = [];

        foreach ($map as $name => $value) {
            $attribute = new NameValueAttribute($name, $value);

            if (! $attribute->isEmpty()) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Render the XML.
     *
     * @return string
     */
    public function render()
    {
        $unitCostAmount      = Xml::escape($this->unitCost['amount']);
        $unitCostCurrency    = Xml::escape($this->unitCost['currency']);
        $legacyDistributorId = Xml::escape($this->legacyDistributorId);
        $mdsfamId            = Xml::escape($this->shipNodeSupply['mdsfamId']);
        $vendorStockId       = Xml::escape($this->shipNodeSupply['vendorStockId']);

        $preferredDistributorsXml = $this->renderPreferredDistributors();

return <<< XML
<itemLogistics>
    <!-- START: Required Dummy Values -->
    <reportingHierarchy>
        <reportingHierarchyLevel>
            <levelId>1</levelId>
            <nodeId>str1234</nodeId>
        </reportingHierarchyLevel>
    </reportingHierarchy>
    <marketAttributes></marketAttributes>
    <preorderInfo>
        <isPreOrder>false</isPreOrder>
        <streetDate>2015-03-30T00:00:00</streetDate>
        <streetDateType>DELIVER_BY</streetDateType>
    </preorderInfo>
    <programEligibilities></programEligibilities>
    <packages></packages>
    <isPerishable>false</isPerishable>
    <isHazmat>false</isHazmat>
    <!-- END: Required Dummy Values -->
    <!-- START: Dummy LIMO Values -->
    <inventoryAvailabilityThreshold>
        <low>1</low>
        <mid>5</mid>
        <high>999</high>
    </inventoryAvailabilityThreshold>
    <onHandSafetyFactorQuantity>
        <value>5</value>
        <unit>EA</unit>
    </onHandSafetyFactorQuantity>
    <assumeInfiniteInventory>true</assumeInfiniteInventory>
    <unitCost>
        <currency>{$unitCostCurrency}</currency>
        <amount>{$unitCostAmount}</amount>
    </unitCost>
    <primarySupplyItemId>2947757</primarySupplyItemId>
    <alternateSupplyItemId>str1234</alternateSupplyItemId>
    {$preferredDistributorsXml}
    <!-- END: Dummy LIMO Values -->
    <!-- START: Required Dummy Values -->
    <fulfillmentOptions></fulfillmentOptions>
    <shipAsIs>true</shipAsIs>
    <signatureOnDelivery>NEVER</signatureOnDelivery>
    <isConveyable>false</isConveyable>
    <bundleFulfillmentMode>SHIP_ALONE</bundleFulfillmentMode>
    <storageType>AMBIENT</storageType>
    <!-- END: Required Dummy Values -->
    <shipNodes>
        <shipNode>
            <legacyDistributorId>{$legacyDistributorId}</legacyDistributorId>
            <!-- START: Required Dummy Values -->
            <shipNodeStatus>ACTIVE</shipNodeStatus>
            <preOrderMaxQty>
                <value>1</value>
                <unit>EA</unit>
            </preOrderMaxQty>
            <handlingCost>
                <currency>GBP</currency>
                <amount>123.45</amount>
            </handlingCost>
            <unitCost>
                <currency>GBP</currency>
                <amount>4.00</amount>
            </unitCost>
            <shipNodeItemId>str1234</shipNodeItemId>
            <initialAvailabilityCode>AA</initialAvailabilityCode>
            <availabilityThreshold>
                <low>123</low>
                <mid>123</mid>
                <high>123</high>
            </availabilityThreshold>
            <inventoryOwnerId>EEB3A0D4-309A-4DAA-9296-77BE4AEFB2CE</inventoryOwnerId>
            <programEligibilities></programEligibilities>
            <!-- END: Required Dummy Values -->
            <shipNodeSupplies>
                <shipNodeSupply>
                    <mdsfamId>{$mdsfamId}</mdsfamId>
                    <vendorStockId>{$vendorStockId}</vendorStockId>
                </shipNodeSupply>
            </shipNodeSupplies>
        </shipNode>
    </shipNodes>
</itemLogistics>
XML;
    }
}
