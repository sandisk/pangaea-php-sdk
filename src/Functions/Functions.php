<?php
namespace Pangaea\Functions;

use \Pangaea\Value\DateValue;
use \Pangaea\Value\EnumValue;
use \Pangaea\Value\EscapedValue;
use \Pangaea\Value\RawValue;
use \Pangaea\Value\TaxValue;
use \Pangaea\Value\ValueInterface;

/**
 * A helper for creating value objects.
 *
 * @param null $value
 * @return mixed
 */
function value($value = null)
{
    if ($value instanceof ValueInterface) {
        return $value;
    }

    return escaped_value($value);
}

/**
 * A helper for creating escaped value objects.
 *
 * @param null $value
 * @return EscapedValue
 */
function escaped_value($value = null)
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
function raw_value($value = null)
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
function tax_value($value = null)
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
function date_value($value = null)
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
function enum_value($value = null)
{
    if ($value instanceof EnumValue) {
        return $value;
    }

    return new EnumValue($value);
}
