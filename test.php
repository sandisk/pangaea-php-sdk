<?php
require_once __DIR__ . '/vendor/autoload.php';

require 'tests/categories.php'; // categories JSON
require 'tests/items.php'; // products XML

foreach (['categories.json', 'items.xml'] as $file) {
    $exampleHash = sha1_file(__DIR__ . '/examples/' . $file);
    $testHash    = sha1_file(__DIR__ . '/out/' . $file);

    echo $file . ($exampleHash === $testHash ? ' passed' : ' failed') . PHP_EOL;
}
