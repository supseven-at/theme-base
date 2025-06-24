<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;

/**
 * Factory for "value-services"
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class DependencyValuesService
{
    public function getRequest(): ServerRequestInterface
    {
        if (empty($GLOBALS['TYPO3_REQUEST'])) {
            throw new \UnexpectedValueException('TYPO request availble');
        }

        return $GLOBALS['TYPO3_REQUEST'];
    }

    public function getApplicationContext(): ApplicationContext
    {
        return Environment::getContext();
    }

    public function getApplicationType(): ApplicationType
    {
        return ApplicationType::fromRequest($this->getRequest());
    }
}
