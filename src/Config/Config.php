<?php
namespace KiwiSuite\Cms\Config;

use KiwiSuite\Contract\Application\SerializableServiceInterface;
use Zend\Diactoros\Uri;

final class Config implements SerializableServiceInterface
{
    /**
     * @var string
     */
    private $localizationUrlSchema;

    /**
     * @var array
     */
    private $navigation = [];

    public function __construct(Configurator $configurator)
    {
        $this->localizationUrlSchema = $configurator->getLocalizationUrlSchema();
        $this->navigation = $configurator->getNavigation();
    }

    /**
     * @return string
     */
    public function localizationUrlSchema(): string
    {
        return $this->localizationUrlSchema;
    }

    /**
     * @param string $locale
     * @return Uri
     */
    public function localizationUri(string $locale): Uri
    {
        $uriString = str_replace('${LANG}', \Locale::getPrimaryLanguage($locale), $this->localizationUrlSchema());
        $uriString = str_replace('${REGION}', \Locale::getRegion($locale), $uriString);
        return new Uri($uriString);
    }

    /**
     * @return array
     */
    public function navigation(): array
    {
        return $this->navigation;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            'localizationUrlSchema' => $this->localizationUrlSchema,
            'navigation' => $this->navigation,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);

        $this->navigation = $unserialized['navigation'];
        $this->localizationUrlSchema = $unserialized['localizationUrlSchema'];
    }
}
