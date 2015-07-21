<?php
namespace Pangaea;

class Item
{
    const SPEC_DEFAULT_END_DATE  = '2049-12-31'; // as specified by Walmart
    const VALID_ATTRIBUTE_GROUPS = ['Product', 'Compliance', 'MarketInProduct', 'MarketInOffer', 'Offer'];

    private $sku, $upc, $title, $shortDescription, $longDescription, $taxCode, $assets, $attributes, $brand, $itemLogistics;
    private $shipping = '';

    /**
     * Create a new product item for a specific SKU and UPC
     *
     * @param $sku
     * @param $upc
     */
    public function __construct($sku, $upc)
    {
        $this->sku = Xml::escape($sku);
        $this->upc = Xml::escape($upc);
    }

    /**
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = Xml::escape($title);
    }

    /**
     * @param $short
     * @param $long
     */
    public function setDescriptions($short, $long)
    {
        $this->shortDescription = Xml::escape($short);
        $this->longDescription  = Xml::escape($long);
    }

    /**
     * Set the tax rate (integer/decimal, for example 12.34) to be exported as Pangaea tax code (1,234.00 for same example)
     *
     * @param $rate
     */
    public function setTaxCode($rate)
    {
        $this->taxCode = number_format($rate * 100, 2);
    }

    /**
     * If an end date isn't provided, a default (@see static::SPEC_DEFAULT_END_DATE) is used instead
     *
     * @param $start
     * @param null $end
     */
    public function setDates($start, $end = null)
    {
        $start = (Date::isEmpty($start) ? '' : '<startDate>' . Date::format($start) . '</startDate>');
        $end   = Date::format(Date::isEmpty($end) ? static::SPEC_DEFAULT_END_DATE : $end);

        $this->dates = "$start<endDate>$end</endDate>";
    }

    /**
     * Items have two statuses, whether published and whether active (aka 'lifecycle')
     *
     * @param $published
     * @param $active
     */
    public function setStatus($published, $active)
    {
        $published = ($published ? 'UNPUBLISHED' : 'PUBLISHED');
        $active    = ($active ? 'RETIRED' : 'ACTIVE');

        $this->status = "<publishStatus>$published</publishStatus><lifecycleStatus>$active</lifecycleStatus>";
    }

    /**
     * Shipping dimensions
     *
     * @param $length
     * @param $width
     * @param $height
     * @param $unit
     */
    public function setDimensions($length, $width, $height, $unit)
    {
        // @todo: possibly validate (numeric?) the values? unclear on rules...
        $this->shipping .= <<<XML
<shippingLength><value>{$length}</value><unit>{$unit}</unit></shippingLength>
<shippingWidth><value>{$width}</value><unit>{$unit}</unit></shippingWidth>
<shippingHeight><value>{$height}</value><unit>{$unit}</unit></shippingHeight>
XML;
    }

    /**
     * Shipping weight
     *
     * @param $weight
     * @param $unit
     */
    public function setWeight($weight, $unit)
    {
        $this->shipping .= "<shippingWeight><value>{$weight}</value><unit>{$unit}</unit></shippingWeight>";
    }

    /**
     * Three pricing values can be specified, along with an effective date (pass null if unknown, to use current time)
     * The previous price is optional, and its presence determines the exported `isStrikethrough` boolean value
     * @param int $retail
     * @param int $current
     * @param int $previous
     * @param $effectiveDate
     */
    public function setPricing($retail = 0, $current = 0, $previous = 0, $effectiveDate = null)
    {
        $effectiveDate = Date::format($effectiveDate);
        $retailPrice   = $this->renderPrice('BaseRetail', $retail, 'BASE');
        $currentPrice  = $this->renderPrice('CurrentPrice', $current, 'BASE');
        $previousPrice = ($previous > 0 ? $this->renderPrice('ComparisonPrice', $previous, 'LIST_PRICE') : '');
        $strikeThrough = Xml::escape($previous > 0);

        $this->pricing = <<<XML
<martId>5</martId>
<sku>{$this->sku}</sku>
{$retailPrice}
<StoreFrontPrice><PriceInfo>
    {$currentPrice}
    {$previousPrice}
    <PriceDisplayCodes><isStrikethrough>{$strikeThrough}</isStrikethrough></PriceDisplayCodes>
    <effectiveDate>{$effectiveDate}</effectiveDate>
</PriceInfo></StoreFrontPrice>
XML;
    }

    private function renderPrice($group, $amount, $type)
    {
        $amount = number_format($amount, 2);

        return "<$group><value><currency>GBP</currency><amount>$amount</amount></value><priceType>$type</priceType></$group>";
    }

    /**
     * Adds a brand element, but only if the provided value isn't empty
     *
     * @param string $brand
     */
    public function setBrand($brand)
    {
        if (! empty($brand)) {
            $this->brand = '<brand>' . Xml::escape($brand) . '</brand>';
        }
    }

    /**
     * Attributes belong in one of several (@see static::VALID_ATTRIBUTE_GROUPS) groups
     * Types are determined automatically from the first value in each attribute (cast when passing to override)
     * Empty string values are allowed and will be exported, but null values exclude the entire attribute element
     * All dates must be in `yyyy-mm-dd hh:mm:ss` format
     *
     * @todo: this should allow empty string values (valid for some attributes) but skip element if value is null...
     *
     * @param $group
     * @param array $attributes multi-dimensional, with names specified as keys then either a single value or an array of values
     */
    public function addAttributes($group, array $attributes)
    {
        if (! in_array($group, static::VALID_ATTRIBUTE_GROUPS)) {
            throw new PangaeaException('Invalid attribute group');
        }

        if (! isset($this->attributes[$group])) {
            $this->attributes[$group] = ''; // prevent 'undefined index' notices
        }

        // @todo: key sort? doc block if do, but wait until settled and verified no differences...

        foreach ($attributes as $id => $values) {
            $this->attributes[$group] .= $this->renderAttribute($id, $values);
        }
    }

    private function renderAttribute($name, $values)
    {
        // cast any single value parameter to an array for ease of iteration
        $values = (array) $values;

        if (count($values) == 0 || is_null($values[0])) {
            return '';
        }

        $name    = Xml::escape($name);
        $type    = $this->determineAttributeType($values[0]);
        $wrapped = '';

        // double <value> wrapping is as per the spec
        foreach ($values as $value) {
            if ('DATE' === $type && ! Date::isEmpty($value)) {
                $value = Date::format($value);
            }

            $wrapped .= '<value><value>'. Xml::escape($value) . '</value></value>';
        }

        return "<NameValueAttribute><name>$name</name><type>$type</type>$wrapped</NameValueAttribute>";
    }

    private function determineAttributeType($value)
    {
        if (is_bool($value)) {
            return 'BOOLEAN';
        } elseif (is_float($value)) {
            return 'DECIMAL';
        } elseif (is_int($value)) {
            return 'INTEGER';
        } elseif (preg_match('/\d{4}-\d{2}-\d{2}/', substr($value, 0, 10))) {
            return 'DATE'; // @todo: better to insist on passing DateTime objects?
        } else {
            return 'STRING';
        }
    }

    /**
     * Only urls need passing, the type is automatically set to PRIMARY for the first url, SECONDARY for others
     * If all urls share the same prefix, can pass that in as an optional parameter (so only need to pass unique ids)
     *
     * @param array $urls
     * @param string $urlPrefix
     */
    public function setAssets(array $urls, $urlPrefix = '')
    {
        $urls = array_filter($urls);

        foreach ($urls as $index => $asset) {
            $asset     = Xml::escape($urlPrefix . $asset);
            $type      = ($index > 0 ? 'SECONDARY' : 'PRIMARY');
            $attribute = $this->renderAttribute('provider_name', 'asda.scene7.com');

            $this->assets .= <<<XML
<Asset>
    <assetURL>{$asset}</assetURL>
    <usageType>{$type}</usageType>
    <rank>1</rank>
    <AssetAttributes>{$attribute}</AssetAttributes>
</Asset>
XML;
        }
    }

    /**
     * Set the item logistics.
     * Corresponding attributes will also be added to the product attributes group too.
     *
     * @param $legacyDistributorId
     * @param $mdsFamId
     * @param $vendorStockId
     */
    public function setItemLogistics($legacyDistributorId, $mdsFamId, $vendorStockId)
    {
        $itemLogistics = [
            'legacyDistributorId' => $legacyDistributorId,
            'mdsFamId'            => $mdsFamId,
            'vendorStockId'       => $vendorStockId,
        ];

        // The name to use when adding them to the product attributes group.
        $attributeLookup = [
            'legacyDistributorId' => 'supplier_number',
            'mdsFamId'            => 'mds_fam_id',
            'vendorStockId'       => 'supplier_stock_number',
        ];

        $itemLogisticsAttributes = [];

        foreach ($itemLogistics as $key => $value) {
            if (isset($attributeLookup[$key])) {
                $itemLogisticsAttributes[$attributeLookup[$key]] = $value;
            }
        }

        $this->addAttributes('Product', $itemLogisticsAttributes);

        $itemLogistics = array_map(function($value) {
            return Xml::escape((string) $value);
        }, $itemLogistics);

        $this->itemLogistics = <<< XML
<!-- START: Required Dummy Values -->
<reportingHierarchy></reportingHierarchy>
<marketAttributes></marketAttributes>
<isPreOrder>false</isPreOrder>
<streetDate>2015-03-30</streetDate>
<streetDateType>str1234</streetDateType>
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
    <currency>GBP</currency>
    <amount>123.99</amount>
</unitCost>
<primarySupplyItemId>2947757</primarySupplyItemId>
<alternateSupplyItemId>str1234</alternateSupplyItemId>
<preferredDistributors>
    <preferredDistributor>
        <legacyDistributorId>str1234</legacyDistributorId>
        <effectiveFrom>2012-12-13T12:12:12</effectiveFrom>
        <effectiveTill>2012-12-13T12:12:12</effectiveTill>
        <rank>1</rank>
    </preferredDistributor>
    <preferredDistributor>
        <legacyDistributorId>str1234</legacyDistributorId>
        <effectiveFrom>2012-12-13T12:12:12</effectiveFrom>
        <effectiveTill>2012-12-13T12:12:12</effectiveTill>
        <rank>2</rank>
    </preferredDistributor>
</preferredDistributors>
<!-- END: Dummy LIMO Values -->
<!-- START: Required Dummy Values -->
<fulfillmentOptions></fulfillmentOptions>
<shipAsIs>str1234</shipAsIs>
<isSignatureOnDeliveryReq>false</isSignatureOnDeliveryReq>
<isConveyable>false</isConveyable>
<bundleFulfillmentMode>false</bundleFulfillmentMode>
<storageType>AMBIENT</storageType>
<!-- END: Required Dummy Values -->
<shipNodes>
    <shipNode>
        <legacyDistributorId>{$itemLogistics['legacyDistributorId']}</legacyDistributorId>
        <!-- START: Required Dummy Values -->
        <itemShipNodeStatus>str1234</itemShipNodeStatus>
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
        <initialAvailabilityCode>str1234</initialAvailabilityCode>
        <availabilityThreshold>
            <low>123</low>
            <mid>123</mid>
            <high>123</high>
        </availabilityThreshold>
        <inventoryOwnerId>str1234</inventoryOwnerId>
        <programEligibilities></programEligibilities>
        <!-- END: Required Dummy Values -->
        <itemShipNodeSupplies>
            <itemShipNodeSupply>
                <mdsfamId>{$itemLogistics['mdsFamId']}</mdsfamId>
                <vendorStockId>{$itemLogistics['vendorStockId']}</vendorStockId>
            </itemShipNodeSupply>
        </itemShipNodeSupplies>
    </shipNode>
</shipNodes>
XML;
    }

    public function render()
    {
        return <<<XML
<UncategorizedItem processMode="INCREMENTAL" action="CREATE">
    <Product>
        <sku>{$this->sku}</sku>
        <productTitle>{$this->title}</productTitle>
        <productShortDescription>{$this->shortDescription}</productShortDescription>
        <productLongDescription>{$this->longDescription}</productLongDescription>
        <productTaxCode>{$this->taxCode}</productTaxCode>
        <ProductIds>
            <ProductId>
                <productIdType>UPC</productIdType>
                <productId>{$this->upc}</productId>
            </ProductId>
        </ProductIds>
        {$this->brand}
        <productSetupType>STANDALONE</productSetupType>
        <ProductAttributes>{$this->attributes['Product']}</ProductAttributes>
        <ComplianceAttributes>{$this->attributes['Compliance']}</ComplianceAttributes>
        <MarketAttributes>{$this->attributes['MarketInProduct']}</MarketAttributes>
        <Assets processMode="REPLACE" action="CREATE">
            <sku>{$this->sku}</sku>
            {$this->assets}
        </Assets>
    </Product>
    <Offer>
        <sku>{$this->sku}</sku>
        <offerType>ONLINE_ONLY</offerType>
        {$this->dates}
        {$this->status}
        {$this->shipping}
        <itemLogistics>{$this->itemLogistics}</itemLogistics>
        <ItemPrice>{$this->pricing}</ItemPrice>
        <OfferAttributes>{$this->attributes['Offer']}</OfferAttributes>
        <MarketAttributes>{$this->attributes['MarketInOffer']}</MarketAttributes>
    </Offer>
</UncategorizedItem>
XML;
    }
}
