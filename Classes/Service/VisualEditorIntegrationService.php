<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A service responsible for integrating the visual editor functionality into TYPO3.
 * This service provides methods to check availability and build payloads for image editing
 * with context-specific URLs in the TYPO3 backend.
 */
final readonly class VisualEditorIntegrationService
{
    /**
     * Defines the key identifier for the visual editor package.
     */
    private const string VISUAL_EDITOR_PACKAGE_KEY = 'visual_editor';

    /**
     * Specifies the fully qualified class name for the service
     * responsible for handling edit mode functionality within the visual editor.
     */
    private const string EDIT_MODE_SERVICE_CLASS = 'TYPO3\\CMS\\VisualEditor\\Service\\EditModeService';

    public function __construct(
        private PackageManager $packageManager,
        private UriBuilder $uriBuilder,
        private Typo3Version $typo3Version,
    ) {
    }

    /**
     * Determines whether the required package is active and the specified class exists.
     *
     * @return bool True if the package is active and the class exists, otherwise false.
     */
    public function isAvailable(): bool
    {
        return $this->packageManager->isPackageActive(self::VISUAL_EDITOR_PACKAGE_KEY)
            && class_exists(self::EDIT_MODE_SERVICE_CLASS);
    }

    /**
     * Builds an image payload containing edit URLs for backend use.
     *
     * @param ServerRequestInterface $request The server request object, which may include normalized parameters and other metadata.
     * @param FileInterface|null $image The image file to process, which must be an instance of File or FileReference.
     * @return array|null An associative array containing the 'url' and 'editUrl' keys, or null if the payload cannot be built.
     * @throws RouteNotFoundException
     */
    public function buildImagePayload(ServerRequestInterface $request, ?FileInterface $image): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }

        if (!$image instanceof File && !$image instanceof FileReference) {
            return null;
        }

        $editModeService = GeneralUtility::makeInstance(self::EDIT_MODE_SERVICE_CLASS);

        if (!$editModeService->isEditMode($request)) {
            return null;
        }

        if ($image instanceof File) {
            $fields = [];
            $table = 'sys_file';
            $uid = (int)$image->getProperty('uid');
        } else {
            $fields = [(string)$image->getProperty('fieldname')];
            $table = (string)$image->getProperty('tablenames');
            $uid = (int)$image->getProperty('uid_foreign');
        }

        if ($uid <= 0 || $table === '') {
            return null;
        }

        $normalizedParams = $request->getAttribute('normalizedParams');

        if (!$normalizedParams instanceof NormalizedParams) {
            return null;
        }

        $backendEditUrl = (string)$editModeService->getBackendEditUrl($request);
        $editParams = [
            'edit' => [
                $table => [
                    $uid => 'edit',
                ],
            ],
            'columnsOnly' => [
                $table => $fields,
            ],
            'returnUrl' => $backendEditUrl,
        ];

        $url = '';

        if ($this->typo3Version->getMajorVersion() >= 14) {
            $url = (string)$this->uriBuilder->buildUriFromRoute('record_edit_contextual', $editParams);
        }

        return [
            'url'     => $url,
            'editUrl' => (string)$this->uriBuilder->buildUriFromRoute('record_edit', $editParams),
        ];
    }
}
