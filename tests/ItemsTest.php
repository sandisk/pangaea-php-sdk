<?php
namespace Pangaea\Test;

use \PHPUnit_Framework_TestCase;
use \Pangaea\Feed;
use \Pangaea\Item;
use \Pangaea\Item\ItemLogistics;
use \Pangaea\PangaeaException;
use \Pangaea\Attribute\VariantMetaDataAttribute;

class ItemsTest extends PHPUnit_Framework_TestCase
{
    use \Pangaea\Test\FixtureLoadTrait;

    protected $feed;

    protected $item;

    public function setUp()
    {
        $feed = new Feed('2015-01-01 12:34:56');

        $variations = [
            ['sku' => 'SKU123', 'upc' => '5000000000123', 'productId' => 'P123', 'primary' => true],
            ['sku' => 'SKU456', 'upc' => '5000000000456', 'productId' => 'P123', 'primary' => false],
            ['sku' => 'SKU787', 'upc' => '5000000000789', 'productId' => 'P123', 'primary' => false],
        ];

        $colours = [
            new VariantMetaDataAttribute('colour', 'red',    'LOCATOR', 0),
            new VariantMetaDataAttribute('colour', 'orange', 'LOCATOR', 1),
            new VariantMetaDataAttribute('colour', 'yellow', 'LOCATOR', 2),
            new VariantMetaDataAttribute('colour', 'green',  'LOCATOR', 3),
            new VariantMetaDataAttribute('colour', 'blue',   'LOCATOR', 4),
            new VariantMetaDataAttribute('colour', 'indigo', 'LOCATOR', 5),
            new VariantMetaDataAttribute('colour', 'violet', 'LOCATOR', 6),
        ];

        $sizes = [
            new VariantMetaDataAttribute('size', 'XS',  'DEFAULT', 0),
            new VariantMetaDataAttribute('size', 'S',   'DEFAULT', 1),
            new VariantMetaDataAttribute('size', 'M',   'DEFAULT', 2),
            new VariantMetaDataAttribute('size', 'L',   'DEFAULT', 3),
            new VariantMetaDataAttribute('size', 'XL',  'DEFAULT', 4),
            new VariantMetaDataAttribute('size', 'XXL', 'DEFAULT', 5),
        ];

        foreach ($variations as $i => $variation) {
            $item = new Item($variation['sku'], $variation['upc']);
            $item->setTitle('Sample item');
            $item->setBrand('Brandtastic');
            $item->setDescriptions('Short description', 'Longer description about the item... •', true);
            $item->setTaxCode(20);
            $item->setDates('2015-01-01', '2025-01-01');
            $item->setPublishStatus('UNPUBLISHED');
            $item->setLifecycleStatus('ACTIVE');
            $item->setDimensions(50, 1.5, 74.67, 'CM');
            $item->setWeight(0.5, 'G');
            $item->setPricing(14.99, 9.99, 12.49, '2015-01-01');

            $item->addAttributes('Product', [
                'availability_flag' => true,
                'catalog_id'        => 'TestCatalog',
                'barcode_list'      => ['5000000000123', '5000000000456'],
                'online_from'       => '2015-01-01 12:34:56',
                'stock_quantity'    => 123,
                'profit_margin'     => 12.34,
                'export_excluded'   => null,
                'export_include'    => ''
            ]);

            $item->setProductSetupType($variation['primary'] ? 'PRIMARY' : 'VARIANT');
            $item->setVariantGroupId($variation['productId']);

            $item->addVariantMetaDataAttributes($colours);
            $item->addVariantMetaDataAttributes($sizes);

            $item->addAttributes('Compliance', [
                'over_18_age' => true
            ]);

            // example of some common attributes duplicated in multiple attribute groups, with an addition in the second...
            $common = ['sku' => 'SKU12345', 'is_international' => true];
            $item->addAttributes('MarketInProduct', $common);
            $item->addAttributes('MarketInOffer', array_merge(['addition' => true], $common));

            $item->addAttributes('Offer', [
                'pre_order' => true
            ]);

            $item->setAssets(['1.png', '2.png', '3.png'], 'http://example.com/image');

            $itemLogistics = new ItemLogistics;
            $itemLogistics->setUnitCost(123.99, 'GBP');
            $itemLogistics->setShipNodeSupply(12345678, 123456);
            $itemLogistics->setAssumeInfiniteInventory(true);
            $itemLogistics->setOnHandSafetyFactorQuantity(5, 'EA');
            $itemLogistics->setInventoryAvailabilityThreshold(1, 5, 999);
            $itemLogistics->setLegacyDistributorId('FOOBAR-TRADEPLACE');
            $itemLogistics->addPreferredDistributor(1, 'FOOBAR-TRADEPLACE', '2012-12-11 11:11:11', '2012-12-12 12:12:12');
            $itemLogistics->addPreferredDistributor(2, 'FOOBAR-CLIPPER', '2012-12-13 13:13:13', '2012-12-14 14:14:14');

            $item->setItemLogistics($itemLogistics);

            $feed->addItem($item);

            if ($i === 0) {
                $this->item = $item;
            }
        }

        $this->feed = $feed;
    }

    public function tearDown()
    {
        $this->feed = null;
        $this->item = null;
    }

    public function testItemsXml()
    {
        $sampleXml = $this->loadXmlFixture('items.xml');
        $outputXml = $this->feed->render();

        $this->assertXmlStringEqualsXmlString($sampleXml, $outputXml);
    }

    /**
     * @expectedException        \Pangaea\PangaeaException
     * @expectedExceptionMessage Publish status cannot be blank
     */
    public function testBlankPublishStatusException()
    {
        $this->item->setPublishStatus('');
    }

    /**
     * @expectedException              \Pangaea\PangaeaException
     * @expectedExceptionMessageRegExp /Invalid publish status ".*"/
     */
    public function testInvalidPublishStatusException()
    {
        $this->item->setPublishStatus('FOOBAR');
    }

    /**
     * @expectedException        \Pangaea\PangaeaException
     * @expectedExceptionMessage Lifecycle status cannot be blank
     */
    public function testBlankLifecycleStatusException()
    {
        $this->item->setLifecycleStatus('');
    }

    /**
     * @expectedException              \Pangaea\PangaeaException
     * @expectedExceptionMessageRegExp /Invalid lifecycle status ".*"/
     */
    public function testInvalidLifecycleStatusException()
    {
        $this->item->setLifecycleStatus('FOOBAR');
    }

    /**
     * @expectedException        \Pangaea\PangaeaException
     * @expectedExceptionMessage Shipping unit of measurement cannot be blank
     */
    public function testBlankShippingMeasurementException()
    {
        $this->item->setDimensions(12, 10, 5, '');
    }

    /**
     * @expectedException              \Pangaea\PangaeaException
     * @expectedExceptionMessageRegExp /Invalid shipping unit of measurement ".*"/
     */
    public function testInvalidShippingMeasurementException()
    {
        $this->item->setDimensions(12, 10, 5, 'FOOBAR');
    }

    /**
     * @expectedException        \Pangaea\PangaeaException
     * @expectedExceptionMessage Shipping unit of weight cannot be blank
     */
    public function testBlankWeightException()
    {
        $this->item->setWeight(42, '');
    }

    /**
     * @expectedException              \Pangaea\PangaeaException
     * @expectedExceptionMessageRegExp /Invalid shipping unit of weight ".*"/
     */
    public function testInvalidWeightException()
    {
        $this->item->setWeight(42, 'FOOBAR');
    }

    /**
     * @expectedException               \Pangaea\PangaeaException
     * @expectedExceptionMessageRegExp  /Entity 'bull' not defined/
     */
    public function testProductsInvalidEntitiesException()
    {
        $this->item->setTitle('product number 123 &bull;');

        $this->feed->addItem($this->item);
        $this->feed->save(__DIR__ . '/output/items.xml');
    }

    /**
     * @expectedException         \Pangaea\PangaeaException
     * @expectedExceptionMessage  Variant Meta Data must be an instance of VariantMetaDataAttribute
     */
    public function testProductsVariantMetaDataInvalidObjectException()
    {
        $invalidObject      = new \stdClass();
        $invalidObject->foo = 'bar';

        $this->item->addVariantMetaDataAttributes($invalidObject);
    }

    /**
     * @expectedException        \Pangaea\PangaeaException
     * @expectedExceptionMessage Cannot render item without an ItemLogistics element being set
     */
    public function testItemsWithoutItemLogisticsException()
    {
        $item = new Item('SKU123', '5000000000123');
        $item->render();
    }

    public function testSaveItemsXml()
    {
        $this->assertTrue($this->feed->save(__DIR__ . '/output/items.xml'));
    }
}
