<?php
namespace Pangaea\Test;

use \Pangaea\Test\AbstractTest;
use \Pangaea\Feed;
use \Pangaea\Item;
use \Pangaea\PangaeaException;

class ItemsTest extends AbstractTest
{
    protected $feed;

    public function setUp()
    {
        $feed = new Feed('2015-01-01 12:34:56');

        $item = new Item('SKU123', '5000000000123');
        $item->setTitle('Sample item');
        $item->setBrand('Brandtastic');
        $item->setDescriptions('Short description', 'Longer description about the item...');
        $item->setTaxCode(20);
        $item->setDates('2015-01-01', '2025-01-01');
        $item->setStatus(true, false);
        $item->setDimensions(50, 1.5, 74.67, 'CM');
        $item->setWeight(0.5, 'G');
        $item->setPricing(14.99, 9.99, 12.49, '2015-01-01');

        $item->setAttributes('Product', [
            'availability_flag' => true,
            'catalog_id'        => 'TestCatalog',
            'barcode_list'      => ['5000000000123', '5000000000456'],
            'online_from'       => '2015-01-01 12:34:56',
            'stock_quantity'    => 123,
            'profit_margin'     => 12.34,
            'export_excluded'   => null,
            'export_include'    => ''
        ]);

        $item->setAttributes('Compliance', [
            'over_18_age' => true
        ]);

        // example of some common attributes duplicated in multiple attribute groups, with an addition in the second...
        $common = ['sku' => 'SKU12345', 'is_international' => true];
        $item->setAttributes('MarketInProduct', $common);
        $item->setAttributes('MarketInOffer', array_merge(['addition' => true], $common));

        $item->setAttributes('Offer', [
            'pre_order' => true
        ]);

        $item->setAssets(['1.png', '2.png', '3.png'], 'http://example.com/image');

        $item->setItemLogistics(12345, 12345678, 123456);

        $feed->addItem($item);

        $this->feed = $feed;
    }

    public function tearDown()
    {
        $this->feed = null;
    }

    public function testItemsXml()
    {
        $sampleXml = $this->loadXmlFixture('items.xml');
        $outputXml = $this->feed->getXml();

        $this->assertXmlStringEqualsXmlString($sampleXml, $outputXml);
    }

    public function testSaveItemsXml()
    {
        $this->assertTrue($this->feed->save(__DIR__ . '/output/items.xml'));
    }
}
