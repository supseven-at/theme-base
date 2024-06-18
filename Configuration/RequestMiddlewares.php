<?php

declare(strict_types=1);

return [
    'frontend' => [
        'theme-base/remove-empty-paragraph' => [
            'target' => \Supseven\ThemeBase\Middleware\RemoveEmptyParagraphMiddleware::class,
            'after'  => [
                'typo3/cms-frontend/content-length-headers',
            ],
        ],
    ],
];
