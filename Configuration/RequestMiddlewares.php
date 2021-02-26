<?php
return [
    'frontend' => [
        'SGalinski/SgCookieOptin/saveOptinHistory' => [
            'target' => \SGalinski\SgCookieOptin\Middlewares\SaveOptinHistory::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering'
            ]
        ],
    ],
];
