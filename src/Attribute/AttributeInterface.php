<?php
namespace Pangaea\Attribute;

interface AttributeInterface
{
    /**
     * Create a basic NameValueAttribute.
     *
     * @param $name
     * @param mixed $values
     */
    public function __construct($name, $values);

    /**
     * Set the name
     *
     * @param $name
     */
    public function setName($name);

    /**
     * Set the attribute value (and infer the type automatically).
     *
     * @param $value
     */
    public function setValue($value);

    /**
     * Return whether or not the attribute is empty (has no values).
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Render XML and return as a string.
     *
     * @return string
     */
    public function render();
}
