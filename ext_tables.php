<?php
/**
 *
 * Copyright notice
 *
 * (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

call_user_func(
	function ($extKey) {
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
			'SGalinski.' . $extKey,
			'OptIn',
			'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_backend.xlf:optInPluginLabel'
		);

		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
			$extKey, 'Configuration/TypoScript/Frontend', 'Cookie Optin'
		);

		if (TYPO3_MODE === 'BE') {
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
				'tx_sgcookieoptin_domain_model_optin'
			);
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
				'tx_sgcookieoptin_domain_model_group'
			);
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
				'tx_sgcookieoptin_domain_model_script'
			);
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
				'tx_sgcookieoptin_domain_model_cookie'
			);

			\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
				'SGalinski.' . $extKey,
				'web',
				'Optin',
				'',
				[
					'Optin' => 'index, activateDemoMode',
				],
				[
					'access' => 'user,group',
					'icon' => 'EXT:' . $extKey . '/Resources/Public/Icons/'
						. (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(
							TYPO3_version
						) >= 10000000 ? 'extension-sg_cookie_optin.svg' : 'module-sgcookieoptin.png'),
					'labels' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf',
				]
			);
		}

	}, 'sg_cookie_optin'
);
