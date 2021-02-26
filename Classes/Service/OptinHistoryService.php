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

use Exception;
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
	 * @param string $preferences
	 * @return array
	 */
	public static function saveOptinHistory(string $preferences): array {
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
				$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(
					'tx_sgcookieoptin_domain_model_user_preference'
				);

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
		} catch (Exception $exception) {
			return [
				'error' => 1,
				'message' => $exception->getMessage()
			];
		}
	}

	/**
	 * Validates the optin history input data
	 *
	 * @param array $input
	 * @return bool
	 */
	protected static function validateInput(array $input): bool {
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
	private static function prepareInsertData(array $jsonInput, int $itemType): array {
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
				'tstamp' => $tstamp,
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

	/**
	 * Searches the user preferences by the given parameters and returns the data or the count
	 *
	 * @param array $parameters
	 * @param false $isCount
	 * @return array
	 * @throws \Doctrine\DBAL\Exception
	 */
	public static function searchUserHistory(array $parameters, $isCount = FALSE): array {
		$connection = GeneralUtility::makeInstance(ConnectionPool::class)
			->getConnectionForTable(self::TABLE_NAME);

		$query = 'SELECT ';
		$select = [];

		if (!empty($parameters['countField'])) {
			$select[] = 'COUNT(`' . $parameters['countField'] . '`) AS `count_' . $parameters['countField'] . '` ';
		} else if ($isCount) {
			$select[] = 'COUNT(*) ';
		} else {
			$select[] = '* ';
		}

		$queryParamTypes = [PDO::PARAM_INT];
		$queryParameters = [(int) $parameters['pid']];
		$where = ['`pid` =  ?'];

		if (!empty($parameters['user_hash'])) {
			$where[] = '`user_hash` = ?';
			$queryParameters[] = $parameters['user_hash'];
			$queryParamTypes[] = PDO::PARAM_STR;
		}

		if (!empty($parameters['item_identifier'])) {
			$where[] = '`item_type` = ? AND `item_identifier` = ?';
			$queryParameters[] = self::TYPE_GROUP;
			$queryParamTypes[] = PDO::PARAM_INT;
			$queryParameters[] = $parameters['item_identifier'];
			$queryParamTypes[] = PDO::PARAM_STR;
		}

		if (!empty($parameters['version'])) {
			$where[] = '`version` = ?';
			$queryParameters[] = $parameters['version'];
			$queryParamTypes[] = PDO::PARAM_STR;
		}

		// Date comes last, because range filters must be at the end of the index
		$where[] = '`date` BETWEEN ? AND ?';
		$queryParameters[] = $parameters['from_date'];
		$queryParameters[] = $parameters['to_date'];
		$queryParamTypes[] = PDO::PARAM_STR;
		$queryParamTypes[] = PDO::PARAM_STR;

		$groupBy = [];
		if (!empty($parameters['groupBy'])) {
			foreach ($parameters['groupBy'] as $groupByField) {
				$groupBy[] = $groupByField;
				$select[] = $groupByField;
			}
		}

		$query .= implode(', ', $select);
		$query .= ' FROM ' . self::TABLE_NAME;

		if (isset($parameters['useIndex'])) {
			$query .= ' USE INDEX (' . $parameters['useIndex'] . ') ';
		}

		$query .= ' WHERE ' . implode(' AND ', $where);

		if (count($groupBy) > 0) {
			$query .= ' GROUP BY ' . implode(',', $groupBy);
		}

		if (!$isCount && $parameters['page'] && $parameters['per_page']) {
			$page = (int) $parameters['page'];
			$perPage = (int) $parameters['per_page'];
			if ($page > 0 && $perPage > 0) {
				$offset = $perPage * ($page - 1);
				$query .= " LIMIT $offset, $perPage";
			}
		}

		$return = $connection->fetchAllAssociative($query, $queryParameters, $queryParamTypes);
		return $return;
	}

	/**
	 * Creates a list of the available item identifiers filtered by the given parameters
	 *
	 * @param array $parameters
	 * @param int $type
	 * @return array
	 */
	public static function getItemIdentifiers($parameters = [], $type = self::TYPE_GROUP): array {
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
			->getQueryBuilderForTable(self::TABLE_NAME);
		$queryBuilder->select('item_identifier');

		$queryBuilder->from(self::TABLE_NAME)
			->where(
				$queryBuilder->expr()->eq(
					'pid', $queryBuilder->createNamedParameter((int) $parameters['pid'], PDO::PARAM_INT)
				)
			);

		if (isset($parameters['from_date'], $parameters['to_date'])) {
			$queryBuilder->andWhere(
				$queryBuilder->expr()->gte('date', $queryBuilder->createNamedParameter($parameters['from_date']))
			)
				->andWhere(
					$queryBuilder->expr()->lte('date', $queryBuilder->createNamedParameter($parameters['to_date']))
				);
		}

		$queryBuilder->andWhere(
			$queryBuilder->expr()->eq('item_type', $queryBuilder->createNamedParameter($type, PDO::PARAM_INT))
		);
		$queryBuilder->addGroupBy('item_type');
		$queryBuilder->addGroupBy('item_identifier');

		return array_column($queryBuilder->execute()->fetchAllAssociative(), 'item_identifier');
	}

	/**
	 * Gets the available versions
	 *
	 * @param array $parameters
	 * @return array
	 */
	public static function getVersions(array $parameters): array {
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
			->getQueryBuilderForTable(self::TABLE_NAME);
		$queryBuilder->select('version')
			->from(self::TABLE_NAME)
			->where(
				$queryBuilder->expr()->eq(
					'pid', $queryBuilder->createNamedParameter((int) $parameters['pid'], PDO::PARAM_INT)
				)
			)
			->addGroupBy('version')
			->orderBy('version', 'asc');

		return array_column($queryBuilder->execute()->fetchAllAssociative(), 'version');
	}
}
