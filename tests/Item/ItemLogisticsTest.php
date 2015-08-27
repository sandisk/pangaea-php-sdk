<?php
namespace Pangaea\Test\Item;

use \PHPUnit_Framework_TestCase;
use \Pangaea\Item\ItemLogistics;

class ItemLogisticsTest extends \PHPUnit_Framework_TestCase
{
    public function testItemLogisticsProductAttributes()
    {
        $itemLogistics = new ItemLogistics;
        $itemLogistics->setLegacyDistributorId(12345);
        $itemLogistics->setShipNodeSupply(12345678, 123456);

        $attributes = $itemLogistics->getProductAttributes();

        $this->assertCount(3, $attributes);
        $this->assertEquals('12345', $attributes[0]->getValue()[0]);
        $this->assertEquals('12345678', $attributes[1]->getValue()[0]);
        $this->assertEquals('123456', $attributes[2]->getValue()[0]);
    }

    public function testItemLogisticsPartialProductAttributes()
    {
        $itemLogistics = new ItemLogistics;
        $itemLogistics->setLegacyDistributorId(12345);

        $attributes = $itemLogistics->getProductAttributes();

        $this->assertCount(1, $attributes);
        $this->assertEquals('12345', $attributes[0]->getValue()[0]);
    }

    public function testItemLogisticsBlankProductAttributes()
    {
        $itemLogistics = new ItemLogistics;
        $this->assertEquals([], $itemLogistics->getProductAttributes());
    }

    /**
     * @expectedException        \Pangaea\PangaeaException
     * @expectedExceptionMessage Unit cost currency cannot be blank
     */
    public function testBlankItemLogisticsCurrencyException()
    {
        $itemLogistics = new ItemLogistics;
        $itemLogistics->setUnitCost(123.99, '');
    }

    /**
     * @expectedException              \Pangaea\PangaeaException
     * @expectedExceptionMessageRegExp /Invalid unit cost currency ".*"/
     */
    public function testInvalidItemLogisticsCurrencyException()
    {
        $itemLogistics = new ItemLogistics;
        $itemLogistics->setUnitCost(123.99, 'FOOBAR');
    }
}
