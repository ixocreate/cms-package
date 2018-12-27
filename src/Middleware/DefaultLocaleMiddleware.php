<?php
namespace Ixocreate\Cms\Middleware;

use Ixocreate\Cms\Config\Config;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Intl\LocaleManager;
use Ixocreate\ProjectUri\ProjectUri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Uri;
use Zend\Expressive\Router\RouteResult;

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
            if (stripos((string) $request->getUri(), (string) $this->getLocalizationBaseUrl($locale['locale'])) !== false) {
                $this->localeManager->acceptLocale($locale['locale']);
                break;
            }
        }

        return $handler->handle($request);
    }

    private function getLocalizationBaseUrl(string $locale): Uri
    {
        $uriString = strtr(
            $this->config->localizationUrlSchema(),
            [
                '%MAIN_URL%' => rtrim((string) $this->projectUri->getMainUrl(), '/'),
                '%LANG%' => \Locale::getPrimaryLanguage($locale),
                '%REGION%' => \Locale::getRegion($locale),
            ]
        );

        return new Uri($uriString);
    }
}
