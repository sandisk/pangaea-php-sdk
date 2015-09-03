<?php
namespace Pangaea;

use \DateTime;

class Date
{
    /**
     * Date Format.
     *
     * @const
     */
    const DATE_FORMAT = 'Y-m-d\TH:i:s.000P';

    /**
     * Formats a date into Pangaea spec format, see static::DATE_FORMAT
     *
     * @param $date
     * @return string
     */
    public static function format($date = null)
    {
        if (is_null($date)) {
            $date = (new DateTime())->format('Y-m-d H:i:s');
        }

        return (new DateTime($date))->format(static::DATE_FORMAT);
    }

    /**
     * Check whether or not a date is empty.
     *
     * @param $date
     * @return bool
     */
    public static function isEmpty($date)
    {
        return mb_strlen($date) === 0 || '0000-00-00' === substr($date, 0, 10);
    }
}
