[![Build Status](https://semaphoreci.com/api/v1/projects/f79de448-d5c0-4e0f-b0e6-a80df6d7906e/490306/badge.svg)](https://semaphoreci.com/gabriel403/pangaea-php-sdk)


PHP library for working with Pangaea platform
===

Produces data feeds for Walmarts global e-commerce platform, Pangaea:

- Product items as XML
- Category taxonomy as JSON

XML files are automatically validated against the included XSD schema files.


Requirements
---

This library requires PHP 5.6+ and the [multibyte string extension](http://php.net/manual/en/book.mbstring.php) to be installed.


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
