<?php
namespace Pangaea\Attribute;

use \Pangaea\PangaeaException;
use \Pangaea\Date;
use \Pangaea\Xml;
use \Pangaea\RenderableInterface;

class VariantMetaDataAttribute implements RenderableInterface
{
    /**
     * Valid Attribute Groups.
     *
     * @const
     */
    const VALID_RESOURCE_TYPES = [
        'DEFAULT',
        'LOCATOR',
    ];

    /**
     * The attribute's XML element structure.
     *
     * @var array
     */
    private $elements = [
        'name'                => null,
        'type'                => null,
        'isVariant'           => 'true',
        'variantResourceType' => 'DEFAULT',
        'value'               => [
            'value'     => null,
            'rank'      => 0,
            'isVariant' => 'true',
        ],
    ];

    /**
     * Create a new VariantMetaDataAttribute.
     *
     * @param $name
     * @param $value
     * @param $resourceType
     * @param $rank
     */
    public function __construct($name, $value, $resourceType = null, $rank = null)
    {
        $this->setName($name);
        $this->setValue($value);

        if (! is_null($resourceType)) {
            $this->setResourceType($resourceType);
        }

        if (! is_null($rank)) {
            $this->setRank($rank);
        }
    }

    /**
     * Set the name
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->elements['name'] = Xml::escape($name);
    }

    /**
     * Set the resource type.
     *
     * @param $resourceType
     * @throws PangaeaException
     */
    public function setResourceType($resourceType)
    {
        $resourceType = mb_strtoupper($resourceType);

        if (! in_array($resourceType, static::VALID_RESOURCE_TYPES)) {
            throw new PangaeaException(sprintf('Invalid resource type "%s"', $resourceType));
        }

        $this->elements['variantResourceType'] = $resourceType;
    }

    /**
     * Set whether the attribute isVariant or not.
     *
     * @param $isVariant
     */
    public function isVariant($isVariant)
    {
        $isVariant = Xml::escape((bool) $isVariant);

        $this->elements['isVariant']          = $isVariant;
        $this->elements['value']['isVariant'] = $isVariant;
    }

    /**
     * Set the attribute value (and infer the type automatically).
     *
     * @param $value
     */
    public function setValue($value)
    {
        $type = Xml::attributeType($value);

        if ('DATE' === $type && ! Date::isEmpty($value)) {
            $value = Date::format($value);
        }

        $this->elements['value']['value'] = Xml::escape($value);
        $this->elements['type']           = $type;
    }

    /**
     * Set the rank.
     *
     * @param $rank
     */
    public function setRank($rank)
    {
        $this->elements['value']['rank'] = Xml::escape((int) $rank);
    }

    /**
     * Return whether or not the node is empty (has no values).
     *
     * @return bool
     */
    public function isEmpty()
    {
        return is_null($this->elements['value']['value']);
    }

    /**
     * Render the attribute.
     *
     * @return string
     */
    public function render()
    {
        if ($this->isEmpty()) {
            return '';
        }

        return <<< XML
<NameValueAttribute>
    <name>{$this->elements['name']}</name>
    <type>{$this->elements['type']}</type>
    <isVariant>{$this->elements['isVariant']}</isVariant>
    <variantResourceType>{$this->elements['variantResourceType']}</variantResourceType>
    <value>
        <value>{$this->elements['value']['value']}</value>
        <rank>{$this->elements['value']['rank']}</rank>
        <isVariant>{$this->elements['value']['isVariant']}</isVariant>
    </value>
</NameValueAttribute>
XML;
    }
}
