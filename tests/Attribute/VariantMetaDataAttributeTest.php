<?php
namespace Pangaea\Test\Attribute;

use \PHPUnit_Framework_TestCase;
use \Pangaea\PangaeaException;
use \Pangaea\Attribute\VariantMetaDataAttribute;

class VariantMetaDataAttributeTest extends \PHPUnit_Framework_TestCase
{
    protected function buildExpectedXml(array $elements)
    {
        $defaults = [
            'name'     => null,
            'value'    => null,
            'type'     => null,
            'variant'  => 'true',
            'resource' => 'DEFAULT',
            'rank'     => 0,
        ];

        $elements = array_merge($defaults, $elements);

return <<< XML
<NameValueAttribute>
    <name>{$elements['name']}</name>
    <type>{$elements['type']}</type>
    <isVariant>{$elements['variant']}</isVariant>
    <variantResourceType>{$elements['resource']}</variantResourceType>
    <value>
        <value>{$elements['value']}</value>
        <rank>{$elements['rank']}</rank>
        <isVariant>{$elements['variant']}</isVariant>
    </value>
</NameValueAttribute>
XML;
    }

    public function testVariantMetaDataAttributeBasicRender()
    {
        $attribute = new VariantMetaDataAttribute('colour', 'red');

        $expectedXml = $this->buildExpectedXml([
            'name'  => 'colour',
            'value' => 'red',
            'type'  => 'STRING',
        ]);

        $this->assertEquals($expectedXml, $attribute->render());
    }

    public function testVariantMetaDataAttributeResourceType()
    {
        $attribute = new VariantMetaDataAttribute('colour', 'red');
        $attribute->setResourceType('LOCATOR');

        $expectedXml = $this->buildExpectedXml([
            'name'     => 'colour',
            'value'    => 'red',
            'type'     => 'STRING',
            'resource' => 'LOCATOR',
        ]);

        $this->assertEquals($expectedXml, $attribute->render());
    }

    /**
     * @expectedException              \Pangaea\PangaeaException
     * @expectedExceptionMessageRegExp /Invalid resource type ".*"/
     */
    public function testVariantMetaDataAttributeInvalidResourceTypeException()
    {
        $attribute = new VariantMetaDataAttribute('colour', 'red');
        $attribute->setResourceType('FOOBAR');
    }

    public function testVariantMetaDataAttributeIsVariant()
    {
        $attribute = new VariantMetaDataAttribute('colour', 'red');
        $attribute->isVariant(false);

        $expectedXml = $this->buildExpectedXml([
            'name'    => 'colour',
            'value'   => 'red',
            'type'    => 'STRING',
            'variant' => 'false',
        ]);

        $this->assertEquals($expectedXml, $attribute->render());
    }

    public function testVariantMetaDataAttributeValueBoolean()
    {
        $attribute = new VariantMetaDataAttribute('boolean', true);

        $expectedXml = $this->buildExpectedXml([
            'name'    => 'boolean',
            'value'   => 'true',
            'type'    => 'BOOLEAN',
        ]);

        $this->assertEquals($expectedXml, $attribute->render());
    }

    public function testVariantMetaDataAttributeValueDate()
    {
        $attribute = new VariantMetaDataAttribute('date', '2015-01-01');

        $expectedXml = $this->buildExpectedXml([
            'name'    => 'date',
            'value'   => '2015-01-01T00:00:00.000+00:00',
            'type'    => 'DATE',
        ]);

        $this->assertEquals($expectedXml, $attribute->render());
    }

    public function testVariantMetaDataAttributeRank()
    {
        $attribute = new VariantMetaDataAttribute('colour', 'red');
        $attribute->setRank(10);

        $expectedXml = $this->buildExpectedXml([
            'name'    => 'colour',
            'value'   => 'red',
            'type'    => 'STRING',
            'rank'    => '10',
        ]);

        $this->assertEquals($expectedXml, $attribute->render());
    }
}
