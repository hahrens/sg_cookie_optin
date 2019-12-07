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
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'SGalinski.' . $extKey,
			'OptIn',
			[
				'Optin' => 'show',
			],
			// non-cacheable actions
			[
				'Optin' => 'show',
			]
		);

		// hook registration
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
			\SGalinski\SgCookieOptin\Hook\GenerateFilesAfterTcaSave::class;

		// User TSConfig
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
			'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $extKey . '/Configuration/TsConfig/User/HideTableButtons.tsconfig">'
		);

		// Page TSConfig
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
			'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $extKey . '/Configuration/TsConfig/Page/NewContentElementWizard.tsconfig">'
		);

		//Register Icons
		$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
		$iconRegistry->registerIcon(
			'extension-' . $extKey,
			\TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
			['source' => 'EXT:' . $extKey . '/Resources/Public/Icons/extension-sg_cookie_optin.svg']
		);
	}, 'sg_cookie_optin'
);
