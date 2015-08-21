<?php
namespace Pangaea\Value;

use \DateTime;
use \Pangaea\Xml;
use \Pangaea\Value\AbstractValue;

class DateValue extends AbstractValue
{
    /**
     * Pangaea default date format.
     *
     * @const
     */
    const DATE_FORMAT = 'Y-m-d\TH:i:s.000P';

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
        if (! is_null($this->value)) {
            return (new DateTime($this->value))->format(static::DATE_FORMAT);
        }
    }

    /**
     * Is the value considered empty.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return mb_strlen($this->value) === 0 || '0000-00-00' === substr($this->value, 0, 10);
    }
}
