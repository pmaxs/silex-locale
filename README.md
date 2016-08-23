# Silex-locale

Silex service provider to adjust locale/language via url.

Installation
------------

    composer require pmaxs/silex-locale "^1.0" # for silex v1.x
    composer require pmaxs/silex-locale "^2.0" # for silex v2.x

Usage
-----

Options:  
locale.locales - available locales  
locale.default_locale - default locale  
locale.resolve_by_host - resolve locale from host name  
locale.exclude_routes - routes that should be excluded (service routes)

Urls:  
locale.resolve_by_host = 0, default locale: scheme://host/...  
locale.resolve_by_host = 0, not default locale: scheme://host/{{locale}}/...  

locale.resolve_by_host = 1, default locale: scheme://host/...  
locale.resolve_by_host = 1, not default locale: scheme://{{locale}}.host/...  

silex v1.x

```php
$app->register(new Pmaxs\Silex\Locale\Provider\LocaleServiceProvider(), [
    'locale.locales' => ['en','ru','jp'],
    'locale.default_locale' => 'en',
    'locale.resolve_by_host' => 0,
    'locale.exclude_routes' => ['^_'],
]);
```

silex v2.x

```php
$app->register(new Pmaxs\Silex\Locale\Provider\LocaleServiceProvider(), [
    'locale.locales' => ['en','ru','jp'],
    'locale.default_locale' => 'en',
    'locale.resolve_by_host' => 0,
    'locale.exclude_routes' => ['^_'],
]);

$app->register(new Silex\Provider\LocaleServiceProvider(), [
]);
```
