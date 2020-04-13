<?php


namespace GAYA\CachedErrorPages\ErrorHandler;


use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageContentErrorHandler;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CachedPageContentErrorHandler implements PageErrorHandlerInterface
{

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var array
     */
    protected $errorHandlerConfiguration;

    /**
     * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    protected $cache;

    /**
     * CachedPageContentErrorHandler constructor.
     * @param int $statusCode
     * @param array $configuration
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function __construct(int $statusCode, array $configuration)
    {
        $this->statusCode = $statusCode;
        if (empty($configuration['errorContentSource'])) {
            throw new \InvalidArgumentException(
                'CachedPageContentErrorHandler needs to have a proper link set.',
                1586766786
            );
        }
        $this->errorHandlerConfiguration = $configuration;
        $this->cache = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class)
            ->getCache('cached_error_pages');
    }

    public function handlePageError(
        \Psr\Http\Message\ServerRequestInterface $request,
        string $message,
        array $reasons = []
    ): \Psr\Http\Message\ResponseInterface {
        $cacheInformations = $this->getCacheInformation(
            $request,
            $this->errorHandlerConfiguration['errorContentSource']
        );

        if (!$cacheInformations['identifier']) {
            return $this->getResponseFromPageContentErrorHandler($request, $message, $reasons);
        }

        $content = $this->cache->get($cacheInformations['identifier']);
        if ($content !== false) {
            return new HtmlResponse($content, $this->statusCode);
        }

        // Get the response from core's PageContentErrorHandler
        $response = $this->getResponseFromPageContentErrorHandler($request, $message, $reasons);

        // Save the response into the cache
        $content = (string)$response->getBody();
        $this->cache->set($cacheInformations['identifier'], $content, $cacheInformations['tags']);

        return $response;
    }

    protected function getCacheInformation(ServerRequestInterface $request, string $typoLinkUrl): array
    {
        $cacheInfos = [
            'identifier' => '',
            'tags' => [],
        ];

        $linkService = GeneralUtility::makeInstance(LinkService::class);
        $urlParams = $linkService->resolve($typoLinkUrl);
        if ($urlParams['type'] !== 'page' && $urlParams['type'] !== 'url') {
            throw new \InvalidArgumentException(
                'CachedPageContentErrorHandler can only handle TYPO3 urls of types "page" or "url"', 1586769718
            );
        }
        if ($urlParams['type'] === 'url') {
            return $cacheInfos;
        }

        $cacheInfos['tags'][] = 'pageId_' . (int)$urlParams['pageuid'];

        // Get the site related to the configured error page
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId((int)$urlParams['pageuid']);
        // Fall back to current request for the site
        if (!$site instanceof Site) {
            $site = $request->getAttribute('site', null);
        }
        $cacheInfos['identifier'] .= $site->getIdentifier();

        /** @var SiteLanguage $requestLanguage */
        $requestLanguage = $request->getAttribute('language', null);
        // Try to get the current request language from the site that was found above
        if ($requestLanguage instanceof SiteLanguage) {
            try {
                $language = $site->getLanguageById($requestLanguage->getLanguageId());
            } catch (\InvalidArgumentException $e) {
                $language = $site->getDefaultLanguage();
            }
        } else {
            $language = $site->getDefaultLanguage();
        }
        $cacheInfos['identifier'] .= '-'.$language->getTwoLetterIsoCode();

        $cacheInfos['identifier'] .= '-'.$this->statusCode;

        return $cacheInfos;
    }

    protected function getResponseFromPageContentErrorHandler(
        \Psr\Http\Message\ServerRequestInterface $request,
        string $message,
        array $reasons = []
    ): \Psr\Http\Message\ResponseInterface {
        $handler = GeneralUtility::makeInstance(
            PageContentErrorHandler::class,
            $this->statusCode,
            $this->errorHandlerConfiguration
        );

        return $handler->handlePageError($request, $message, $reasons);
    }
}
