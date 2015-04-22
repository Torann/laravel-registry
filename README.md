# Registry Manager for Laravel

Registry manager for Laravel 5. An alternative for managing application configurations and settings. Now with the magic of caching, so no more database calls to simply get site setting.

----------

## Installation

- [Registry on GitHub](https://github.com/compareasia/laravel-registry)

To get the latest version of Registry simply require it in your `composer.json` file.

~~~
"repositories": [
	  {
	  	"type": "vcs",
	  	"url": "https://github.com/compareasiagroup/laravel-registry"
		}
  	],
"require" : "compareasiagroup/laravel-registry": "dev-master"
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

Once Registry is installed you need to register the service provider with the application. Open up `app/app.php` and find the `providers` key.

```php
'providers' => array(
    'CompareAsiaGroup\Registry\RegistryServiceProvider',
)
```

Registry also ships with a facade which provides the static syntax for creating collections. You can register the facade in the aliases key of your `app/app.php` file.

```php
'aliases' => array(
    'Registry' => 'CompareAsiaGroup\Registry\Facades\Registry',
)
```

### Publish the configurations and migration

Run this on the command line from the root of your project:

~~~
$ php artisan vendor:publish
~~~

A configuration file will be publish to `config/registry.php`

## Documentation

@TODO Add documentation to Confluence

## Change Log

#### v0.2.0

- Update to Laravel 5

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