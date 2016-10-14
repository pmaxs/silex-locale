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
     * Constructor
     * @param UrlGeneratorInterface $generator url generator
     * @param array $locales possible locales
     * @param string $default_locale default locale
     * @param boolean $resolve_by_host resolve locale by host
     */
    public function __construct(UrlGeneratorInterface $generator, $locales, $default_locale, $resolve_by_host)
    {
        $this->generator = $generator;
        $this->locales = $locales;
        $this->default_locale = $default_locale;
        $this->resolve_by_host = $resolve_by_host;
    }

    /**
     * Generate url for route
     * @param $locale
     * @param $name
     * @param array $parameters
     * @param int $referenceType
     * @return string url
     */
    public function generate($locale, $name, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if ($this->resolve_by_host) {
            if (UrlGeneratorInterface::ABSOLUTE_URL !=  $referenceType && $locale == $this->getLocale()) {
                $url = $this->generator->generate($name, $parameters, $referenceType);

            } else {
                $url = $this->generator->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);

                $url = ''
                    . $this->getScheme() . '://'
                    . $this->getHostForLocale($locale)
                    . $url;
            }
        } else {
            $parameters['locale'] = $locale != $this->default_locale ? $locale . '/' : '';

            $url = $this->generator->generate($name, $parameters, $referenceType);
        }

        return $url;
    }

    /**
     * Return url for locale
     * @param $url
     * @param bool $absolute
     * @return string
     */
    public function getUrlForLocale($url, $locale, $absolute = false)
    {
        $url_parts = parse_url($url);

        if (!empty($url_parts['scheme'])) {
            return $url;
        }

        if ($this->resolve_by_host) {
            if ($absolute || $locale != $this->getLocale()) {
                $url = ''
                    . $this->getScheme() . '://'
                    . $this->getHostForLocale($locale)
                    . $url;
            }
        } else {
            $url = $this->clearPathFromLocale($url);

            $url = '/'
                . ($locale != $this->default_locale ? $locale . '/' : '')
                . ltrim($url, '/');

            if ($absolute) {
                $url = ''
                    . $this->getScheme() . '://'
                    . $this->getHost()
                    . $url;
            }
        }

        return $url;
    }

    /**
     * Return url for current locale
     * @param $url
     * @param bool $absolute
     * @return string
     */
    public function getUrl($url, $absolute = false)
    {
        return $this->getUrlForLocale($url, $this->getLocale(), $absolute);
    }

    /**
     * Return index url for current locale
     * @param boolean $absolute absolute url
     * @return string url
     */
    public function getIndexUrl($absolute = false)
    {
        return $this->getIndexUrlForLocale($this->getLocale(), $absolute);
    }

    /**
     * Return index url for locale
     * @param string $locale locale
     * @param boolean $absolute absolute url
     * @return string url
     */
    public function getIndexUrlForLocale($locale, $absolute = false)
    {
        return $this->getUrlForLocale('/', $locale, $absolute);
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

        $path = preg_replace('~^/(' . $this->getLocalesReg() . ')(/|$)~', '/', $path);

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
     * Return base host
     * @return string
     */
    protected function getHost()
    {
        return $this->generator->getContext()->getHost();
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
     * Return base host
     * @return string
     */
    protected function getBaseHost()
    {
        static $host;

        if (isset($host)) return $host;

        $host = preg_replace('~(' . $this->getLocalesReg() . ')\\.~i', '', $this->getHost());

        return $host;
    }

    /**
     * Return host for locale
     * @param string $locale locale
     * @return string
     */
    protected function getHostForLocale($locale)
    {
        return ($locale != $this->default_locale ? $locale . '.' : '') . $this->getBaseHost();
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
