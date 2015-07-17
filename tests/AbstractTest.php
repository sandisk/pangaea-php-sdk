<?php
namespace Pangaea\Test;

use \PHPUnit_Framework_TestCase;
use \DOMDocument;

abstract class AbstractTest extends PHPUnit_Framework_TestCase
{
    protected function loadXmlFixture($filename)
    {
        return file_get_contents(__DIR__ . '/fixtures/' . ltrim($filename, '/'));
    }

    protected function loadJsonFixture($filename)
    {
        return file_get_contents(__DIR__ . '/fixtures/' . ltrim($filename, '/'));
    }
}
