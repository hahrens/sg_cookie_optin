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
	static function () {
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

			$hideModuleInProductionContext = \SGalinski\SgCookieOptin\Service\ExtensionSettingsService::getSetting(
				\SGalinski\SgCookieOptin\Service\ExtensionSettingsService::SETTING_HIDE_MODULE_IN_PRODUCTION_CONTEXT
			);

			$showModule = TRUE;
			if ($hideModuleInProductionContext) {
				if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '10.2.0', '<')) {
					$applicationContext = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext();
				} else {
					$applicationContext = \TYPO3\CMS\Core\Core\Environment::getContext();
				}

				if (isset($applicationContext)) {
					$showModule = !$applicationContext->isProduction();
				}
			}
			if ($showModule) {
				\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
					'SGalinski.sg_cookie_optin',
					'web',
					'Optin',
					'',
					[
						'Optin' => 'index, activateDemoMode, create',
					],
					[
						'access' => 'user,group',
						'icon' => 'EXT:sg_cookie_optin/Resources/Public/Icons/module-sgcookieoptin.png',
						'labels' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang.xlf',
					]
				);
			}

			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
			        'tx_sgcookieoptin_domain_model_optin',
			        'EXT:sg_cookie_optin/Resources/Private/Language/locallang_csh_tx_sgcookieoptin_domain_model_optin.xlf'
			);
		}
	}
);
