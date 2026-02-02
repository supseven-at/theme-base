<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Service;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Http\PropagateResponseException;

/**
 * Service to quit with an error page
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class ErrorPageService
{
    public function __construct(
        #[Autowire(service: 'typo3.request', lazy: true)]
        protected readonly ServerRequestInterface $request,
    ) {
    }

    /**
     * Show the current sites error page for the given code
     *
     * IMPORTANT: Exceptions thrown because of wrong or missing
     * configuration settings are not handled intentionally!
     *
     * @param int $code
     * @param string $reason
     */
    public function error(int $code, string $reason): void
    {
        $response = $this->request
            ->getAttribute('site')
            ->getErrorHandler($code)
            ->handlePageError($this->request, $reason);

        throw new PropagateResponseException($response, 1713449666);
    }

    /**
     * Show the not-found error page
     *
     * @param string $reason
     */
    public function notFound(string $reason = 'Page not found'): void
    {
        $this->error(404, $reason);
    }

    /**
     * Show the gone error page
     *
     * @param string $reason
     */
    public function gone(string $reason = 'Page not available anymore'): void
    {
        $this->error(410, $reason);
    }
}
