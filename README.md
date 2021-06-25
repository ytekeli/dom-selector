DOM Selector
==========================

[![Build Status](https://scrutinizer-ci.com/g/ytekeli/dom-selector/badges/build.png?b=master)](https://travis-ci.com/ytekeli/dom-selector)
[![Code Coverage](https://scrutinizer-ci.com/g/ytekeli/dom-selector/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ytekeli/dom-selector/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ytekeli/dom-selector/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ytekeli/dom-selector/?branch=master)

An HTML DOM selector and extractor with using YAML config.
* Free software: MIT license

Install
-------
Install the latest version using composer.

```bash
$ composer require ytekeli/dom-selector
```

This package can be found on [packagist](https://packagist.org/packages/ytekeli/dom-selector) and is best loaded using [composer](http://getcomposer.org/). We support php 7.3, 7.4 and 8.0.

Example
--------
You can find many examples of how to use the DOM Selector in the tests directory.

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";

use DOMSelector\DOMSelector;

$yaml_string = '
title:
    css: "h1"
    type: Text
link:
    css: "h2 a"
    type: Link';

$selector = DOMSelector::fromYamlString($yaml_string);
$extracted = $selector->extract('<h1>Title</h1><h2>Usage <a class="headerlink" href="https://example.com">¶</a></h2>');

print_r($extracted);
```

```pre
// output

Array
(
    [title] => Title
    [link] => https://example.com
)
```

We strongly inspired by [selectorlib](https://github.com/scrapehero/selectorlib) written with python.