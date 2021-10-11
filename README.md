Stuck Template
==============

PHP Templating engine.

Installation
------------

- With composer

`composer req eghojansu/php-template:dev-master`

Usage
-----

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$directories = './templates/'; // template directories, could be an array of directory or directories delimited by "," OR ";" OR "|"
$extensions = 'php'; // template extension
$templateManager = new Stuck\Template\Manager($directories);

echo $templateManager->render('view'); // will render './templates/view.php'
```

Features
--------

- PHP 8.0
- No Dependencies
- Globals
- Custom Helper
- Chainable
- Template Inheritance
- Escaping data by utilize [`htmlspecialchars`](https://www.php.net/manual/en/function.htmlspecialchars.php).

Disclaimer
----------

This is only simple php templating engine. The Code Coverage has reach 100% but not guaranted to be bug free.