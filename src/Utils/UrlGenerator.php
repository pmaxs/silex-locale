<?php
namespace Pmaxs\Silex\Locale\Utils;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class to generate urls for different locales
 */
class UrlGenerator
{
    /**
     * Url generator
     * @var UrlGeneratorInterface|null
     */
    protected $generator;

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
     * Fake index route name
     * @var string
     */
    protected $fake_index_route;

    /**
     * Constructor
     * @param UrlGeneratorInterface $generator url generator
     * @param array $locales possible locales
     * @param string $default_locale default locale
     * @param boolean $resolve_by_host resolve locale by host
     * @param string $fake_index_route fake index route name
     */
    public function __construct(UrlGeneratorInterface $generator, $locales, $default_locale, $resolve_by_host, $fake_index_route)
    {
        $this->generator = $generator;
        $this->locales = $locales;
        $this->default_locale = $default_locale;
        $this->resolve_by_host = $resolve_by_host;
        $this->fake_index_route = $fake_index_route;
    }

    /**
     * Returns url for route
     * @param $locale
     * @param $name
     * @param array $parameters
     * @param int $referenceType
     * @return string url
     */
    public function generate($locale, $name, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if ($locale != $this->default_locale) {
            $parameters['locale'] = $locale . '/';
        }

        return $this->generator->generate($name, $parameters, $referenceType);
    }

    public function getUrl($url, $absolute = false)
    {
        return $this->getUrlForLocale($url, $this->getLocale(), $absolute);
    }

    public function getUrlForLocale($url, $locale, $absolute = false)
    {
        $url_parts = parse_url($url);

        if (!empty($url_parts['scheme'])){
            return $url;
        }

        if ($this->resolve_by_host) {
            if (!$absolute && $locale == $this->getLocale()) {
                return $url;
            }

            $url = ''
                .$this->getScheme().'://'
                .($locale != $this->default_locale ? $locale . '.' : '')
                .$this->getHost() . '/'
                .$url;

            return $url;

        } else {
            if (!strlen($url_parts['path']) && !strlen($url_parts['query'])) {
                return $url;
            }

            if (!preg_match('~^/?(' . $this->getLocalesReg() . ')(/|$)~', $url)) {
                $url = '/'
                    .($locale != $this->default_locale ? $locale . '/' : '')
                    .ltrim($url, '/');
            }

            if ($absolute) {
                $url = ''
                    .$this->getScheme().'://'
                    .$this->getHost() . '/'
                    .$url;
            }

            return $url;
        }
    }

    /**
     * Returns index url for current locale
     * @param boolean $absolute absolute url
     * @return string url
     */
    public function getIndexUrl($absolute = false)
    {
        return $this->getIndexUrlForLocale($this->getLocale(), $absolute);
    }

    /**
     * Returns index url for locale
     * @param string $locale locale
     * @param boolean $absolute absolute url
     * @return string url
     */
    public function getIndexUrlForLocale($locale, $absolute = false)
    {
        if ($this->resolve_by_host) {
            $url = ''
                .$this->getScheme().'://'
                .($locale != $this->default_locale ? $locale . '.' : '')
                .$this->getHost() . '/';

            return $url;

        } else {
            $url = $this->generator->generate(
                $this->fake_index_route,
                ['locale0' => $locale != $this->default_locale ? $locale : ''],
                $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
            );

            $url = rtrim($url, '/') . '/';

            return $url;
        }
    }

    /**
     * Clear path from locale
     * @param $path
     * @return string
     */
    public function clearPathFromLocale($path)
    {
        if ($this->resolve_by_host) {
            return $path;
        }

        $path = preg_replace('~^/?(' . $this->getLocalesReg() . ')(/|$)~', '', $path);

        return $path;
    }

    /**
     * Return scheme
     * @return string
     */
    protected function getScheme()
    {
        return $this->generator->getContext()->getScheme();
    }

    /**
     * Return host
     * @return string
     */
    protected function getHost()
    {
        static $host;

        if (isset($host)) return $host;

        $host = $this->generator->getContext()->getHost();
        $host = preg_replace('~(' . $this->getLocalesReg() . ')\\.~i', '', $host);

        return $host;
    }

    /**
     * Return current locale
     * @return string
     */
    protected function getLocale()
    {
        return $this->generator->getContext()->getParameter('_locale');
    }

    /**
     * Return locales reg
     * @return string
     */
    protected function getLocalesReg()
    {
        static $locales_reg;

        if (isset($locales_reg)) return $locales_reg;

        $locales_reg = implode('|', $this->locales);

        return $locales_reg;
    }
}
