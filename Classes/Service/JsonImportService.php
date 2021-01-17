<?php

namespace SGalinski\SgCookieOptin\Service;

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

use SGalinski\SgCookieOptin\Exception\JsonImportException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class SGalinski\SgCookieOptin\Service\JsonImportService
 */
class JsonImportService {

	/**
	 * Hardcoded default values that are reused on different places, including Import
	 */
	const TEXT_BANNER_DESCRIPTION = 'Auf unserer Webseite werden Cookies verwendet. Einige davon werden zwingend benötigt, während es uns andere ermöglichen, Ihre Nutzererfahrung auf unserer Webseite zu verbessern.';
	const TEXT_ESSENTIAL_DESCRIPTION = 'Essentielle Cookies werden für grundlegende Funktionen der Webseite benötigt. Dadurch ist gewährleistet, dass die Webseite einwandfrei funktioniert.';
	const TEXT_IFRAME_DESCRIPTION = 'Wir verwenden auf unserer Website externe Inhalte, um Ihnen zusätzliche Informationen anzubieten.';
	const TEXT_ESSENTIAL_DEFAULT_COOKIE_PURPOSE = 'Dieses Cookie wird verwendet, um Ihre Cookie-Einstellungen für diese Website zu speichern.';

	/**
	 * Separates the locale in the filename
	 */
	const LOCALE_SEPARATOR = '--';

	/**
	 * Stores the mapping data for the default language so that the next imported languages can have it's entities
	 * connected correspondingly
	 *
	 * @var null|array
	 */
	private $defaultLanguageIdMappingLookup = NULL;

	/**
	 * Imports the cookie OptIn data
	 *
	 * @param array $jsonData
	 * @param int $pid
	 * @param null|int $sysLanguageUid
	 * @param null|int $defaultLanguageOptinId
	 * @return string
	 */
	public function importJsonData($jsonData, $pid, $sysLanguageUid = NULL, $defaultLanguageOptinId = NULL) {
		// extract group data into other variables so that we can import all the settings information with little to no
		// value mapping
		$cookieGroups = $jsonData['cookieGroups'];
		$iframeGroup = $jsonData['iFrameGroup'];
		$footerLinks = $jsonData['footerLinks'];

		if (!is_array($footerLinks)) {
			$footerLinks = [];
		}

		unset($jsonData['cookieGroups']);
		unset($jsonData['iFrameGroup']);
		unset($jsonData['footerLinks']);

		// flatten the data into one array to prepare it for SQL
		$flatJsonData = [];
		array_walk_recursive(
			$jsonData, function ($value, $key) use (&$flatJsonData) {
			$flatJsonData[$key] = $value;
		}
		);

		// add required system data and remove junk from the JSON
		unset($flatJsonData['markup']);
		$flatJsonData['pid'] = $pid;
		$flatJsonData['crdate'] = $GLOBALS['EXEC_TIME'];
		$flatJsonData['tstamp'] = $GLOBALS['EXEC_TIME'];
		$flatJsonData['cruser_id'] = $GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column];
		$flatJsonData['navigation'] = $this->buildNavigationFromFooterLinks($footerLinks);
		// essential_description
		$flatJsonData['essential_description'] = $cookieGroups[0]['description'];
		$flatJsonData['iframe_description'] = $iframeGroup['description'];
		if ($sysLanguageUid !== NULL) {
			$flatJsonData['sys_language_uid'] = $sysLanguageUid;
			$flatJsonData['l10n_parent'] = $defaultLanguageOptinId;
		}

		// store the optin object
		$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
		$queryBuilder = $connectionPool->getQueryBuilderForTable('tx_sgcookieoptin_domain_model_optin');
		$queryBuilder->insert('tx_sgcookieoptin_domain_model_optin')->values($flatJsonData);
		$queryBuilder->execute();

		$optInId = $queryBuilder->getConnection()->lastInsertId();

		// Add Groups
		foreach ($cookieGroups as $groupIndex => $group) {
			$groupIdentifier = $groupIndex;
			if ($group['groupName'] !== 'essential' && $group['groupName'] !== 'iframes') {
				$groupId = $this->addGroup(
					$group, $groupIndex, $optInId, $sysLanguageUid, $defaultLanguageOptinId, $connectionPool
				);
			} else {
				// we use this only for the internal language mapping lookup array
				$groupIdentifier = $group['groupName'];
			}

			// store the mapping
			if ($defaultLanguageOptinId === NULL) {
				$this->defaultLanguageIdMappingLookup[$groupIdentifier] = [
					'id' => $groupId,
					'cookies' => [],
					'scripts' => []
				];
			}

			// Add Cookies
			if (isset($group['cookieData'])) {
				foreach ($group['cookieData'] as $cookieIndex => $cookie) {
					if ($cookie['pseudo'] === TRUE) {
						continue;
					}
					$cookieId = $this->addCookie(
						$cookie, $cookieIndex, $group['groupName'], $optInId, $groupId,
						$sysLanguageUid, $groupIdentifier, $defaultLanguageOptinId, $connectionPool
					);
					if ($defaultLanguageOptinId === NULL) {
						$this->defaultLanguageIdMappingLookup[$groupIdentifier]['cookies'][$cookieIndex] = $cookieId;
					}
				}
			}
			// Add Scripts
			if (isset($group['scriptData'])) {
				foreach ($group['scriptData'] as $scriptIndex => $script) {
					$scriptId = $this->addScript(
						$script, $scriptIndex, $group['groupName'], $optInId, $groupId, $sysLanguageUid,
						$defaultLanguageOptinId, $groupIdentifier, $connectionPool
					);
					if ($defaultLanguageOptinId === NULL) {
						$this->defaultLanguageIdMappingLookup[$groupIdentifier]['scripts'][$scriptIndex] = $scriptId;
					}
				}
			}
		}

		return $optInId;
	}

	/**
	 * Builds the navigation CSV string from the footerlinks
	 *
	 * @param array $footerLinks
	 * @return string
	 */
	protected function buildNavigationFromFooterLinks(array $footerLinks) {
		$navigationIds = [];
		foreach ($footerLinks as $footerLink) {
			if (isset($footerLink['uid'])) {
				$navigationIds[] = $footerLink['uid'];
			}
		}
		return implode(', ', $navigationIds);
	}

	/**
	 * Adds a group entry in the database
	 *
	 * @param array $group
	 * @param int $groupIndex
	 * @param int $optInId
	 * @param int|null $sysLanguageUid
	 * @param int|null $defaultLanguageOptinId
	 * @param ConnectionPool $connectionPool
	 * @return mixed
	 */
	protected function addGroup($group, $groupIndex, $optInId, $sysLanguageUid, $defaultLanguageOptinId, $connectionPool
	) {
		$groupData = [
			'cruser_id' => $GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column],
			'group_name' => $group['groupName'],
			'title' => $group['label'],
			'description' => $group['description'],
			'sorting' => $groupIndex + 1,
			'parent_optin' => $optInId,
			'crdate' => $GLOBALS['EXEC_TIME'],
			'tstamp' => $GLOBALS['EXEC_TIME'],
		];
		if ($defaultLanguageOptinId !== NULL) {
			$groupData['l10n_parent'] = $this->defaultLanguageIdMappingLookup[$groupIndex]['id'];
			$groupData['sys_language_uid'] = $sysLanguageUid;
		}

		$queryBuilder = $connectionPool->getQueryBuilderForTable('tx_sgcookieoptin_domain_model_group');
		$queryBuilder->insert('tx_sgcookieoptin_domain_model_group')->values($groupData);
		$queryBuilder->execute();

		return $queryBuilder->getConnection()->lastInsertId();
	}

	/**
	 * Adds a cookie entry in the database
	 *
	 * @param array $cookie
	 * @param int $cookieIndex
	 * @param string $groupName
	 * @param int $optInId
	 * @param int $groupId
	 * @param int|null $sysLanguageUid
	 * @param string $groupIdentifier
	 * @param int $defaultLanguageOptinId
	 * @param ConnectionPool $connectionPool
	 */
	protected function addCookie(
		$cookie, $cookieIndex, $groupName, $optInId, $groupId, $sysLanguageUid, $groupIdentifier,
		$defaultLanguageOptinId, $connectionPool
	) {
		$cookieData = [
			'cruser_id' => $GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column],
			'name' => $cookie['Name'],
			'provider' => $cookie['Provider'],
			'purpose' => $cookie['Purpose'],
			'lifetime' => $cookie['Lifetime'],
			'sorting' => $cookieIndex + 1,
			'crdate' => $GLOBALS['EXEC_TIME'],
			'tstamp' => $GLOBALS['EXEC_TIME'],
		];
		switch ($groupName) {
			case 'essential':
				$cookieData['parent_optin'] = $optInId;
				break;
			case 'iframes':
				$cookieData['parent_iframe'] = $optInId;
				break;
			default:
				$cookieData['parent_group'] = $groupId;
				break;
		}
		if ($defaultLanguageOptinId !== NULL) {
			$cookieData['sys_language_uid'] = $sysLanguageUid;
			$cookieData['l10n_parent'] = $this->defaultLanguageIdMappingLookup[$groupIdentifier]['cookies'][$cookieIndex];
		}
		$queryBuilder = $connectionPool->getQueryBuilderForTable('tx_sgcookieoptin_domain_model_cookie');
		$queryBuilder->insert('tx_sgcookieoptin_domain_model_cookie')->values($cookieData);
		$queryBuilder->execute();

		return $queryBuilder->getConnection()->lastInsertId();
	}

	/**
	 * Adds a script entry in the database
	 *
	 * @param array $script
	 * @param int $scriptIndex
	 * @param int $optInId
	 * @param int $groupId
	 * @param int|null $sysLanguageUid
	 * @param int|null $defaultLanguageOptinId
	 * @param string $groupIdentifier
	 * @param ConnectionPool $connectionPool
	 * @return mixed
	 */
	protected function addScript(
		$script, $scriptIndex, $groupName, $optInId, $groupId, $sysLanguageUid, $defaultLanguageOptinId,
		$groupIdentifier, $connectionPool
	) {
		$scriptData = [
			'cruser_id' => $GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column],
			'title' => $script['title'],
			'script' => $script['script'],
			'html' => $script['html'],
			'sorting' => $scriptIndex + 1,
			'crdate' => $GLOBALS['EXEC_TIME'],
			'tstamp' => $GLOBALS['EXEC_TIME'],
		];
		if ($groupName === 'essential') {
			$scriptData['parent_optin'] = $optInId;
		} else {
			$scriptData['parent_group'] = $groupId;
		}
		if ($defaultLanguageOptinId !== NULL) {
			$scriptData['sys_language_uid'] = $sysLanguageUid;
			$scriptData['l10n_parent'] = $this->defaultLanguageIdMappingLookup[$groupIdentifier]['scripts'][$scriptIndex];
		}
		$queryBuilder = $connectionPool->getQueryBuilderForTable('tx_sgscriptoptin_domain_model_script');
		$queryBuilder->insert('tx_sgcookieoptin_domain_model_script')->values($scriptData);
		$queryBuilder->execute();

		return $queryBuilder->getConnection()->lastInsertId();
	}

	/**
	 * Parses the uploaded files, prepares and stores the data into the session
	 *
	 * @param array $languages
	 * @throws JsonImportException
	 */
	public function parseAndStoreImportedData(array $languages) {
		$dataStorage = [];
		unset($_SESSION['tx_sgcookieoptin']['importJsonData']);
		// get and import the default language
		if ($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['type']['file'] !== 'application/json'
			|| $_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['error']['file'] !== 0) {
			throw new JsonImportException(
				LocalizationUtility::translate('frontend.error.theFileCouldNotBeUploaded', 'sg_cookie_optin'),
				102
			);
		}

		$languagesJson = json_decode(
			file_get_contents($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['tmp_name']['file']), TRUE
		);

		if (!$languagesJson) {
			throw new JsonImportException(
				LocalizationUtility::translate(
					'frontend.error.theImportedFileDoesNotContainProperlyFormattedJson', 'sg_cookie_optin'
				), 103
			);
		}

		foreach ($languagesJson as $locale => $jsonData) {
			$defaultFound = FALSE;
			foreach ($languages as $language) {
				if ($language['uid'] === 0 && strpos($language['locale'], $locale) !== FALSE) {
					$defaultLanguageId = $language['uid'];
					$defaultLanguageLocale = $locale;
					$defaultFound = TRUE;
					break;
				}
			}

			if (!$defaultFound) {
				continue;
			}

			$defaultLanguageJsonData = $jsonData;
			$dataStorage['defaultLanguageId'] = $defaultLanguageId;
			$dataStorage['languageData'][$defaultLanguageId] = $defaultLanguageJsonData;
			break;
		}

		if (!$defaultFound) {
			throw new JsonImportException(LocalizationUtility::translate('frontend.jsonImport.error.pleaseUploadTheDefaultLanguageConfigurationFile', 'sg_cookie_optin'));
		}

		// import the other languages
		foreach ($languagesJson as $locale => $jsonData) {

			// we already stored that
			if ($locale === $defaultLanguageLocale) {
				continue;
			}

			$languageId = LanguageService::getLanguageIdByLocale($locale, $languages);
			if ($languageId !== NULL) {
				$dataStorage['languageData'][$languageId] = $jsonData;
			}
		}

		// Save into session
		$_SESSION['tx_sgcookieoptin']['importJsonData'] = $dataStorage;
	}
}
