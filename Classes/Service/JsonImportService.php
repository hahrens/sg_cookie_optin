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
				$groupData = [
					'cruser_id' => $GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column],
					'group_name' => $group['groupName'],
					'description' => $group['description'],
					'sorting' => $groupIndex + 1,
					'parent_optin' => $optInId,
					'crdate' => time(),
					'tstamp' => time(),
				];
				if ($defaultLanguageOptinId !== NULL) {
					$groupData['l10n_parent'] = $this->defaultLanguageIdMappingLookup[$groupIndex]['id'];
					$groupData['sys_language_uid'] = $sysLanguageUid;
				}

				$queryBuilder = $connectionPool->getQueryBuilderForTable('tx_sgcookieoptin_domain_model_group');
				$queryBuilder->insert('tx_sgcookieoptin_domain_model_group')->values($groupData);
				$queryBuilder->execute();

				$groupId = $queryBuilder->getConnection()->lastInsertId();

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
					$cookieData = [
						'cruser_id' => $GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column],
						'name' => $cookie['Name'],
						'provider' => $cookie['Provider'],
						'purpose' => $cookie['Purpose'],
						'lifetime' => $cookie['Lifetime'],
						'sorting' => $cookieIndex + 1,
						'crdate' => time(),
						'tstamp' => time(),
					];
					switch ($group['groupName']) {
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

					$cookieId = $queryBuilder->getConnection()->lastInsertId();
					if ($defaultLanguageOptinId === NULL) {
						$this->defaultLanguageIdMappingLookup[$groupIdentifier]['cookies'][$cookieIndex] = $cookieId;
					}
				}
			}
			// Add Scripts
			if (isset($group['scriptData'])) {
				foreach ($group['scriptData'] as $scriptIndex => $script) {
					$scriptData = [
						'cruser_id' => $GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column],
						'title' => $script['title'],
						'script' => $script['script'],
						'html' => $script['html'],
						'sorting' => $scriptIndex + 1,
						'crdate' => time(),
						'tstamp' => time(),
					];
					switch ($group['groupName']) {
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

					$scriptId = $queryBuilder->getConnection()->lastInsertId();
					if ($defaultLanguageOptinId === NULL) {
						$this->defaultLanguageIdMappingLookup[$groupIdentifier]['scripts'][$scriptIndex] = $scriptId;
					}
				}
			}
		}

		return $optInId;
	}
}
