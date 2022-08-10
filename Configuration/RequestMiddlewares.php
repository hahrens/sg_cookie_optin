<?php

if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '11.0.0', '<')) {
	return [
		'frontend' => [
			'SGalinski/SgCookieOptin/saveOptinHistory' => [
				'target' => \SGalinski\SgCookieOptin\Middlewares\SaveOptinHistory::class,
				'after' => [
					'typo3/cms-frontend/site-resolver',
				],
				'before' => [
					'typo3/cms-frontend/base-redirect-resolver'
				]

			],
		],
	];
}

return [
	'frontend' => [
		'SGalinski/SgCookieOptin/saveOptinHistory' => [
			'target' => \SGalinski\SgCookieOptin\Middlewares\SaveOptinHistory::class,
			'after' => [
				'typo3/cms-frontend/site',
			],
			'before' => [
				'typo3/cms-frontend/base-redirect-resolver'
			]

		],
	],
];
