PHP library for working with Pangaea platform
===

Produces data feeds for Walmarts global e-commerce platform, Pangaea:

- product items as XML
- category taxonomy as JSON

XML files are automatically validated against the included XSD schema files.


Install
---

Via Composer:

``` bash
$ composer require fusionspim/pangaea-php-sdk
```

Usage
---

To run the tests run `php vendor/bin/phpunit` from the project directory. Look in `tests/fixtures` for output examples.


Future plans
---

- Guzzle up the feed gateway
- PHPUnit and better understand how scales / memory usage
