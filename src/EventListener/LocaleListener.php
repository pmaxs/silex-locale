<?php
namespace Pmaxs\Silex\Locale\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouteCollection;
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
     * Routes that should be excluded
     * @var array
     */
    protected $exclude_routes;

    /**
     * @param array $locales possible locales
     * @param string $default_locale default locale
     * @param boolean $resolve_by_host resolve locale by host name
     * @param RouteCollection $routes routes
     * @param array $exclude_routes routes that should be excluded
     */
    public function __construct($locales, $default_locale, $resolve_by_host, RouteCollection $routes, $exclude_routes)
    {
        $this->locales = $locales;
        $this->default_locale = $default_locale;
        $this->resolve_by_host = $resolve_by_host;
        $this->routes = $routes;
        $this->exclude_routes = $exclude_routes;
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
        if ($this->resolve_by_host) return;

        $locales =  implode('|', $this->locales);

        $exclude_routes_reg = $this->getExcludeRoutesReg();

        foreach ($this->routes as $routeName => $route) {
            if ($exclude_routes_reg && preg_match($exclude_routes_reg, $routeName)) continue;

            $route
                ->setPath('/{locale}' . ltrim($route->getPath(), '/'))
                ->setRequirement('locale', '((?:' . $locales . ')/)?')
                ->setDefault('locale', '')
            ;
        }
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
            if (preg_match('~\\b(' . \implode($this->locales, '|') . ')\\.~i', $request->getHost(), $matches)) {
                $locale = $matches[1];
            }
        } else {
            if ($request->get('locale')) {
                $locale = trim($request->get('locale'), '\\/');
            }
        }
        if (empty($locale)) {
            $locale = $this->default_locale;
        }

        // set
        $request->setLocale($locale);
        $request->attributes->set('_locale', $locale);

        // routes
        if (!$this->resolve_by_host) {
            $locale1 = $locale != $this->default_locale ? $locale . '/' : '';

            $exclude_routes_reg = $this->getExcludeRoutesReg();

            foreach ($this->routes as $routeName => $route) {
                if ($exclude_routes_reg && preg_match($exclude_routes_reg, $routeName)) continue;

                $route->setDefault('locale', $locale1);
            }
        }
    }

    /**
     * Return host
     * @return string
     */
    protected function getExcludeRoutesReg()
    {
        static $exclude_routes_reg;

        if (isset($exclude_routes_reg)) return $exclude_routes_reg;

        $exclude_routes_reg = implode('|', $this->exclude_routes);
        $exclude_routes_reg = str_replace('~', '\\~', $exclude_routes_reg);
        $exclude_routes_reg = '~' . $exclude_routes_reg . '~';

        return $exclude_routes_reg;
    }
}
