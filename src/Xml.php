<?php
namespace Pangaea;

use \DOMDocument;
use \Pangaea\PangaeaException;

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
     * Validates the XML file against the specified XSD schema
     * Also formats/normalizes the file to improve human readability and ease diff'ing
     *
     * @param $filePath
     * @param $schemaPath
     * @throws PangaeaException
     */
    public static function validate($filePath, $schemaPath)
    {
        if (! file_exists($filePath)) {
            throw new PangaeaException('XML file missing');
        }

        if (! file_exists($schemaPath)) {
            throw new PangaeaException('XSD schema missing');
        }

        libxml_use_internal_errors(true);

        set_error_handler(function ($number, $message, $file, $line, $context) {
            throw new PangaeaException($message, $number);
        });

        // possibly more efficient to pass a $dom object rather than save/reload, but cleaner to assume already saved
        $dom                     = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->load($filePath);
        $dom->normalizeDocument();
        $dom->save($filePath);

        restore_error_handler();

        if (! $dom->schemaValidate(realpath($schemaPath))) {
            throw new PangaeaException(static::errorSummary());
        }
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
