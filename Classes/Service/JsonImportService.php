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

/**
 * Class SGalinski\SgCookieOptin\Service\JsonImportService
 */
class JsonImportService {

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
		unset($jsonData['cookieGroups']);
		unset($jsonData['iFrameGroup']);

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
		$flatJsonData['crdate'] = time();
		$flatJsonData['tstamp'] = time();
		$flatJsonData['cruser_id'] = $GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column];
		// essential_description TODO: is essential always 0
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
				$groupId = $this->addGroup($group, $groupIndex, $optInId, $sysLanguageUid, $defaultLanguageOptinId, $connectionPool);
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
					$cookieId = $this->addCookie($cookie, $cookieIndex, $group['groupName'], $optInId, $groupId,
						$sysLanguageUid, $groupIdentifier, $defaultLanguageOptinId, $connectionPool);
					if ($defaultLanguageOptinId === NULL) {
						$this->defaultLanguageIdMappingLookup[$groupIdentifier]['cookies'][$cookieIndex] = $cookieId;
					}
				}
			}
			// Add Scripts
			if (isset($group['scriptData'])) {
				foreach ($group['scriptData'] as $scriptIndex => $script) {
					$scriptId = $this->addScript($script, $scriptIndex, $group['groupName'], $optInId, $groupId, $sysLanguageUid,
						$defaultLanguageOptinId, $groupIdentifier, $connectionPool);
					if ($defaultLanguageOptinId === NULL) {
						$this->defaultLanguageIdMappingLookup[$groupIdentifier]['scripts'][$scriptIndex] = $scriptId;
					}
				}
			}
		}

		return $optInId;
	}

	/**
	 * @param array $group
	 * @param int $groupIndex
	 * @param int $optInId
	 * @param int|null $sysLanguageUid
	 * @param int|null $defaultLanguageOptinId
	 * @param ConnectionPool $connectionPool
	 * @return mixed
	 */
	protected function addGroup($group, $groupIndex, $optInId, $sysLanguageUid, $defaultLanguageOptinId, $connectionPool) {
		$groupData = array(
			'cruser_id' => $GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column],
			'group_name' => $group['groupName'],
			'title' => $group['label'],
			'description' => $group['description'],
			'sorting' => $groupIndex + 1,
			'parent_optin' => $optInId,
			'crdate' => time(),
			'tstamp' => time(),
		);
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
	protected function addCookie($cookie, $cookieIndex, $groupName, $optInId, $groupId, $sysLanguageUid, $groupIdentifier,
		$defaultLanguageOptinId, $connectionPool) {
		$cookieData = array(
			'cruser_id' => $GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column],
			'name' => $cookie['Name'],
			'provider' => $cookie['Provider'],
			'purpose' => $cookie['Purpose'],
			'lifetime' => $cookie['Lifetime'],
			'sorting' => $cookieIndex + 1,
			'crdate' => time(),
			'tstamp' => time(),
		);
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
	protected function addScript($script, $scriptIndex, $groupName, $optInId, $groupId, $sysLanguageUid, $defaultLanguageOptinId,
		$groupIdentifier, $connectionPool) {
		$scriptData = [
			'cruser_id' => $GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column],
			'title' => $script['title'],
			'script' => $script['script'],
			'html' => $script['html'],
			'sorting' => $scriptIndex + 1,
			'crdate' => time(),
			'tstamp' => time(),
		];
		switch ($groupName) {
			case 'essential':
				$scriptData['parent_optin'] = $optInId;
				break;
			default:
				$scriptData['parent_group'] = $groupId;
				break;
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
	 * @param array $languages
	 * @throws JsonImportException
	 */
	public function parseAndStoreImportedData($languages) {
		$dataStorage = array();
		unset($_SESSION['tx_sgcookieoptin']['importJsonData']);
		// get and import the default language
		foreach ($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['name']['file'] as $key => $fileName) {
			if ($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['type']['file'][$key] !== 'application/json'
				|| $_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['error']['file'][$key] !== 0) {
				// TODO: what to do in case of an error
				continue;
			}
			$fileName = str_replace('.json', '', $fileName);
			$parts = explode('--', $fileName);
			$parts = array_reverse($parts);
			$languageId = (int) $parts[0];
			$locale = $parts[1];

			$defaultFound = FALSE;
			foreach ($languages as $language) {
				if ($language['uid'] === 0 && strpos($language['locale'], $locale) !== FALSE) {
					$defaultLanguageId = $languageId;
					$defaultFound = TRUE;
					break;
				}
			}

			if (!$defaultFound) {
				continue;
			}

			$defaultLanguageJsonData = json_decode(
				file_get_contents($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['tmp_name']['file'][$key]),
				TRUE
			);
			$dataStorage['defaultLanguageId'] = $defaultLanguageId;
			$dataStorage['languageData'][$defaultLanguageId] = $defaultLanguageJsonData;
		}

		if (!$defaultFound) {
			throw new JsonImportException('Please upload the default language configuration file');
		}

		// import the other languages
		foreach ($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['name']['file'] as $key => $fileName) {
			if ($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['type']['file'][$key] !== 'application/json'
				|| $_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['error']['file'][$key] !== 0) {
				// TODO: what to do in case of an error
				continue;
			}

			$fileName = str_replace('.json', '', $fileName);
			$parts = explode('--', $fileName);
			$parts = array_reverse($parts);
			$languageId = (int) $parts[0];

			// we already stored that
			if ($languageId === $defaultLanguageId) {
				continue;
			}

			$jsonData = json_decode(
				file_get_contents($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['tmp_name']['file'][$key]),
				TRUE
			);
			$dataStorage['languageData'][$languageId] = $jsonData;
		}

		// Save into session
		$_SESSION['tx_sgcookieoptin']['importJsonData'] = $dataStorage;
	}
}
