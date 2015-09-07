<?php
namespace Pangaea;

class Pangaea
{
    /**
     * Default End Date (as specified by Walmart).
     *
     * @const
     */
    const SPEC_DEFAULT_END_DATE  = '2049-12-31';

    /**
     * Valid Lifecycle Statuses.
     *
     * @const
     */
    const LIFECYCLE_STATUSES = [
        'ACTIVE',
        'ARCHIVED',
        'RETIRED',
    ];

    /**
     * Valid Published Statuses.
     *
     * @const
     */
    const PUBLISHED_STATUSES = [
        'IN_PROGRESS',
        'READY_TO_PUBLISH',
        'PUBLISHED',
        'UNPUBLISHED',
        'SYSTEM_PROBLEM',
        'STAGE',
    ];

    /**
     * Valid Product Setup Types.
     */
    const PRODUCT_SETUP_TYPES = [
        'STANDALONE',
        'PRIMARY',
        'VARIANT',
        'BUNDLE',
    ];

    /**
     * Valid units of measurement.
     *
     * @const
     */
    const UNITS_MEASUREMENT = [
        'EA',
        'FT',
        'IN',
        'INCH',
        'YD',
        'M',
        'CM',
        'MM',
        'KG',
        'G',
        'MG',
        'POUND',
        'LB',
        'OZ',
        'FOZ',
        'GAL',
        'QT',
        'PT',
        'IMPGAL',
        'IMPQT',
        'IMPPT',
        'L',
        'ML',
        'CC',
        'CBM',
        'CFT',
        'CYD',
        'CIN',
        'SM',
        'SFT',
        'SYD',
        'SIN',
        'SCM',
        'SMM',
    ];

    /**
     * Valid units of weight.
     *
     * @const
     */
    const UNITS_WEIGHT = [
        'KG',
        'G',
        'MG',
        'LB',
        'OZ',
    ];

    /**
     * Valid Unit Cost Currencies.
     *
     * @const
     */
    const UNIT_COST_CURRENCIES = [
        'AED',
        'AFN',
        'ALL',
        'AMD',
        'ANG',
        'AOA',
        'ARS',
        'AUD',
        'AWG',
        'AZN',
        'BAM',
        'BBD',
        'BDT',
        'BGN',
        'BHD',
        'BIF',
        'BMD',
        'BND',
        'BOB',
        'BRL',
        'BSD',
        'BTN',
        'BWP',
        'BYR',
        'BZD',
        'CAD',
        'CDF',
        'CHF',
        'CLP',
        'CNY',
        'COP',
        'CRC',
        'CUP',
        'CVE',
        'CZK',
        'DJF',
        'DKK',
        'DOP',
        'DZD',
        'EGP',
        'ERN',
        'ETB',
        'EUR',
        'FJD',
        'FKP',
        'GBP',
        'GEL',
        'GHS',
        'GIP',
        'GMD',
        'GNF',
        'GTQ',
        'GYD',
        'HKD',
        'HNL',
        'HRK',
        'HTG',
        'HUF',
        'IDR',
        'ILS',
        'INR',
        'IQD',
        'IRR',
        'ISK',
        'JMD',
        'JOD',
        'JPY',
        'KES',
        'KGS',
        'KHR',
        'KMF',
        'KPW',
        'KRW',
        'KWD',
        'KYD',
        'KZT',
        'LAK',
        'LBP',
        'LKR',
        'LRD',
        'LSL',
        'LTL',
        'LVL',
        'LYD',
        'MAD',
        'MDL',
        'MGA',
        'MKD',
        'MMK',
        'MNT',
        'MOP',
        'MRO',
        'MUR',
        'MVR',
        'MWK',
        'MXN',
        'MYR',
        'MZN',
        'NAD',
        'NGN',
        'NIO',
        'NOK',
        'NPR',
        'NZD',
        'OMR',
        'PAB',
        'PEN',
        'PGK',
        'PHP',
        'PKR',
        'PLN',
        'PYG',
        'QAR',
        'RON',
        'RSD',
        'RUB',
        'RUR',
        'RWF',
        'SAR',
        'SBD',
        'SCR',
        'SDG',
        'SEK',
        'SGD',
        'SHP',
        'SLL',
        'SOS',
        'SRD',
        'STD',
        'SYP',
        'SZL',
        'THB',
        'TJS',
        'TMT',
        'TND',
        'TOP',
        'TRY',
        'TTD',
        'TWD',
        'TZS',
        'UAH',
        'UGX',
        'USD',
        'UYU',
        'UZS',
        'VEF',
        'VND',
        'VUV',
        'WST',
        'XAF',
        'XAG',
        'XAU',
        'XBA',
        'XBB',
        'XBC',
        'XBD',
        'XCD',
        'XDR',
        'XFU',
        'XOF',
        'XPD',
        'XPF',
        'XPT',
        'XTS',
        'XXX',
        'YER',
        'ZAR',
        'ZMK',
        'ZWL',
    ];
}
