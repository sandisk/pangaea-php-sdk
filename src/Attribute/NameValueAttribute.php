<?php
namespace Pangaea\Attribute;

use \Pangaea\Date;
use \Pangaea\PangaeaException;
use \Pangaea\RenderableInterface;
use \Pangaea\Xml;
use \Pangaea\Attribute\AttributeInterface;

class NameValueAttribute implements AttributeInterface, RenderableInterface
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
    private $value = [];

    /**
     * Create a basic NameValueAttribute.
     *
     * @param $name
     * @param mixed $values
     */
    public function __construct($name, $value = null)
    {
        $this->setName($name);
        $this->setValue($value);
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
            throw new PangaeaException('NameValueAttribute element "name" cannot be blank');
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
     * Set the attribute value (and infer the type automatically).
     *
     * @param $value
     */
    public function setValue($values)
    {
        if (! is_array($values)) {
            $values = [$values];
        }

        if (count($values) === 0 || is_null(reset($values))) {
            return;
        }

        $this->type  = Xml::attributeType(reset($values));
        $this->value = [];

        foreach ($values as $value) {
            if ('DATE' === $this->type && ! Date::isEmpty($value)) {
                $value = Date::format($value);
            }

            $this->value[] = $value;
        }
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
     * Return whether or not the attribute is empty (has no values).
     *
     * @return bool
     */
    public function isEmpty()
    {
        if (count($this->value) === 0) {
            return true;
        }

        foreach ($this->value as $value) {
            if (! is_null($value)) {
                return false;
            }
        }

        return true;
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

        $name = Xml::escape($this->name);

        $valuesXml = '';

        foreach ($this->value as $value) {
            $valuesXml .= '<value><value>'. Xml::escape($value) . '</value></value>';
        }

        return <<< XML
<NameValueAttribute>
    <name>{$name}</name>
    <type>{$this->type}</type>
    {$valuesXml}
</NameValueAttribute>
XML;
    }
}
