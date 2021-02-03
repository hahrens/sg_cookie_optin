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

use Doctrine\DBAL\Connection;
use PDO;
use SGalinski\SgCookieOptin\Exception\SaveOptinHistoryException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class OptinHistoryService
 *
 * @package SGalinski\SgCookieOptin\Service
 */
class OptinHistoryService {
	const TYPE_GROUP = 1;

	const TABLE_NAME = 'tx_sgcookieoptin_domain_model_user_preference';

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
					$GLOBALS['TYPO3_DB']->exec_INSERTquery(self::TABLE_NAME, $data);
				}
			} else {
				$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_sgcookieoptin_domain_model_user_preference');

				foreach ($insertData as $data) {
					$queryBuilder
					   ->insert(self::TABLE_NAME)
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
		// we want the next 3 values to be identical for all items of this preference
		$preferenceHash = StringUtility::getUniqueId();
		$tstamp = date('Y-m-d H:i:s', $GLOBALS['EXEC_TIME']);
		$date = substr($tstamp, 0, 10);
		foreach ($cookieValuePairs as $pair) {
			list($groupName, $value) = explode(':', $pair);
			$insertData[] = [
				'user_hash' => $jsonInput['uuid'],
				'version' => $jsonInput['version'],
				'tstamp' =>  $tstamp,
				'date' => $date,
				'preference_hash' => $preferenceHash,
				'item_identifier' => $groupName,
				'item_type' => $itemType,
				'is_all' => (int) $jsonInput['isAll'],
				'is_accepted' => (int) $value,
				'pid' => $jsonInput['identifier'],
			];
		}
		return $insertData;
	}

	public static function searchUserHistory($parameters) {
		$connection = GeneralUtility::makeInstance(ConnectionPool::class)
			->getConnectionForTable(self::TABLE_NAME);

		//TODO: pid
		$parameters['pid'] = 1;

		$queryParams = [':pid' => $parameters['pid'],
			':from_date' => $parameters['from_date'],
			':to_date' => $parameters['to_date']];
		$paramTypes = [PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_STR];
		$query = 'SELECT * FROM ' . self::TABLE_NAME .
			" WHERE pid = :pid AND DATE(tstamp) >= :from_date AND DATE(tstamp) <= :to_date ";

		if (!empty($parameters['user_hash'])) {
			$queryParams['user_hash'] = $parameters['user_hash'];
			$query .= " AND user_hash = :user_hash ";
		}

		if ($parameters['page'] && $parameters['per_page']) {
			$page = (int) $parameters['page'];
			$perPage = (int) $parameters['per_page'];
			if ($page > 0 && $perPage > 0) {
				$offset = $perPage * ($page - 1);
				$query .= " LIMIT $offset, $perPage";
			}
		}

		$result = $connection->executeQuery($query, $queryParams);
		return $result->fetchAllAssociative();
	}
}
