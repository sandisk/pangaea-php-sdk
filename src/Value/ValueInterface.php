<?php
namespace Pangaea\Value;

interface ValueInterface
{
    /**
     * Create a value object.
     *
     * @param null $value
     */
    public function __construct($value = null);

    /**
     * Set the value of the object.
     *
     * @param null $value
     */
    public function setValue($value);

    /**
     * Get the value of the object.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Get the raw value of the object.
     *
     * @return mixed
     */
    public function getRawValue();

    /**
     * Is the value considered empty.
     *
     * @return boolean
     */
    public function isEmpty();
}
