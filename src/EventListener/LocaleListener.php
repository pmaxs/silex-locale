<?php
namespace Pmaxs\Silex\Locale\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Listener to resolve locale from path or host
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * Possible locales
     * @var array
     */
    protected $locales;

    /**
     * Default locale
     * @var string
     */
    protected $default_locale;

    /**
     * Resolve locale by host name
     * @var boolean
     */
    protected $resolve_by_host;

    /**
     * Route collection
     * @var RouteCollection
     */
    protected $routes;

    /**
     * Fake index route name
     * @var string
     */
    protected $fake_index_route;

    /**
     * @param array $locales possible locales
     * @param string $default_locale default locale
     * @param boolean $resolve_by_host resolve locale by host name
     * @param RouteCollection $routes routes
     * @param string $fake_index_route fake index route name
     */
    public function __construct($locales, $default_locale, $resolve_by_host, RouteCollection $routes, $fake_index_route)
    {
        $this->locales = $locales;
        $this->default_locale = $default_locale;
        $this->resolve_by_host = $resolve_by_host;
        $this->routes = $routes;
        $this->fake_index_route = $fake_index_route;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('setupRoutes', 33),
                array('resolveLocale', 17),
            ),
        );
    }

    /**
     * Setups routes, adds locale parameter
     * @param GetResponseEvent $event
     */
    public function setupRoutes(GetResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) return;

        $locales =  \implode('|', $this->locales);

        foreach ($this->routes as $route) {
            $route
                ->setPath('/{locale}' . \ltrim($route->getPath(), '/'))
                ->setRequirement('locale', '((?:' . $locales . ')/)?')
                ->setDefault('locale', '')
            ;
        }

        $this->routes->add($this->fake_index_route, new Route(
            '/{locale0}/',
            [
                'locale0' => '',
                '_controller' => null,
            ],
            [
                'locale0' => '|' . $locales,
            ]
        ));
    }

    /**
     * Resolves locale
     * @param GetResponseEvent $event
     */
    public function resolveLocale(GetResponseEvent $event)
    {
        //if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) return;

        $request = $event->getRequest();

        // calc
        if ($this->resolve_by_host) {
            if (\preg_match('~\\b(' . \implode($this->locales, '|') . ')\\.~i', $request->getHost(), $matches)) {
                $locale = $matches[1];
            }
        } else {
            if ($request->get('locale')) {
                $locale = \trim($request->get('locale'), '\\/');
            }
        }
        if (empty($locale)) {
            $locale = $this->default_locale;
        }

        // set
        $request->setLocale($locale);
        $request->attributes->set('_locale', $locale);

        // routes
        if (!$this->resolve_by_host && $locale != $this->default_locale) {
            foreach ($this->routes as $route) {
                $route->setDefault('locale', $locale . '/');
            }

            $this->routes->get($this->fake_index_route)->setDefault('locale0', $locale);
        }
    }
}
