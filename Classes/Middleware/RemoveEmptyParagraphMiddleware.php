<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\Stream;

/**
 * Class RemoveEmptyParagraphMiddleware
 *
 * removes empty paragraphs from the whole website content.
 *
 * This middleware was introduced in favor of a fluid viewhelper
 * which was often forgotten while integrating a TYPO3 project.
 */
class RemoveEmptyParagraphMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($response instanceof NullResponse) {
            return $response;
        }

        $body = $response->getBody();
        $body->rewind();
        $content = $response->getBody()->getContents();
        $content = preg_replace('~\\s?<p>(\\s|&nbsp;)+</p>\\s?~', '', $content);
        $body = new Stream('php://temp', 'rw');
        $body->write($content);

        return $response->withBody($body);
    }
}
