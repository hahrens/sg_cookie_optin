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

use SGalinski\SgCookieOptin\Exception\SaveOptinHistoryException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class OptinHistoryService
 *
 * @package SGalinski\SgCookieOptin\Service
 */
class OptinHistoryService {
	const TYPE_GROUP = 1;

	/**
	 * Saves the optin history
	 *
	 * @param $preferences
	 * @return array
	 */
	public static function saveOptinHistory($preferences) {
		try {
			$jsonInput = json_decode($preferences, TRUE);

			if (!self::validateInput($jsonInput)) {
				throw new SaveOptinHistoryException('Invalid input');
			}

			$insertData = self::prepareInsertData($jsonInput, self::TYPE_GROUP);

			if (count($insertData) < 1) {
				throw new SaveOptinHistoryException('No data to save');
			}

			if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 9000000) {
				foreach ($insertData as $data) {
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_sgcookieoptin_domain_model_user_preference', $data);
				}
			} else {
				$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_sgcookieoptin_domain_model_user_preference');

				foreach ($insertData as $data) {
					$queryBuilder
					   ->insert('tx_sgcookieoptin_domain_model_user_preference')
					   ->values($data)
					   ->execute();
				}
			}

			return [
				'error' => 0,
				'message' => 'OK'
			];
		} catch (\Exception $exception) {
			return [
				'error' => 1,
				'message' => $exception->getMessage()
			];
		}
	}

	/**
	 * Validates the optin history input data
	 *
	 * @param $input
	 * @return bool
	 */
	protected static function validateInput($input) {
		return !(!isset($input['uuid'], $input['version'], $input['cookieValue'], $input['isAll'], $input['identifier'])
			|| ((int) $input['version']) < 1);
	}

	/**
	 * Parses the json input and prepares an array with the data to insert
	 *
	 * @param array $jsonInput
	 * @param int $itemType
	 * @return array
	 */
	private static function prepareInsertData($jsonInput, $itemType) {
		$insertData = [];
		$cookieValuePairs = explode('|', $jsonInput['cookieValue']);
		foreach ($cookieValuePairs as $pair) {
			list($groupName, $value) = explode(':', $pair);
			$insertData[] = [
				'user_hash' => $jsonInput['uuid'],
				'version' => $jsonInput['version'],
				'crdate' => $jsonInput['timestamp'],
				'tstamp' => $jsonInput['timestamp'],
				'item_identifier' => $groupName,
				'item_type' => $itemType,
				'is_all' => (int) $jsonInput['isAll'],
				'is_accepted' => (int) $value,
				'pid' => $jsonInput['identifier'],
			];
		}
		return $insertData;
	}
}
