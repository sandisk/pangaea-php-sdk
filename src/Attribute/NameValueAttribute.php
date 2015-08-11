<?php
namespace Pangaea\Attribute;

use \Pangaea\Date;
use \Pangaea\Xml;
use \Pangaea\RenderableInterface;

class NameValueAttribute implements RenderableInterface
{
    /**
     * The attribute's XML element structure.
     *
     * @var array
     */
    private $elements = [
        'name' => null,
        'type' => null,
        'value' => [
            'value' => [],
        ],
    ];

    /**
     * Create a basic NameValueAttribute.
     *
     * @param $name
     * @param mixed $values
     */
    public function __construct($name, $values)
    {
        $this->setName($name);
        $this->setValue($values);
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
     * Set the attribute value (and infer the type automatically).
     *
     * @param $value
     */
    public function setValue($values)
    {
        $values = (array) $values;

        if (count($values) === 0) {
            return;
        }

        $this->elements['type']           = Xml::attributeType(reset($values));
        $this->elements['value']['value'] = [];

        foreach ($values as $value) {
            if ('DATE' === $this->elements['type'] && ! Date::isEmpty($value)) {
                $value = Date::format($value);
            }

            $this->elements['value']['value'][] = $value;
        }
    }

    /**
     * Return whether or not the node is empty (has no values).
     *
     * @return bool
     */
    public function isEmpty()
    {
        if (count($this->elements['value']['value']) === 0) {
            return true;
        }

        foreach ($this->elements['value']['value'] as $value) {
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

        $valuesXml = '';

        foreach ($this->elements['value']['value'] as $value) {
            $valuesXml .= '<value><value>'. Xml::escape($value) . '</value></value>';
        }

        return <<< XML
<NameValueAttribute>
    <name>{$this->elements['name']}</name>
    <type>{$this->elements['type']}</type>
    {$valuesXml}
</NameValueAttribute>
XML;
    }
}
