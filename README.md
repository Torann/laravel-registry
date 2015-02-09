# Registry Manager for Laravel

[![Latest Stable Version](https://poser.pugx.org/torann/registry/v/stable.png)](https://packagist.org/packages/torann/registry) [![Total Downloads](https://poser.pugx.org/torann/registry/downloads.png)](https://packagist.org/packages/torann/registry)

Registry manager for Laravel. An alternative for managing application configurations and settings. Now with the magic of caching, so no more database calls to simply get site setting.

----------

## Installation

- [Registry on Packagist](https://packagist.org/packages/torann/registry)
- [Registry on GitHub](https://github.com/Torann/laravel-registry)

Add the following into your `composer.json` file:

```json
{
    "require": {
        "torann/registry": "0.1.*@dev"
    }
}
```

Add the service provider and alias into your `app/config/app.php`

```php
'providers' => array(
    'Torann\Registry\RegistryServiceProvider',
),

'Registry' => 'Torann\Registry\Facades\Registry',
```

Create configuration file using artisan

```
php artisan config:publish torann/registry
```

Run `php artisan migrate --package="torann/registry"` to install the registry table

## Usage

**Retrieve an item from the registry**

```php
Registry::get('foo'); \\will return null if key does not exists
Registry::get('foo.bar'); \\will return null if key does not exists

Registry::get('foo', 'undefined') \\will return undefined if key does not exists
```

**Store item into registry**

```php
Registry::set('foo', 'bar');
Registry::set('foo', array('bar' => 'foobar'));

Registry::get('foo'); \\bar
Registry::get('foo.bar'); \\foobar
```

**Remove item from registry**

```php
Registry::forget('foo');
Registry::forget('foo.bar');
```

**Flush registry**

```php
Registry::flush();
```

**Mass update**

```php
$settings = array(
    'site_name' => 'FooBar, Inc.', 
    'address'   => '11 Bean Street', 
    'email'     => 'foo@bar.com'
);

Registry::store($settings);
```

## Custom Timestamp Managers

For instance when multiple web servers are sharing a database we need to ensure the settings are all in sync. To accomplish this we use timestamp managers. The master timestamp is held in **Redis** and compared against the cached registry's timestamp, if the cached version is expired the system reloads the registry from the database and caches them.

To write a custom timestamp manager implement `Torann\Registry\Timestamps\TimestampInterface` and include your class in the registry settings `timestamp_manager`.

## Change Log

#### v0.1.3

- Added timestamp managers for multi-instance websites
- Added custom caching

#### v0.1.2

- Added config for custom table name
- Added forced variable types 
- Code cleanup

#### v0.1.1

- Bug fixes

#### v0.1.0

- First release