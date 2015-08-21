<?php
namespace Pangaea\Value;

use \Pangaea\Xml;
use \Pangaea\Value\AbstractValue;

class EnumValue extends AbstractValue
{
    /**
     * Get the value of the object.
     *
     * @return mixed
     */
    public function getValue()
    {
        return Xml::escape(mb_strtoupper($this->value));
    }

    /**
     * Check if the value is within a range of options.
     *
     * @param array $options
     * return boolean
     */
    public function isValid(array $options)
    {
        if ($this->isEmpty()) {
            return false;
        }

        return in_array($this->getValue(), $options);
    }
}
