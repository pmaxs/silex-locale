<?php
namespace Pmaxs\Silex\Locale\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Silex\Api\BootableProviderInterface;
use Symfony\Component\Routing\Route;
use Pmaxs\Silex\Locale\EventListener\LocaleListener;
use Pmaxs\Silex\Locale\Utils\UrlGenerator as LocaleUrlGenerator;
use Pmaxs\Silex\Locale\Twig\LocaleExtension;

/**
 * Locale Provider.
 */
class LocaleServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $app)
    {
        $app['locale.resolve_by_host'] = false;
        $app['locale.exclude_routes'] = [];

        $app['locale.url_generator'] = function() use ($app) {
            return new LocaleUrlGenerator(
                $app['url_generator'],
                $app['locale.locales'],
                $app['locale.default_locale'],
                $app['locale.resolve_by_host']
            );
        };

        if (isset($app['twig'])) {
            $app->extend('twig', function ($twig) use ($app) {
                static $initialized = false;
                if ($initialized) return $twig;
                $initialized = true;

                $twig->addExtension(new LocaleExtension(
                    $app['locale.url_generator'],
                    $app['locale.locales']
                ));

                return $twig;
            });
        }
    }

    /**
     * @inheritdoc
     */
    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber(new LocaleListener(
            $app['locale.locales'],
            $app['locale.default_locale'],
            $app['locale.resolve_by_host'],
            $app['routes'],
            $app['locale.exclude_routes']
        ));
    }
}
