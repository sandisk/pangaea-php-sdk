<?php
namespace Pangaea\Attribute;

interface AttributeInterface
{
    /**
     * Create an attribute.
     *
     * @param $name
     * @param mixed $values
     */
    public function __construct($name, $value = null);

    /**
     * Set the name.
     *
     * @param $name
     */
    public function setName($name);

    /**
     * Get the name.
     *
     * @return mixed
     */
    public function getName();

    /**
     * Set the attribute value (and infer the type automatically).
     *
     * @param $value
     */
    public function setValue($value);

    /**
     * Get the value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Get the type.
     *
     * @return mixed
     */
    public function getType();

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
