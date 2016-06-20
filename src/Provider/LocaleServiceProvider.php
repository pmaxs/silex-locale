<?php
namespace Pmaxs\Silex\Locale\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Pmaxs\Silex\Locale\EventListener\LocaleListener;
use Pmaxs\Silex\Locale\UrlGenerator as LocaleUrlGenerator;
use Pmaxs\Silex\Locale\Twig\LocaleExtension;

/**
 * Locale Provider.
 */
class LocaleServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Application $app)
    {
        $app['locale.fake_index_route'] = 'locale-index0';

        $app['locale.url_generator'] = $app->share(function() use ($app) {
            return new LocaleUrlGenerator(
                $app['url_generator'],
                $app['locale.locales'],
                $app['locale'],
                $app['locale.default_locale'],
                $app['locale.resolve_by_host'],
                $app['locale.fake_index_route']
            );
        });

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
            $app['locale.fake_index_route']
        ));
    }
}
