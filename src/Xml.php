<?php
namespace Pangaea;

use \DOMDocument;
use \Pangaea\PangaeaException;
use \Pangaea\Xml;

class Xml
{
    /**
     * Escapes the value suitable for inclusion in XML and converts booleans to 'true'/'false' strings
     *
     * @param $value
     * @return string
     */
    public static function escape($value)
    {
        if (is_bool($value)) {
            return ($value ? 'true' : 'false');
        } else {
            $value = html_entity_decode($value); // not sure why, other than back compat

            return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', false);
        }
    }

    /**
     * Determine the type of a value.
     *
     * @param $value
     * @return string
     */
    public static function attributeType($value)
    {
        if (is_bool($value)) {
            return 'BOOLEAN';
        } elseif (is_float($value)) {
            return 'DECIMAL';
        } elseif (is_int($value)) {
            return 'INTEGER';
        } elseif (preg_match('/\d{4}-\d{2}-\d{2}/', substr($value, 0, 10))) {
            return 'DATE'; // @todo: better to insist on passing DateTime objects?
        } else {
            return 'STRING';
        }
    }

    /**
     * Validates the XML file against the specified XSD schema
     * Also formats/normalizes the file to improve human readability and ease diff'ing
     * Formatting will only occur if the XML is valid, otherwise DOMDocument truncates the data making debugging impossible.
     *
     * @param $filePath
     * @param $schemaPath
     * @throws PangaeaException
     */
    public static function validate($filePath, $schemaPath)
    {
        if (! file_exists($filePath)) {
            throw new PangaeaException('XML file missing');
        } elseif (! is_readable($filePath)) {
            throw new PangaeaException('XML file is not readable');
        }

        if (! file_exists($schemaPath)) {
            throw new PangaeaException('XSD schema missing');
        } elseif (! is_readable($schemaPath)) {
            throw new PangaeaException('XSD file is not readable');
        }

        libxml_use_internal_errors(true);

        set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
            throw new PangaeaException($errstr, $errno);
        });

        // possibly more efficient to pass a $dom object rather than save/reload, but cleaner to assume already saved
        $dom                     = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->load($filePath);
        $dom->normalizeDocument();

        restore_error_handler();

        if (! $dom->schemaValidate(realpath($schemaPath))) {
            throw new PangaeaException(static::errorSummary());
        }

        return $dom->save($filePath) > 0;
    }

    /**
     * Summarises `libxml_get_errors()`, grouping the line numbers each unique error occurred on
     *
     * @return string
     */
    private static function errorSummary()
    {
        $errors  = libxml_get_errors();
        $concise = [];
        $summary = '';

        foreach ($errors as $error) {
            $concise[$error->message][] = $error->line;
        }

        foreach ($concise as $error => $lines) {
            $summary .= trim($error) . PHP_EOL . "\tLines: " . implode(', ', $lines) . PHP_EOL . PHP_EOL;
        }

        return trim($summary);
    }
}
