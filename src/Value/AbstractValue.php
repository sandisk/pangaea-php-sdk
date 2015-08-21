<?php
namespace Pangaea\Value;

abstract class AbstractValue implements ValueInterface
{
    /**
     * The value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Create an escaped value object.
     *
     * @param null $value
     */
    public function __construct($value = null)
    {
        $this->setValue($value);
    }

    /**
     * Set the value of the object.
     *
     * @param null $value
     */
    public function setValue($value = null)
    {
        $this->value = $value;
    }

    /**
     * Get the value of the object.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the raw value of the object.
     *
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->value;
    }

    /**
     * Is the value considered empty.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return mb_strlen($this->value) === 0;
    }

    /**
     * Get value as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}
