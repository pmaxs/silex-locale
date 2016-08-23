<?php
namespace Pmaxs\Silex\Locale\Twig;

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
            new \Twig_SimpleFunction('locale_get_index_url', [$this, 'getIndexUrl']),
            new \Twig_SimpleFunction('locale_get_index_url_for_locale', [$this, 'getIndexUrlForLocale']),
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
     * Returns index url for current locale
     * @return string url
     */
    public function getIndexUrl()
    {
        return $this->generator->getIndexUrl();
    }

    /**
     * Returns index url for locale
     * @param string $locale locale
     * @return string url
     */
    public function getIndexUrlForLocale($locale)
    {
        return $this->generator->getIndexUrlForLocale($locale);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return get_class($this);
    }
}