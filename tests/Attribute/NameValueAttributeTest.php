<?php
namespace Pangaea\Test\Attribute;

use \PHPUnit_Framework_TestCase;
use \Pangaea\Attribute\NameValueAttribute;

class NameValueAttributeTest extends \PHPUnit_Framework_TestCase
{
    protected function buildExpectedXml(array $elements)
    {
        $defaults = [
            'name'  => null,
            'value' => null,
            'type'  => null,
        ];

        $elements          = array_merge($defaults, $elements);
        $elements['value'] = (array) $elements['value'];

        $valuesXml = '';

        foreach ($elements['value'] as $value) {
            $valuesXml .= '<value><value>' . $value . '</value></value>';
        }

        return <<< XML
<NameValueAttribute>
    <name>{$elements['name']}</name>
    <type>{$elements['type']}</type>
    {$valuesXml}
</NameValueAttribute>
XML;
    }

    public function testNameValueAttributeBasicRender()
    {
        $attribute = new NameValueAttribute('string', 'test');

        $expectedXml = $this->buildExpectedXml([
            'name'  => 'string',
            'value' => 'test',
            'type'  => 'STRING',
        ]);

        $this->assertEquals($expectedXml, $attribute->render());
    }

    public function testNameValueAttributeGetters()
    {
        $attribute = new NameValueAttribute('list', ['FOO', 'BAR']);

        $this->assertEquals('list', $attribute->getName());
        $this->assertEquals(['FOO', 'BAR'], $attribute->getValue());
    }

    public function testNameValueAttributeValueBoolean()
    {
        $attribute = new NameValueAttribute('boolean', true);

        $expectedXml = $this->buildExpectedXml([
            'name'    => 'boolean',
            'value'   => 'true',
            'type'    => 'BOOLEAN',
        ]);

        $this->assertEquals($expectedXml, $attribute->render());
    }

    public function testNameValueAttributeValueDate()
    {
        $attribute = new NameValueAttribute('date', '2015-01-01');

        $expectedXml = $this->buildExpectedXml([
            'name'    => 'date',
            'value'   => '2015-01-01T00:00:00.000+00:00',
            'type'    => 'DATE',
        ]);

        $this->assertEquals($expectedXml, $attribute->render());
    }

    public function testNameValueAttributeValueArray()
    {
        $attribute = new NameValueAttribute('list', ['FOO', 'BAR']);

        $expectedXml = $this->buildExpectedXml([
            'name'    => 'list',
            'value'   => ['FOO', 'BAR'],
            'type'    => 'STRING',
        ]);

        $this->assertEquals($expectedXml, $attribute->render());
    }

    /**
     * @expectedException         \Pangaea\PangaeaException
     * @expectedExceptionMessage  NameValueAttribute element "name" cannot be empty
     */
    public function testNameValueAttributeValueNameException()
    {
        $attribute = new NameValueAttribute('', 'foobar');
    }
}
