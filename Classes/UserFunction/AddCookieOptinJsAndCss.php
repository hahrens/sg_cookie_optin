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

use SGalinski\SgCookieOptin\Service\LicensingService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\SingletonInterface;
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
	 */
	public function addJavaScript($content, array $configuration) {
		if (LicensingService::checkKey() !== LicensingService::STATE_LICENSE_VALID
			&& !LicensingService::isInDemoMode()
		) {
			LicensingService::removeAllCookieOptInFiles();
			return '';
		}

		$rootPageId = $this->getRootPageId();
		if ($rootPageId <= 0 || !$this->isConfigurationOnPage($rootPageId)) {
			return '';
		}

		$file = 'fileadmin/sg_cookie_optin/siteroot-' . $rootPageId . '/' . 'cookieOptin.js';
		if (file_exists(PATH_site . $file)) {
			$jsonFile = 'fileadmin/sg_cookie_optin/siteroot-' . $rootPageId . '/' . 'cookieOptinData_' .
				$this->getLanguage() . '.json';
			if (!file_exists(PATH_site . $jsonFile)) {
				$jsonFile = 'fileadmin/sg_cookie_optin/siteroot-' . $rootPageId . '/' . 'cookieOptinData_0.json';
				if (!file_exists(PATH_site . $jsonFile)) {
					return '';
				}
			}

			return '<script id="cookieOptinData" type="application/json">' . file_get_contents(PATH_site . $jsonFile) .
				'</script><script src="/' . $file . '" type="text/javascript" data-ignore="1"></script>';
		} {
			// Old including from version 2.X.X @todo remove in version 4.X.X
			$file = 'fileadmin/sg_cookie_optin/siteroot-' . $rootPageId . '/' . 'cookieOptin_' .
				$this->getLanguage() . '_v2.js';
			if (!file_exists(PATH_site . $file)) {
				$file = 'fileadmin/sg_cookie_optin/siteroot-' . $rootPageId . '/' . 'cookieOptin_0_v2.js';
				if (!file_exists(PATH_site . $file)) {
					return '';
				}
			}

			$cacheBuster = filemtime(PATH_site . $file);
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
		if ($rootPageId <= 0 || !$this->isConfigurationOnPage($rootPageId)) {
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
	 * Returns true, if a configuration is on the given page id.
	 *
	 * @param int $pageUid
	 *
	 * @return boolean
	 */
	protected function isConfigurationOnPage($pageUid) {
		$pageUid = (int) $pageUid;
		if ($pageUid <= 0) {
			return FALSE;
		}

		$table = 'tx_sgcookieoptin_domain_model_optin';
		if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) <= 9000000) {
			/** @var DatabaseConnection $database */
			$database = $GLOBALS['TYPO3_DB'];
			$rows = $database->exec_SELECTgetSingleRow('uid', $table, 'deleted=0 AND pid =' . $pageUid);
		} else {
			$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
			$queryBuilder = $connectionPool->getQueryBuilderForTable($table);
			$queryBuilder->getRestrictions()
				->removeAll()
				->add(GeneralUtility::makeInstance(DeletedRestriction::class));
			$rows = $queryBuilder->select('uid')
				->from($table)
				->setMaxResults(1)
				->where(
					$queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid, \PDO::PARAM_INT))
				)->execute()->fetchAll();
		}

		return is_array($rows) && count($rows) > 0;
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
		/** @var TypoScriptFrontendController $typoScriptFrontendController */
		$typoScriptFrontendController = $GLOBALS['TSFE'];
		return $typoScriptFrontendController->sys_language_uid;
	}
}
