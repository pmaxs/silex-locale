<?php
namespace Pmaxs\Silex\Locale\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Pmaxs\Silex\Locale\Utils\UrlGenerator;

/**
 * Twig extension for locale
 */
class LocaleExtension extends \Twig_Extension
{
    /**
     * @param UrlGenerator $generator
     */
    protected $generator;

    /**
     * Possible locales
     * @var array
     */
    protected $locales;

    /**
     * Constructor
     * @param UrlGenerator $generator
     * @param array $locales possible locales
     */
    public function __construct(UrlGenerator $generator, $locales)
    {
        $this->generator = $generator;
        $this->locales = $locales;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('locale_generate', [$this, 'generate']),
            new \Twig_SimpleFunction('locale_get_url', [$this, 'getUrl']),
            new \Twig_SimpleFunction('locale_get_url_for_locale', [$this, 'getUrlForLocale']),
            new \Twig_SimpleFunction('locale_get_index_url', [$this, 'getIndexUrl']),
            new \Twig_SimpleFunction('locale_get_index_url_for_locale', [$this, 'getIndexUrlForLocale']),
            new \Twig_SimpleFunction('locale_clear_path_from_locale', [$this, 'clearPathFromLocale']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getGlobals()
    {
        return [
            'locales' => $this->locales,
        ];
    }

    /**
     * Returns url for route
     * @return string url
     */
    public function generate($locale, $name, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->generator->generate($locale, $name, $parameters, $referenceType);
    }

    /**
     * Returns url for current locale
     * @return string url
     */
    public function getUrl($url, $absolute = false)
    {
        return $this->generator->getUrl($url, $absolute);
    }

    /**
     * Returns url for locale
     * @param string $locale locale
     * @return string url
     */
    public function getUrlForLocale($url, $locale, $absolute = false)
    {
        return $this->generator->getUrlForLocale($url, $locale, $absolute);
    }

    /**
     * Returns index url for current locale
     * @return string url
     */
    public function getIndexUrl($absolute = false)
    {
        return $this->generator->getIndexUrl($absolute);
    }

    /**
     * Returns index url for locale
     * @param string $locale locale
     * @return string url
     */
    public function getIndexUrlForLocale($locale, $absolute = false)
    {
        return $this->generator->getIndexUrlForLocale($locale, $absolute);
    }

    /**
     * Clear path from locale
     * @param $path
     * @return string
     */
    public function clearPathFromLocale($path)
    {
        return $this->generator->clearPathFromLocale($path);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return get_class($this);
    }
}