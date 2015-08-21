<?php
namespace Pangaea\Utils;

use \Pangaea\Value\DateValue;
use \Pangaea\Value\EnumValue;
use \Pangaea\Value\EscapedValue;
use \Pangaea\Value\RawValue;
use \Pangaea\Value\TaxValue;
use \Pangaea\Value\ValueInterface;

class Value
{
    /**
     * A helper for creating value objects.
     *
     * @param null $value
     * @return mixed
     */
    public static function value($value = null)
    {
        if ($value instanceof ValueInterface) {
            return $value;
        }

        return static::escaped($value);
    }

    /**
     * A helper for creating escaped value objects.
     *
     * @param null $value
     * @return EscapedValue
     */
    public static function escaped($value = null)
    {
        if ($value instanceof EscapedValue) {
            return $value;
        }

        return new EscapedValue($value);
    }

    /**
     * A helper for creating raw value objects.
     *
     * @param null $value
     * @return RawValue
     */
    public static function raw($value = null)
    {
        if ($value instanceof RawValue) {
            return $value;
        }

        return new RawValue($value);
    }

    /**
     * A helper for creating tax value objects.
     *
     * @param null $value
     * @return TaxValue
     */
    public static function tax($value = null)
    {
        if ($value instanceof TaxValue) {
            return $value;
        }

        return new TaxValue($value);
    }

    /**
     * A helper for creating date value objects.
     *
     * @param null $value
     * @return DateValue
     */
    public static function date($value = null)
    {
        if ($value instanceof DateValue) {
            return $value;
        }

        return new DateValue($value);
    }

    /**
     * A helper for creating enum value objects.
     *
     * @param null $value
     * @return EnumValue
     */
    public static function enum($value = null)
    {
        if ($value instanceof EnumValue) {
            return $value;
        }

        return new EnumValue($value);
    }
}
