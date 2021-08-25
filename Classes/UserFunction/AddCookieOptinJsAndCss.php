<?php /** @noinspection ConstantMatcherInspection */

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

use SGalinski\SgCookieOptin\Service\BaseUrlService;
use SGalinski\SgCookieOptin\Service\ExtensionSettingsService;
use SGalinski\SgCookieOptin\Service\JsonImportService;
use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
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
	 * @throws AspectNotFoundException
	 * @throws SiteNotFoundException
	 */
	public function addJavaScript($content, array $configuration) {
		if (!LicenceCheckService::isInDevelopmentContext()
		    && !LicenceCheckService::isInDemoMode()
			&& !LicenceCheckService::hasValidLicense()
		) {
			LicenceCheckService::removeAllCookieOptInFiles();
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

		$siteBaseUrl = BaseUrlService::getSiteBaseUrl($this->rootpage);

		$file = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptin.js';
		$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . '/';
		if (file_exists($sitePath . $file)) {
			$jsonFile = $this->getJsonFilePath($folder, $rootPageId, $sitePath);
			if ($jsonFile === NULL) {
				return '';
			}

			// we decode and encode again to remove the PRETTY_PRINT when rendering for better performance on the frontend
			// for easier debugging, you can check the generated file in the fileadmin
			// see https://gitlab.sgalinski.de/typo3/sg_cookie_optin/-/issues/118
			$jsonData = json_decode(file_get_contents($sitePath . $jsonFile), TRUE);
			if (!$jsonData['settings']['disable_for_this_language']) {
				if ($jsonData['settings']['render_assets_inline']) {
					return '<script id="cookieOptinData" type="application/json">' . json_encode($jsonData) .
						"</script>\n".'<script type="text/javascript" data-ignore="1">' . file_get_contents($sitePath . $file) . "</script>\n";
				}

				return '<script id="cookieOptinData" type="application/json">' . json_encode($jsonData) .
					'</script>
					<link rel="preload" as="script" href="' . $siteBaseUrl . $file . '" data-ignore="1">
					<script src="' . $siteBaseUrl . $file . '" data-ignore="1"></script>';
			}
		} else {
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

			return '<script src="' . $siteBaseUrl . $file . '?' . $cacheBuster . '" type="text/javascript" data-ignore="1"></script>';
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
		$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . '/';
		if (!file_exists($sitePath . $file)) {
			return '';
		}

		$cacheBuster = filemtime($sitePath . $file);
		if (!$cacheBuster) {
			$cacheBuster = '';
		}

		$jsonFile = $this->getJsonFilePath($folder, $rootPageId, $sitePath);
		if ($jsonFile) {
			$jsonData = json_decode(file_get_contents($sitePath . $jsonFile), TRUE);
			if ($jsonData['settings']['render_assets_inline']) {
				return '<style>' . file_get_contents($sitePath . $file) .  "</style>\n";
			}
		}

		$siteBaseUrl = BaseUrlService::getSiteBaseUrl($this->rootpage);
		return '<link rel="preload" as="style" href="' . $siteBaseUrl . $file . '?' . $cacheBuster . '" media="all">' . "\n"
			. '<link rel="stylesheet" href="' . $siteBaseUrl . $file . '?' . $cacheBuster . '" media="all">';
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
	 * Get the path to the json file
	 *
	 * @param string $folder
	 * @param int $rootPageId
	 * @param string $sitePath
	 * @return string|null
	 * @throws AspectNotFoundException
	 * @throws SiteNotFoundException
	 */
	protected function getJsonFilePath(string $folder, int $rootPageId, string $sitePath) {
		$jsonFile = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptinData' . JsonImportService::LOCALE_SEPARATOR .
			$this->getLanguageWithLocale() . '.json';
		if (!file_exists($sitePath . $jsonFile)) {
			$jsonFile = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptinData_' .
				$this->getLanguage() . '.json';
			if (!file_exists($sitePath . $jsonFile)) {
				$jsonFile = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptinData_0.json';
				if (!file_exists($sitePath . $jsonFile)) {
					return NULL;
				}
			}
		}

		return $jsonFile;
	}

	/**
	 * Returns the current language id
	 *
	 * @return int
	 * @throws AspectNotFoundException
	 */
	protected function getLanguage() {
		$versionNumber = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
		if ($versionNumber >= 9005000) {
			$languageAspect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				\TYPO3\CMS\Core\Context\Context::class
			)->getAspect('language');
			// no object check, because if the object is not set we don't know which language that is anyway
			return $languageAspect->getId();
		}

		/** @var TypoScriptFrontendController $typoScriptFrontendController */
		$typoScriptFrontendController = $GLOBALS['TSFE'];
		return $typoScriptFrontendController->sys_language_uid;
	}

	/**
	 * Returns the current Language Id with locale
	 *
	 * @return array|false|int|mixed|string
	 * @throws AspectNotFoundException
	 * @throws SiteNotFoundException
	 */
	protected function getLanguageWithLocale() {
		$versionNumber = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
		if ($versionNumber >= 9005000) {
			$languageAspect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				\TYPO3\CMS\Core\Context\Context::class
			)->getAspect('language');
			// no object check, because if the object is not set we don't know which language that is anyway
			$languageId = $languageAspect->getId();
			$site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($this->getRootPageId());
			$language = $site->getLanguageById($languageId);
			$returnString = $language->getLocale() . JsonImportService::LOCALE_SEPARATOR . $language->getLanguageId();
		} else {
			/** @var TypoScriptFrontendController $typoScriptFrontendController */
			$typoScriptFrontendController = $GLOBALS['TSFE'];
			$languageId = $typoScriptFrontendController->sys_language_uid;
			$returnString = JsonImportService::LOCALE_SEPARATOR . $languageId;
		}

		return $returnString;
	}
}
