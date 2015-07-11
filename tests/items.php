<?php
use \Pangaea\Feed;
use \Pangaea\Item;
use \Pangaea\PangaeaException;

$feed = new Feed('2015-01-01 12:34:56');

$item = new Item('SKU123', '5000000000123');
$item->setTitle('Sample item');
$item->setDescriptions('Short description', 'Longer description about the item...');
$item->setTaxCode(20);
$item->setDates('2015-01-01', '2025-01-01');
$item->setStatus(true, false);
$item->setShipping(50, 1.5, 74.67, 0.5);
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

$feed->addItem($item);

try {
    $feed->save('out/items.xml');
} catch (PangaeaException $e) {
    echo $e->getMessage();
}
