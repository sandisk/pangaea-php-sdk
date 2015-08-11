<?php
namespace Pangaea;

use \Pangaea\Attribute\AttributeInterface;
use \Pangaea\Attribute\NameValueAttribute;

class Item
{
    /**
     * Default End Date (as specified by Walmart).
     *
     * @const
     */
    const SPEC_DEFAULT_END_DATE  = '2049-12-31';

    /**
     * Valid Attribute Groups.
     *
     * @const
     */
    const VALID_ATTRIBUTE_GROUPS = [
        'Product',
        'Compliance',
        'MarketInProduct',
        'MarketInOffer',
        'Offer',
        'VariantMetaData',
    ];

    /**
     * Valid Lifecycle Statuses.
     *
     * @const
     */
    const LIFECYCLE_STATUSES = [
        'ACTIVE',
        'ARCHIVED',
        'RETIRED',
    ];

    /**
     * Valid Published Statuses.
     *
     * @const
     */
    const PUBLISHED_STATUSES = [
        'IN_PROGRESS',
        'READY_TO_PUBLISH',
        'PUBLISHED',
        'UNPUBLISHED',
        'SYSTEM_PROBLEM',
        'STAGE',
    ];

    /**
     * SKU
     *
     * @var mixed
     */
    private $sku;

    /**
     * UPC
     *
     * @var mixed
     */
    private $upc;

    /**
     * Title
     *
     * @var mixed
     */
    private $title;

    /**
     * Short Description
     *
     * @var mixed
     */
    private $shortDescription;

    /**
     * Long Description
     *
     * @var mixed
     */
    private $longDescription;

    /**
     * Tax Code
     *
     * @var mixed
     */
    private $taxCode;

    /**
     * Assets
     *
     * @var mixed
     */
    private $assets;

    /**
     * Attributes
     *
     * @var array
     */
    private $attributes = [];

    /**
     * Brand
     *
     * @var mixed
     */
    private $brand;

    /**
     * Item Logistics XML
     *
     * @var mixed
     */
    private $itemLogistics;

    /**
     * Shipping Dimensions XML
     *
     * @var string
     */
    private $shipping;

    /**
     * Publish and Lifecycle Statuses.
     *
     * @var array
     */
    private $status = [
        'publish'   => '',
        'lifecycle' => '',
    ];

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
     * Set the publish status.
     *
     * @param $status
     * @return void
     * @throws PangaeaException
     */
    public function setPublishStatus($status)
    {
        $status = mb_strtoupper($status);

        if (! in_array($status, static::PUBLISHED_STATUSES)) {
            throw new PangaeaException(sprintf('Invalid publish status "%s"', $status));
        }

        $this->status['publish'] = '<publishStatus>' . Xml::escape($status) . '</publishStatus>';
    }

    /**
     * Set the lifecycle status.
     *
     * @param $status
     * @return void
     * @throws PangaeaException
     */
    public function setLifecycleStatus($status)
    {
        $status = mb_strtoupper($status);

        if (! in_array($status, static::LIFECYCLE_STATUSES)) {
            throw new PangaeaException(sprintf('Invalid lifecycle status "%s"', $status));
        }

        $this->status['lifecycle'] = '<lifecycleStatus>' . Xml::escape($status) . '</lifecycleStatus>';
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
        if (mb_strlen($brand) > 0) {
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
     * @throw \Pangaea\PangaeaException
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
            // Convert a value into a basic attribute.
            if (! is_object($values)) {
                $values = new NameValueAttribute($id, $values);
            }

            if ($values instanceof AttributeInterface) {
                $this->attributes[$group] .= $values->render();
            } else {
                throw new PangaeaException(sprintf('Class "%s" must implement AttributeInterface', get_class($values)));
            }
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

            $attribute = new NameValueAttribute('provider_name', 'asda.scene7.com');

            $this->assets .= <<<XML
<Asset>
    <assetURL>{$asset}</assetURL>
    <usageType>{$type}</usageType>
    <rank>1</rank>
    <AssetAttributes>{$attribute->render()}</AssetAttributes>
</Asset>
XML;
        }
    }

    /**
     * Set the item logistics.
     * Corresponding attributes will also be added to the product attributes group too.
     *
     * @param null $legacyDistributorId
     * @param null $mdsFamId
     * @param null $vendorStockId
     */
    public function setItemLogistics($legacyDistributorId = null, $mdsfamId = null, $vendorStockId = null)
    {
        $itemLogisticsParams = [
            'legacyDistributorId' => $legacyDistributorId,
            'mdsfamId'            => $mdsfamId,
            'vendorStockId'       => $vendorStockId,
        ];

        // The name to use when adding them to the product attributes group.
        $attributeLookup = [
            'legacyDistributorId' => 'supplier_number',
            'mdsfamId'            => 'mds_fam_id',
            'vendorStockId'       => 'supplier_stock_number',
        ];

        $itemLogisticsElements   = [];
        $itemLogisticsAttributes = [];

        foreach ($itemLogisticsParams as $key => $value) {
            $hasValue = mb_strlen($value) > 0;

            $itemLogisticsElements[$key] = '';

            if ($hasValue) {
                $itemLogisticsElements[$key] = '<' . $key . '>' . Xml::escape($value) . '</' . $key . '>';
            }

            if ($hasValue && isset($attributeLookup[$key])) {
                $itemLogisticsAttributes[$attributeLookup[$key]] = (string) $value;
            }
        }

        $this->addAttributes('Product', $itemLogisticsAttributes);

        $this->itemLogistics = <<< XML
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
<shipAsIs>true</shipAsIs>
<signatureOnDelivery>NEVER</signatureOnDelivery>
<isConveyable>false</isConveyable>
<bundleFulfillmentMode>SHIP_ALONE</bundleFulfillmentMode>
<storageType>AMBIENT</storageType>
<!-- END: Required Dummy Values -->
<shipNodes>
    <shipNode>
        {$itemLogisticsElements['legacyDistributorId']}
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
                {$itemLogisticsElements['mdsfamId']}
                {$itemLogisticsElements['vendorStockId']}
            </shipNodeSupply>
        </shipNodeSupplies>
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
        <VariantMetaData>{$this->attributes['VariantMetaData']}</VariantMetaData>
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
        {$this->status['publish']}
        {$this->status['lifecycle']}
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
