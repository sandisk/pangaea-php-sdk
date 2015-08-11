<?php
namespace Pangaea\Test;

use \DOMDocument;

trait FixtureLoadTrait
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
