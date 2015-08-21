<?php
namespace Pangaea\Value;

use \Pangaea\Value\AbstractValue;

class TaxValue extends AbstractValue
{
    /**
     * Set the value of the object.
     *
     * @param null $value
     */
    public function setValue($value = null)
    {
        $this->value = (int) $value;
    }

    /**
     * Get the value of the object.
     *
     * @return mixed
     */
    public function getValue()
    {
        return number_format($this->value * 100, 2);
    }
}
