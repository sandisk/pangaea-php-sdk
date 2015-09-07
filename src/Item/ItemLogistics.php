<?php
namespace Pangaea\Item;

use \Pangaea\Date;
use \Pangaea\Pangaea;
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

    /*
     * Assume Infinite Inventory.
     *
     * @var bool
     */
    private $assumeInfiniteInventory = false;

    /**
     * On hand Safety Factor Quantity.
     *
     * @var array
     */
    private $onHandSafetyFactorQuantity = [
        'value' => null,
        'unit'  => null,
    ];

    /**
     * Preferred Distributors.
     *
     * @var array
     */
    private $preferredDistributors = [];

    /**
     * Inventory Availability Threshold.
     *
     * @var array
     */
    private $inventoryAvailabilityThreshold = [
        'low'    => null,
        'medium' => null,
        'high'   => null,
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

        if (! in_array($currency, Pangaea::UNIT_COST_CURRENCIES)) {
            throw new PangaeaException(sprintf('Invalid unit cost currency "%s"', $currency));
        }

        $this->unitCost['amount']   = (float) $amount;
        $this->unitCost['currency'] = $currency;
    }

    /**
     * Set the legacy distributor ID.
     *
     * @param string $legacyDistributorId
     */
    public function setLegacyDistributorId($legacyDistributorId)
    {
        $this->legacyDistributorId = $legacyDistributorId;
    }

    /**
     * Set the ship node supply.
     *
     * @param string $mdsfamId
     * @param string $vendorStockId
     */
    public function setShipNodeSupply($mdsfamId, $vendorStockId)
    {
        $this->shipNodeSupply['mdsfamId']      = $mdsfamId;
        $this->shipNodeSupply['vendorStockId'] = $vendorStockId;
    }

    /**
     * Set assume infinite inventory.
     *
     * @param bool $assumeInfiniteInventory
     */
    public function setAssumeInfiniteInventory($assumeInfiniteInventory)
    {
        $this->assumeInfiniteInventory = (bool) $assumeInfiniteInventory;
    }

    /**
     * Set the On Hand Safety Factor Quantity.
     *
     * @param $value
     * @param $unit
     * @throws PangaeaException
     */
    public function setOnHandSafetyFactorQuantity($value, $unit)
    {
        $unit = mb_strtoupper($unit);

        if (mb_strlen($unit) === 0) {
            throw new PangaeaException('Unit of measurement cannot be blank');
        }

        if (! in_array($unit, Pangaea::UNITS_MEASUREMENT)) {
            throw new PangaeaException(sprintf('Invalid unit of measurement "%s"', $unit));
        }

        $this->onHandSafetyFactorQuantity['value'] = (int) $value;
        $this->onHandSafetyFactorQuantity['unit']  = $unit;
    }

    /**
     * Set the Inventory Availability Threshold.
     *
     * @param $low
     * @param null $mid
     * @param null $high
     */
    public function setInventoryAvailabilityThreshold($low, $mid = null, $high = null)
    {
        $this->inventoryAvailabilityThreshold = [
            'low'  => (int) $low,
            'mid'  => ($mid  ? (int) $mid  : null),
            'high' => ($high ? (int) $high : null),
        ];
    }

    /**
     * Render the Inventory Availability Threshold XML.
     *
     * return string
     */
    private function renderInventoryAvailabilityThreshold()
    {
        $inventoryAvailabilityThresholdXml = '<inventoryAvailabilityThreshold>';

        foreach ($this->inventoryAvailabilityThreshold as $name => $value) {
            if (!is_null($value) && mb_strlen($value) > 0) {
                $inventoryAvailabilityThresholdXml .= '<' . $name . '>' . Xml::escape($value) . '</' . $name . '>';
            }
        }

        $inventoryAvailabilityThresholdXml .= '</inventoryAvailabilityThreshold>';

        return $inventoryAvailabilityThresholdXml;
    }

    /**
     * Add a preferred distributor.
     *
     * @param $rank
     * @param $legacyDistributorId
     * @param null $from
     * @param null $till
     */
    public function addPreferredDistributor($rank, $legacyDistributorId, $from = null, $till = null)
    {
        $this->preferredDistributors[] = [
            'legacyDistributorId' => $legacyDistributorId,
            'effectiveFrom'       => (! Date::isEmpty($from) ? Date::format($from) : null),
            'effectiveTill'       => (! Date::isEmpty($till) ? Date::format($till) : null),
            'rank'                => (! is_null($rank)       ? (int) $rank         : null),
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

            if (mb_strlen($distributorXml) > 0) {
                $distributorsXml .= '<preferredDistributor>' . $distributorXml . '</preferredDistributor>';
            }
        }

        if (mb_strlen($distributorsXml) > 0) {
            $distributorsXml = '<preferredDistributors>' . $distributorsXml . '</preferredDistributors>';
        }

        return $distributorsXml;
    }

    /**
     * Get an array of attributes that belong in the item's product attributes element.
     * Note: Attributes are only created and returned if there's a non-empty value.
     *       Attributes are to be of type STRING if they're not empty/null.
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
                $attribute->setValue((string) $value);

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
        $unitCostAmount                  = Xml::escape($this->unitCost['amount']);
        $unitCostCurrency                = Xml::escape($this->unitCost['currency']);
        $legacyDistributorId             = Xml::escape($this->legacyDistributorId);
        $mdsfamId                        = Xml::escape($this->shipNodeSupply['mdsfamId']);
        $vendorStockId                   = Xml::escape($this->shipNodeSupply['vendorStockId']);
        $assumeInfiniteInventory         = Xml::escape($this->assumeInfiniteInventory);
        $onHandSafetyFactorQuantityValue = Xml::escape($this->onHandSafetyFactorQuantity['value']);
        $onHandSafetyFactorQuantityUnit  = Xml::escape($this->onHandSafetyFactorQuantity['unit']);

        $inventoryAvailabilityThresholdXml = $this->renderInventoryAvailabilityThreshold();

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
    {$inventoryAvailabilityThresholdXml}
    <onHandSafetyFactorQuantity>
        <value>{$onHandSafetyFactorQuantityValue}</value>
        <unit>{$onHandSafetyFactorQuantityUnit}</unit>
    </onHandSafetyFactorQuantity>
    <assumeInfiniteInventory>{$assumeInfiniteInventory}</assumeInfiniteInventory>
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
