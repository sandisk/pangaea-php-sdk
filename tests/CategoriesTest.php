<?php
namespace Pangaea\Test;

use \Pangaea\Test\AbstractTest;
use \Pangaea\Category;
use \Pangaea\PangaeaException;
use \Pangaea\Taxonomy;

class CategoriesTest extends AbstractTest
{
    public function testCategoriesJson()
    {
        $taxonomy = new Taxonomy;

        foreach (['CAT123', 'CAT456', 'CAT789'] as $index => $category) {
            $isEven = ($index % 2 == 0); // just to give test examples mock, but consistent values

            $item = new Category($category, $isEven);
            $item->setName('Category ' . $category);
            $item->setParent('CAT000', 'Root category');
            $item->setShelf(! $isEven);
            $item->setDates('2015-01-01 01:23:45', '2015-12-31 01:23:45');

            $taxonomy->addCategory($item);
        }

        $sampleJson = $this->loadJsonFixture('categories.json');
        $outputJson = $taxonomy->getJson();

        $this->assertJsonStringEqualsJsonString($sampleJson, $outputJson);
    }
}
