# Registry Manager for Laravel

[![Latest Stable Version](https://poser.pugx.org/torann/registry/v/stable.png)](https://packagist.org/packages/torann/registry) [![Total Downloads](https://poser.pugx.org/torann/registry/downloads.png)](https://packagist.org/packages/torann/registry)

Registry manager for Laravel. An alternative for managing application configurations and settings. Now with the magic of caching, so no more database calls to simply get site setting.

----------

## Installation

- [Registry on Packagist](https://packagist.org/packages/torann/registry)
- [Registry on GitHub](https://github.com/Torann/laravel-registry)

To get the latest version of Registry simply require it in your `composer.json` file.

~~~
"torann/registry": "0.1.*@dev"
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

Once Registry is installed you need to register the service provider with the application. Open up `config/app.php` and find the `providers` key.

```php
'providers' => array(
    'Torann\Registry\RegistryServiceProvider',
)
```

Registry also ships with a facade which provides the static syntax for creating collections. You can register the facade in the aliases key of your `config/app.php` file.

```php
'aliases' => array(
    'Registry' => 'Torann\Registry\Facades\Registry',
)
```

### Publish the config

Run this on the command line from the root of your project:

~~~
$ php artisan vendor:publish --provider="Torann\Registry\RegistryServiceProvider"
~~~

This will publish Moderate's config to ``config/registry.php``.

### Migration

Run this on the command line from the root of your project:

~~~
$ php artisan migrate --package="torann/registry"
~~~

## Documentation

[View the official documentation](http://lyften.com/projects/laravel-registry/).

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