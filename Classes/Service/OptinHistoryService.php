<?php

namespace SGalinski\SgCookieOptin\Service;

use PDO;
use SGalinski\SgCookieOptin\Exception\SaveOptinHistoryException;

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
class OptinHistoryService {
	const TYPE_GROUP = 1;

	/**
	 * Saves the optin history
	 *
	 * @param $preferences
	 * @return array
	 */
	public static function saveOptinHistory($preferences) {
		if (isset($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'])) {
			$host = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host'];
			$db = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'];
			$user = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user'];
			$pass = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['password'];
		} else {
			$host = $GLOBALS['TYPO3_CONF_VARS']['DB']['host'];
			$db = $GLOBALS['TYPO3_CONF_VARS']['DB']['database'];
			$user = $GLOBALS['TYPO3_CONF_VARS']['DB']['username'];
			$pass = $GLOBALS['TYPO3_CONF_VARS']['DB']['password'];
		}

		try {
			$dsn = "mysql:host=$host;dbname=$db";
			$options = [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => FALSE,
			];
			$pdo = new PDO($dsn, $user, $pass, $options);

			$jsonInput = json_decode($preferences, TRUE);

			if (!self::validateInput($jsonInput)) {
				throw new SaveOptinHistoryException('Invalid input');
			}

			$insertData = self::prepareInsertData($jsonInput);

			if (count($insertData) < 1) {
				throw new SaveOptinHistoryException('No data to save');
			}

			$statement = $pdo->prepare(
				'INSERT INTO `tx_sgcookieoptin_domain_model_user_preference`
			(pid, user_uid, version, crdate, tstamp, item_identifier, item_type, is_accepted, is_all,
			cruser_id, deleted, hidden ) VALUES (:pid, :user_uid, :version, :crdate, :tstamp, :item_identifier,
			:item_type, :is_accepted, :is_all, 0, 0 ,0)'
			);
			foreach ($insertData as $data) {
				$statement->execute($data);
			}

			return [
				'error' => 0,
				'message' => ''
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
	 * @param $jsonInput
	 * @return array
	 */
	private static function prepareInsertData($jsonInput) {
		$insertData = [];
		$cookieValuePairs = explode('|', $jsonInput['cookieValue']);
		foreach ($cookieValuePairs as $pair) {
			list($groupName, $value) = explode(':', $pair);
			$insertData[] = [
				'user_uid' => $jsonInput['uuid'],
				'version' => $jsonInput['version'],
				'crdate' => $jsonInput['timestamp'],
				'tstamp' => $jsonInput['timestamp'],
				'item_identifier' => $groupName,
				'item_type' => self::TYPE_GROUP,
				'is_all' => (int) $jsonInput['isAll'],
				'is_accepted' => (int) $value,
				'pid' => $jsonInput['identifier'],
			];
		}
		return $insertData;
	}
}
