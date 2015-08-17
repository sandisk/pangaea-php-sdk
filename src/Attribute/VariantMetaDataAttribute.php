<?php
namespace Pangaea\Attribute;

use \Pangaea\PangaeaException;
use \Pangaea\Date;
use \Pangaea\Xml;
use \Pangaea\Attribute\AttributeInterface;

class VariantMetaDataAttribute implements AttributeInterface
{
    /**
     * The attribute name.
     *
     * @var mixed
     */
    private $name;

    /**
     * The attribute type.
     *
     * @var mixed
     */
    private $type;

    /**
     * The attribute value.
     *
     * @var array
     */
    private $value;

    /**
     * Is the attribute for a variant?
     *
     * @var string
     */
    private $isVariant = true;

    /**
     * The variant resource type.
     *
     * @var string
     */
    private $variantResourceType = 'DEFAULT';

    /**
     * The attribute rank (order for colours, sizes etc).
     *
     * @var int
     */
    private $rank = 0;

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
     * Create a new VariantMetaDataAttribute.
     *
     * @param $name
     * @param $value
     * @param $resourceType
     * @param $rank
     */
    public function __construct($name, $value = null, $resourceType = null, $rank = null, $isVariant = null)
    {
        $this->setName($name);
        $this->setValue($value);

        if (! is_null($resourceType)) {
            $this->setResourceType($resourceType);
        }

        if (! is_null($rank)) {
            $this->setRank($rank);
        }

        if (! is_null($isVariant)) {
            $this->setIsVariant($isVariant);
        }
    }

    /**
     * Set the name
     *
     * @param $name
     * @throw \Pangaea\PangaeaException
     */
    public function setName($name)
    {
        if (mb_strlen($name) === 0) {
            throw new PangaeaException('VariantMetaDataAttribute element "name" cannot be empty');
        }

        $this->name = $name;
    }

    /**
     * Get the name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
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

        $this->variantResourceType = $resourceType;
    }

    /**
     * Get the resource type.
     *
     * return bool
     */
    public function getResourceType()
    {
        return $this->variantResourceType;
    }

    /**
     * Set whether the attribute isVariant or not.
     *
     * @param $isVariant
     */
    public function setIsVariant($isVariant)
    {
        $this->isVariant = (bool) $isVariant;
    }

    /**
     * Get whether the attribute isVariant or not.
     *
     * @return bool
     */
    public function getIsVariant()
    {
        return $this->isVariant;
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

        $this->value = $value;
        $this->type  = $type;
    }

    /**
     * Get the value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the type.
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the rank.
     *
     * @param $rank
     */
    public function setRank($rank)
    {
        $this->rank = (int) $rank;
    }

    /**
     * Get the rank.
     *
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Return whether or not the attribute is empty (has no values).
     *
     * @return bool
     */
    public function isEmpty()
    {
        return is_null($this->value);
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

        $name      = Xml::escape($this->name);
        $value     = Xml::escape($this->value);
        $isVariant = Xml::escape($this->isVariant);
        $rank      = Xml::escape($this->rank);

        return <<< XML
<NameValueAttribute>
    <name>{$name}</name>
    <type>{$this->type}</type>
    <isVariant>{$isVariant}</isVariant>
    <variantResourceType>{$this->variantResourceType}</variantResourceType>
    <value>
        <value>{$value}</value>
        <rank>{$rank}</rank>
        <isVariant>{$isVariant}</isVariant>
    </value>
</NameValueAttribute>
XML;
    }
}
