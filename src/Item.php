<?php
namespace Pangaea;

use \Pangaea\RenderableInterface;
use \Pangaea\Attribute\AttributeInterface;
use \Pangaea\Attribute\NameValueAttribute;
use \Pangaea\Attribute\VariantMetaDataAttribute;
use \Pangaea\Item\ItemLogistics;

class Item implements RenderableInterface
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
     * Valid Product Setup Types.
     */
    const PRODUCT_SETUP_TYPES = [
        'STANDALONE',
        'PRIMARY',
        'VARIANT',
        'BUNDLE',
    ];

    /**
     * Valid units of measurement.
     *
     * @const
     */
    const UNITS_MEASUREMENT = [
      'EA',
      'FT',
      'IN',
      'INCH',
      'YD',
      'M',
      'CM',
      'MM',
      'KG',
      'G',
      'MG',
      'POUND',
      'LB',
      'OZ',
      'FOZ',
      'GAL',
      'QT',
      'PT',
      'IMPGAL',
      'IMPQT',
      'IMPPT',
      'L',
      'ML',
      'CC',
      'CBM',
      'CFT',
      'CYD',
      'CIN',
      'SM',
      'SFT',
      'SYD',
      'SIN',
      'SCM',
      'SMM',
    ];

    /**
     * Valid units of weight.
     *
     * @const
     */
    const UNITS_WEIGHT = [
        'KG',
        'G',
        'MG',
        'LB',
        'OZ',
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

    /*
     * Product Setup Type
     *
     * @var string
     */
    private $productSetupType;

    /*
     * Variant Group ID
     *
     * @var string
     */
    private $variantGroupId;

    /**
     * Variant Meta Data attributes.
     *
     * @var array
     */
    private $variantMetaDataAttributes;

    /**
     * Brand
     *
     * @var mixed
     */
    private $brand;

    /**
     * Item Logistics
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
     * Set the descriptions (short and long).
     * Accepts text and unencoded HTML (which will be encoded as UTF-8 entities).
     *
     * @param $short
     * @param $long
     * @param $raw
     */
    public function setDescriptions($short, $long, $raw = false)
    {
        if (! $raw) {
            $long = Xml::escape($long);
        }

        $this->shortDescription = Xml::escape($short);
        $this->longDescription  = $long;
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

        if (mb_strlen($status) === 0) {
            throw new PangaeaException('Publish status cannot be blank');
        }

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

        if (mb_strlen($status) === 0) {
            throw new PangaeaException('Lifecycle status cannot be blank');
        }

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
     * @throws PangaeaException
     */
    public function setDimensions($length, $width, $height, $unit)
    {
        $unit = mb_strtoupper($unit);

        if (mb_strlen($unit) === 0) {
            throw new PangaeaException('Shipping unit of measurement cannot be blank');
        }

        if (! in_array($unit, static::UNITS_MEASUREMENT)) {
            throw new PangaeaException(sprintf('Invalid shipping unit of measurement "%s"', $unit));
        }

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
        $unit = mb_strtoupper($unit);

        if (mb_strlen($unit) === 0) {
            throw new PangaeaException('Shipping unit of weight cannot be blank');
        }

        if (! in_array($unit, static::UNITS_WEIGHT)) {
            throw new PangaeaException(sprintf('Invalid shipping unit of weight "%s"', $unit));
        }

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
     * @param mixed $attributes             A multi-dimensional array with names as keys then either a single value or an array of values.
     *                                      A single NameValueAttribute object or an array of NameValueAttribute objects.
     */
    public function addAttributes($group, $attributes)
    {
        if (! is_array($attributes)) {
            $attributes = [$attributes];
        }

        if (! in_array($group, static::VALID_ATTRIBUTE_GROUPS)) {
            throw new PangaeaException('Invalid attribute group');
        }

        if (! isset($this->attributes[$group])) {
            $this->attributes[$group] = '';
        }

        // @todo: key sort? doc block if do, but wait until settled and verified no differences...
        $attributes = $this->arrayToNameValueAttributes($attributes);

        foreach ($attributes as $attribute) {
            if ($attribute instanceof AttributeInterface) {
                $this->attributes[$group] .= $attribute->render();
            } else {
                throw new PangaeaException(sprintf('Class "%s" must implement AttributeInterface', get_class($attribute)));
            }
        }
    }

    /**
     * Convert a multi-dimensional array of name, value pairs into NameValueAttribute objects.
     * Note: Eventually this should be removed and we should always use the objects.
     *
     * @param  array $data
     * @return array
     */
    private function arrayToNameValueAttributes(array $data)
    {
        if (count($data) === 0) {
            return [];
        }

        $attributes = [];

        foreach ($data as $name => $value) {
            if (! $value instanceof AttributeInterface) {
                $attribute = new NameValueAttribute($name, $value);
            } else {
                $attribute = $value;
            }

            $attributes[] = $attribute;
        }

        return $attributes;
    }

    /**
     * Set the product setup type.
     *
     * @param $type
     */
    public function setProductSetupType($type)
    {
        $type = mb_strtoupper($type);

        if (mb_strlen($type) === 0) {
            throw new PangaeaException('Product setup type cannot be blank');
        }

        if (! in_array($type, static::PRODUCT_SETUP_TYPES)) {
            throw new PangaeaException(sprintf('Invalid product setup type "%s"', $type));
        }

        $this->productSetupType = $type;
    }

    /**
     * Set the variant group ID.
     *
     * @param $groupId
     */
    public function setVariantGroupId($groupId)
    {
        $this->variantGroupId = $groupId;
    }

    /**
     * Add variant meta data attributes to the item.
     *
     * @param $attributes
     * @throw \Pangaea\PangaeaException
     */
    public function addVariantMetaDataAttributes($attributes)
    {
        if (! is_array($attributes)) {
            $attributes = [$attributes];
        }

        foreach ($attributes as $attribute) {
            if (! $attribute instanceof VariantMetaDataAttribute) {
                throw new PangaeaException('Variant Meta Data must be an instance of VariantMetaDataAttribute');
            }

            $this->variantMetaDataAttributes .= $attribute->render();
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
     * Set the item logistics elemnt.
     * Note: This will also add corresponding attributes to the product attributes group.
     *
     * @param ItemLogistics $itemLogistics
     */
    public function setItemLogistics(ItemLogistics $itemLogistics)
    {
        $this->itemLogistics = $itemLogistics->render();
        $this->addAttributes('Product', $itemLogistics->getProductAttributes());
    }

    /**
     * Render the item.
     *
     * @return string
     */
    public function render()
    {
        if (null === $this->itemLogistics) {
            throw new PangaeaException('Cannot render item without an ItemLogistics element being set');
        }

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
        <productSetupType>{$this->productSetupType}</productSetupType>
        <variantGroupID>{$this->variantGroupId}</variantGroupID>
        <VariantMetaData>{$this->variantMetaDataAttributes}</VariantMetaData>
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
        {$this->itemLogistics}
        <ItemPrice>{$this->pricing}</ItemPrice>
        <OfferAttributes>{$this->attributes['Offer']}</OfferAttributes>
        <MarketAttributes>{$this->attributes['MarketInOffer']}</MarketAttributes>
    </Offer>
</UncategorizedItem>
XML;
    }
}
