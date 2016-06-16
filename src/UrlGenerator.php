<?php
namespace Pmaxs\Silex\Locale;

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
     * Current locale
     * @var string
     */
    protected $locale;

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
     * Host name
     * @var string
     */
    protected $host;

    /**
     * Constructor
     * @param UrlGeneratorInterface $generator url generator
     * @param array $locales possible locales
     * @param string $locale current locale
     * @param string $default_locale default locale
     * @param boolean $resolve_by_host resolve locale by host
     * @param string $fake_index_route fake index route name
     */
    public function __construct(UrlGeneratorInterface $generator, $locales, $locale, $default_locale, $resolve_by_host, $fake_index_route)
    {
        $this->generator = $generator;
        $this->locales = $locales;
        $this->locale = $locale;
        $this->default_locale = $default_locale;
        $this->resolve_by_host = $resolve_by_host;
        $this->fake_index_route = $fake_index_route;

        $host = $generator->getContext()->getHost();
        $host = \preg_replace('~(' . \implode('|', $this->locales) . ')\\.~i', '', $host);
        $this->host = $host;
    }

    /**
     * Returns index url for current locale
     * @param boolean $absolute absolute url
     * @return string url
     */
    public function getIndexUrl($absolute = false)
    {
        return $this->getIndexUrlForLocale($this->locale, $absolute);
    }

    /**
     * Returns index url for locale
     * @param string $locale locale
     * @param boolean $absolute absolute url
     * @return string url
     */
    public function getIndexUrlForLocale($locale, $absolute = false)
    {
        if ($locale == $this->default_locale) $locale = '';

        if ($this->resolve_by_host) {
            $url = ''
                .$this->generator->getContext()->getScheme().'://'
                .($locale ? $locale . '.' : '')
                .$this->host . '/';
        } else {
            $url = $this->generator->generate(
                $this->fake_index_route,
                ['locale0' => $locale],
                $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
            );

            $url = \rtrim($url, '/') . '/';
        }

        return $url;
    }
}
