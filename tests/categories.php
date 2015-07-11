<?php
use \Pangaea\Category;
use \Pangaea\PangaeaException;
use \Pangaea\Taxonomy;

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

try {
    $taxonomy->save('out/categories.json');
} catch (PangaeaException $e) {
    echo $e->getMessage();
}
