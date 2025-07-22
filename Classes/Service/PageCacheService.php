<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Service;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Event\ModifyCacheLifetimeForPageEvent;

/**
 * Helper to change the page-cache behaviour
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class PageCacheService implements SingletonInterface
{
    public int $cacheLifetime = 0;

    public function __construct(
        #[Autowire(service: 'typo3.request')]
        protected readonly ServerRequestInterface $request,
    ) {
    }

    public function __invoke(ModifyCacheLifetimeForPageEvent $event): void
    {
        if ($this->cacheLifetime > 0 && $this->cacheLifetime < $event->getCacheLifetime()) {
            $event->setCacheLifetime($this->cacheLifetime);
        }
    }

    public function addTags(string ...$tags): void
    {
        $this->request->getAttribute('frontend.controller')->addCacheTags($tags);
    }

    public function disable(string $reason): void
    {
        $this->request->getAttribute('frontend.controller')->set_no_cache($reason, true);
    }
}
