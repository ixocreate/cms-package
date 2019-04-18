<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Middleware;

use Ixocreate\Package\Cms\Config\Config;
use Ixocreate\Package\Intl\LocaleManager;
use Ixocreate\Package\ProjectUri\ProjectUri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Uri;

final class DefaultLocaleMiddleware implements MiddlewareInterface
{
    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProjectUri
     */
    private $projectUri;

    /**
     * DefaultLocaleMiddleware constructor.
     * @param Config $config
     * @param LocaleManager $localeManager
     * @param ProjectUri $projectUri
     */
    public function __construct(Config $config, LocaleManager $localeManager, ProjectUri $projectUri)
    {
        $this->localeManager = $localeManager;
        $this->config = $config;
        $this->projectUri = $projectUri;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->localeManager->acceptLocale($this->localeManager->defaultLocale());
        foreach ($this->localeManager->all() as $locale) {
            if (\mb_stripos((string) $request->getUri(), (string) $this->getLocalizationBaseUrl($locale['locale'])) !== false) {
                $this->localeManager->acceptLocale($locale['locale']);
                break;
            }
        }

        return $handler->handle($request);
    }

    private function getLocalizationBaseUrl(string $locale): Uri
    {
        $uriString = \strtr(
            $this->config->localizationUrlSchema(),
            [
                '%MAIN_URL%' => \rtrim((string) $this->projectUri->getMainUrl(), '/'),
                '%LANG%' => \Locale::getPrimaryLanguage($locale),
                '%REGION%' => \Locale::getRegion($locale),
            ]
        );

        return new Uri($uriString);
    }
}
