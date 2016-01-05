## Laravel IPBoard API
[![Packagist License](https://poser.pugx.org/alawrence/laravel-ipboardapi/license.png)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://poser.pugx.org/alawrence/laravel-ipboardapi/version.png)](https://packagist.org/packages/A-Lawrence/laravel-ipboardapi)
[![Latest Unstable Version](https://poser.pugx.org/alawrence/laravel-ipboardapi/v/unstable)](https://packagist.org/packages/A-Lawrence/laravel-ipboardapi)
[![Total Downloads](https://poser.pugx.org/alawrence/laravel-ipboardapi/d/total.png)](https://packagist.org/packages/A-Lawrence/laravel-ipboardapi)

This package includes accessor methods for all common IPBoard API calls:
 - API Call 1
 - API Call 2
 - API Call 3

## Installation

Require this package with composer:

```
composer require alawrence/laravel-ipboardapi
```

After updating composer, add this package's ServiceProvider to the providers array in config/app.php

### Laravel 5.x:

ServiceProvider:
```php
Alawrence\Ipboardapi\ServiceProvider::class,
```

Facade:
```php
'Ipboardapi' => Alawrence\Ipboardapi\Facade::class,
```

In order to set the required variables for your instance of IPBoard, you must first publish the configuration files:

```
php artisan vendor:publish
```

## Usage

Detail the useage here once written.