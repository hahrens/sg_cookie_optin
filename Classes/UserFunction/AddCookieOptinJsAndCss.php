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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Adds the Cookie Optin JavaScript if it's generated for the current page.
 */
class AddCookieOptinJsAndCss {
	/**
	 * Adds the Cookie Optin JavaScript if it's generated for the current page.
	 *
	 * Example line: fileadmin/sg_cookie_optin/siteroot-1/cookieOptin.js
	 *
	 * @param string $content
	 * @param array $configuration
	 * @return string
	 */
	public function addJavaScript($content, array $configuration) {
		$disableOptIn = (bool) GeneralUtility::_GP('disableOptIn');
		if ($disableOptIn) {
			return '';
		}

		$rootPageId = $this->getRootPageId();
		if ($rootPageId <= 0) {
			return '';
		}

		$file = 'fileadmin/sg_cookie_optin/siteroot-' . $rootPageId . '/' . 'cookieOptin_' .
			$this->getLanguage() . '.js';
		if (!file_exists(PATH_site . $file)) {
			$file = 'fileadmin/sg_cookie_optin/siteroot-' . $rootPageId . '/' . 'cookieOptin_0.js';
			if (!file_exists(PATH_site . $file)) {
				return '';
			}
		}

		$cacheBuster = filemtime(PATH_site . $file);
		if (!$cacheBuster) {
			$cacheBuster = '';
		}

		return '<script src="/' . $file . '?' . $cacheBuster . '" type="text/javascript"></script>';
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
		$disableOptIn = (bool) GeneralUtility::_GP('disableOptIn');
		if ($disableOptIn) {
			return '';
		}

		$rootPageId = $this->getRootPageId();
		if ($rootPageId <= 0) {
			return '';
		}

		$file = 'fileadmin/sg_cookie_optin/siteroot-' . $rootPageId . '/cookieOptin.css';
		if (!file_exists(PATH_site . $file)) {
			return '';
		}

		$cacheBuster = filemtime(PATH_site . $file);
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
		/** @var TypoScriptFrontendController $typoScriptFrontendController */
		$typoScriptFrontendController = $GLOBALS['TSFE'];

		return (isset($typoScriptFrontendController->rootLine[0]['uid']) ?
			(int) $typoScriptFrontendController->rootLine[0]['uid'] : -1
		);
	}

	/**
	 * Returns always the first page within the rootline
	 *
	 * @return int
	 */
	protected function getLanguage() {
		/** @var TypoScriptFrontendController $typoScriptFrontendController */
		$typoScriptFrontendController = $GLOBALS['TSFE'];
		return $typoScriptFrontendController->sys_language_uid;
	}
}
