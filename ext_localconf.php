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

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

call_user_func(
	static function () {
        $currentTypo3Version = VersionNumberUtility::getCurrentTypo3Version();
        if (version_compare($currentTypo3Version, '11.0.0', '>=')) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
                'sg_cookie_optin',
                'OptIn',
                [
                    \SGalinski\SgCookieOptin\Controller\OptinController::class => 'show',
                ],
                // non-cacheable actions
                [
                    \SGalinski\SgCookieOptin\Controller\OptinController::class => '',
                ]
            );
        } else {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
                'SGalinski.sg_cookie_optin',
                'OptIn',
                [
                    'Optin' => 'show',
                ],
                // non-cacheable actions
                [
                    'Optin' => '',
                ]
            );
        }

		// hook registration
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
			\SGalinski\SgCookieOptin\Hook\GenerateFilesAfterTcaSave::class;
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
			\SGalinski\SgCookieOptin\Hook\HandleTemplateAfterTcaSave::class;
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
			\SGalinski\SgCookieOptin\Hook\HandleVersionChange::class;

		// User TSConfig
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
			'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:sg_cookie_optin/Configuration/TsConfig/User/HideTableButtons.tsconfig">'
		);

		// Page TSConfig
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
			'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:sg_cookie_optin/Configuration/TsConfig/Page/NewContentElementWizard.tsconfig">'
		);

		// External Content Frame Class TSConfig
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
			'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:sg_cookie_optin/Configuration/TsConfig/Page/ExternalContentFrameClass.tsconfig">'
		);

		// Licence check
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['constructPostProcess'][] =
			\SGalinski\SgCookieOptin\Hook\LicenceCheckHook::class . '->performLicenseCheck';

		// Register Icons
		if ($currentTypo3Version >= 7000000) {
			$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				\TYPO3\CMS\Core\Imaging\IconRegistry::class
			);
			$iconRegistry->registerIcon(
				'extension-sg_cookie_optin',
				\TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
				['source' => 'EXT:sg_cookie_optin/Resources/Public/Icons/extension-sg_cookie_optin.svg']
			);
		}

		// Wizard Registration
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][] = [
			'nodeName' => 'templatePreviewLinkWizard',
			'priority' => 70,
			'class' => \SGalinski\SgCookieOptin\Wizards\TemplatePreviewLinkWizard::class
		];

		// Ajax Endpoint
		$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['sg_cookie_optin_saveOptinHistory'] = \SGalinski\SgCookieOptin\Endpoints\OptinHistoryController::class . '::saveOptinHistory';

		if (!class_exists('SgCookieAbstractViewHelper')) {
			$typo3Version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
			if ($typo3Version >= 10000000) {
				class_alias('\TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper', 'SgCookieAbstractViewHelper');
			} else {
				class_alias('\TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper', 'SgCookieAbstractViewHelper');
			}
		}
	}
);
