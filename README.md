# Silex-locale

Silex service provider to adjust locale/language via url.

## Installation

```
composer require pmaxs/silex-locale "^1.0" # for silex v1.x
composer require pmaxs/silex-locale "^2.0" # for silex v2.x
```

## Options

- locale.locales - available locales  
- locale.default_locale - default locale  
- locale.resolve_by_host - resolve locale from host name  
- locale.exclude_routes - routes that should be excluded (service routes)

## Urls

- locale.resolve_by_host = 0
 - default locale: scheme://host/...  
 - not default locale: scheme://host/{{locale}}/...  
- locale.resolve_by_host = 1
 - default locale: scheme://host/...  
 - not default locale: scheme://{{locale}}.host/...  

## Loading provider

silex v1.x

```php
$app->register(new Pmaxs\Silex\Locale\Provider\LocaleServiceProvider(), [
    'locale.locales' => ['en', 'ru', 'es'],
    'locale.default_locale' => 'en',
    'locale.resolve_by_host' => false,
    'locale.exclude_routes' => ['^_'],
]);
```

silex v2.x

```php
$app->register(new Pmaxs\Silex\Locale\Provider\LocaleServiceProvider(), [
    'locale.locales' => ['en', 'ru', 'es'],
    'locale.default_locale' => 'en',
    'locale.resolve_by_host' => false,
    'locale.exclude_routes' => ['^_'],
]);

$app->register(new Silex\Provider\LocaleServiceProvider(), [
]);
```

## Usage

```
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require '../vendor/autoload.php';

$app = new \Silex\Application();

$app->register(new \Silex\Provider\LocaleServiceProvider());
$app->register(new \Silex\Provider\TranslationServiceProvider());
$app->register(new \Pmaxs\Silex\Locale\Provider\LocaleServiceProvider(), [
    'locale.locales' => ['en', 'ru', 'es'],
    'locale.default_locale' => 'en',
    'locale.resolve_by_host' => false,
    'locale.exclude_routes' => ['^_']
]);

// will be accessible by urls `/`, `/en/`, `/ru/`, `/es/`
$app->get('/', function (Request $request) use ($app) {
    return new Response('index ' . $request->getLocale());
})->bind('index');

// will be accessible by urls `/test/123`, `/en/test/123`, `/ru/test/123`, `/es/test/123`
$app->get('/test/{var}', function(Request $request) use ($app) {
    return new Response('test ' . $request->getLocale() . ' ' . $request->get('var'));
})->bind('test');
```

## Url generation

- Index url
  - Current locale
    - php: `$app['locale.url_generator']->getIndexUrl()`
    - twig: `locale_get_index_url()`
  - Any locale
    - php: `$app['locale.url_generator']->getIndexUrlForLocale('es')`
    - twig: `locale_get_index_url_for_locale('es')`
- Other urls
  - Current locale
    - php: standard silex mechanism for url generation `$app['url_generator']->generate(...)`
    - twig: standard twig mechanism for url generation `path(...)`
  - Any locale
    - php: `$app['locale.url_generator']->generate('es', $name, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)`
    - twig: `locale_generate('es', $name, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)`
   
