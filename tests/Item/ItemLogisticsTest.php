<?php
namespace Pangaea\Test\Item;

use \PHPUnit_Framework_TestCase;
use \Pangaea\Item\ItemLogistics;

class ItemLogisticsTest extends \PHPUnit_Framework_TestCase
{
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
