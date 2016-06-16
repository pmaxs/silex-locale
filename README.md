# Silex-locale

Silex service provider to adjust locale/language via url.

Installation
------------

    composer require pmaxs/silex-locale

Usage
-----

Options:
locale.locales - available locales  
locale.default_locale - default locale  
locale.resolve_by_host - resolve locale from host name  

Urls:
locale.resolve_by_host = 0, default locale: scheme://host/...  
locale.resolve_by_host = 0, not default locale: scheme://host/{{locale}}/...  

locale.resolve_by_host = 1, default locale: scheme://host/...  
locale.resolve_by_host = 1, not.default locale: scheme://{{locale}}.host/...  

```php
$app->register(new Pmaxs\Silex\Locale\Provider\LocaleServiceProvider(), [
    'locale.locales' => ['en','ru','jp'],
    'locale.default_locale' => 'en',
    'locale.resolve_by_host' => 0,
]);
```
