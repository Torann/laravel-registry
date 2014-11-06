# Registry Manager for Laravel - Alpha

[![Latest Stable Version](https://poser.pugx.org/torann/registry/v/stable.png)](https://packagist.org/packages/torann/registry) [![Total Downloads](https://poser.pugx.org/torann/registry/downloads.png)](https://packagist.org/packages/torann/registry)

Registry manager for Laravel. An alternative for managing application configurations and settings. Now with the magic of caching.

----------

## Installation

Add the following into your `composer.json` file:

```json
{
    "require": {
        "torann/registry": "dev-master"
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

Run `php artisan migrate --package="torann/registry"` to install the registry table

## Usage

Retrieve an item from the registry

```php
Registry::get('foo'); \\will return null if key does not exists
Registry::get('foo.bar'); \\will return null if key does not exists

Registry::get('foo', 'undefine') \\will return undefine if key does not exists
```

Store item into registry

```php
Registry::set('foo', 'bar');
Registry::set('foo', array('bar' => 'foobar'));

Registry::get('foo'); \\bar
Registry::get('foo.bar'); \\foobar
```

Remove item from registry

```php
Registry::forget('foo');
Registry::forget('foo.bar');
```

Flush registry

```php
Registry::flush();
```

Mass update

```php
$settings = Input::only('site_name', 'company_address', 'email');

Registry::store($settings);
```

## Change Log

#### v0.1.2

- Added config for custom table name
- Added forced variable types 
- Code cleanup

#### v0.1.1

- Bug fixes

#### v0.1.0

- First release