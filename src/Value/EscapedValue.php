<?php
namespace Pangaea\Value;

use \Pangaea\Xml;
use \Pangaea\Value\AbstractValue;

class EscapedValue extends AbstractValue
{
    /**
     * Get the value of the object.
     *
     * @return mixed
     */
    public function getValue()
    {
        return Xml::escape($this->value);
    }
}
