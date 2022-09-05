# Laravel query logger

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hxd/query-logger.svg?style=flat-square)](https://packagist.org/packages/hxd/query-logger)
[![Total Downloads](https://img.shields.io/packagist/dt/hxd/query-logger.svg?style=flat-square)](https://packagist.org/packages/hxd/query-logger)

This is a package that saves all database queries to a log file with some customizations

## Installation

You can install the package via composer:

```bash
composer require hxd/query-logger
```

## Usage

You can publish the config file with:

```bash
php artisan vendor:publish --tag="query-logger-config"
```

This is the contents of the published config file:

```php
return [

    // Enable or disable query logger
    'enabled' => env('QUERY_LOGGER_ENABLED', true),

    // Enable or disable query logger for specific connection | null for all
    'enable_for_connection' => env('QUERY_LOGGER_ENABLE_FOR_CONNECTION', null),

    // Channel you want to save query into (must have in laravel logging channel config)
    'channel' => env('QUERY_LOGGER_LOG_CHANNEL', 'stack'),

    // Enable or Disable automatically assign values to the query,
    // by default the queries will be hidden values to ensure security.
    // Make sure you know what you're doing when you turn this on
    'map_value' => env('QUERY_LOGGER_MAP_VALUE', true),

    // Log query execute time
    'log_exec_time' => env('QUERY_LOGGER_LOG_EXEC_TIME', true),

    // Look at the name, you know, the threshold to assign "SLOW QUERY" before your query in the log
    'slow_query_threshold' => env('QUERY_LOGGER_SLOW_QUERY_THRESHOLD', 0),

];

```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email me@hxd.vn instead of using the issue tracker.

## Credits

-   [Xuan Dung, Ho](https://github.com/hxd)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
