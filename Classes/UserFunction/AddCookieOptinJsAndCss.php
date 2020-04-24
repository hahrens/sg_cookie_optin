<?php

namespace SGalinski\SgCookieOptin\UserFunction;

/***************************************************************
 *  Copyright notice
 *
 *  (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use SGalinski\SgCookieOptin\Service\ExtensionSettingsService;
use SGalinski\SgCookieOptin\Service\LicensingService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Adds the Cookie Optin JavaScript if it's generated for the current page.
 */
class AddCookieOptinJsAndCss implements SingletonInterface {
	/** @var int */
	protected $rootpage = NULL;

	/**
	 * Adds the Cookie Optin JavaScript if it's generated for the current page.
	 *
	 * Example line: fileadmin/sg_cookie_optin/siteroot-1/cookieOptin_0_v2.js
	 *
	 * @param string $content
	 * @param array $configuration
	 * @return string
	 */
	public function addJavaScript($content, array $configuration) {
		if (LicensingService::checkKey() !== LicensingService::STATE_LICENSE_VALID
			&& !LicensingService::isInDemoMode()
		) {
			LicensingService::removeAllCookieOptInFiles();
			return '';
		}

		$rootPageId = $this->getRootPageId();
		if ($rootPageId <= 0) {
			return '';
		}

		$folder = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_FOLDER);
		if (!$folder) {
			return '';
		}

		$file = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptin.js';
		$sitePath = defined(PATH_site) ? PATH_site : \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/';
		if (file_exists($sitePath . $file)) {
			$jsonFile = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptinData_' .
				$this->getLanguage() . '.json';
			if (!file_exists($sitePath . $jsonFile)) {
				$jsonFile = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptinData_0.json';
				if (!file_exists($sitePath . $jsonFile)) {
					return '';
				}
			}

			return '<script id="cookieOptinData" type="application/json">' . file_get_contents($sitePath . $jsonFile) .
				'</script><script src="/' . $file . '" type="text/javascript" data-ignore="1"></script>';
		} {
			// Old including from version 2.X.X @todo remove in version 4.X.X
			$file = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptin_' .
				$this->getLanguage() . '_v2.js';
			if (!file_exists($sitePath . $file)) {
				$file = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptin_0_v2.js';
				if (!file_exists($sitePath . $file)) {
					return '';
				}
			}

			$cacheBuster = filemtime($sitePath . $file);
			if (!$cacheBuster) {
				$cacheBuster = '';
			}

			return '<script src="/' . $file . '?' . $cacheBuster . '" type="text/javascript" data-ignore="1"></script>';
		}
	}

	/**
	 * Adds the Cookie Optin CSS if it's generated for the current page.
	 *
	 * Example line: fileadmin/sg_cookie_optin/siteroot-1/cookieOptin.css
	 *
	 * @param string $content
	 * @param array $configuration
	 * @return string
	 */
	public function addCSS($content, array $configuration) {
		$rootPageId = $this->getRootPageId();
		if ($rootPageId <= 0) {
			return '';
		}

		$folder = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_FOLDER);
		if (!$folder) {
			return '';
		}

		$file = $folder . 'siteroot-' . $rootPageId . '/cookieOptin.css';
		$sitePath = defined(PATH_site) ? PATH_site : \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/';
		if (!file_exists($sitePath . $file)) {
			return '';
		}

		$cacheBuster = filemtime($sitePath . $file);
		if (!$cacheBuster) {
			$cacheBuster = '';
		}

		return '<link rel="stylesheet" type="text/css" href="/' . $file . '?' . $cacheBuster . '" media="all">';
	}

	/**
	 * Returns always the first page within the rootline
	 *
	 * @return int
	 */
	protected function getRootPageId() {
		if ($this->rootpage === NULL) {
			/** @var TypoScriptFrontendController $typoScriptFrontendController */
			$typoScriptFrontendController = $GLOBALS['TSFE'];

			$siteRootId = -1;
			foreach ($typoScriptFrontendController->rootLine as $rootLineEntry) {
				if (!isset($rootLineEntry['is_siteroot'])) {
					continue;
				}

				$isSiteRoot = (boolean) $rootLineEntry['is_siteroot'];
				if (!$isSiteRoot) {
					continue;
				}

				$siteRootId = (int) $rootLineEntry['uid'];
				break;
			}

			$this->rootpage = $siteRootId;
		}

		return $this->rootpage;
	}

	/**
	 * Returns always the first page within the rootline
	 *
	 * @return int
	 */
	protected function getLanguage() {
		if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 10000000) {
			/** @var LanguageAspect $languageAspect */
			$languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
			$sysLanguageUid = $languageAspect->getId();
		} else {
			/** @var TypoScriptFrontendController $typoScriptFrontendController */
			$typoScriptFrontendController = $GLOBALS['TSFE'];
			$sysLanguageUid = $typoScriptFrontendController->sys_language_uid;
		}
		return $sysLanguageUid;
	}
}
