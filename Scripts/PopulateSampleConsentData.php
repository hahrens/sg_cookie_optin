<?php
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

/**
 * Populates the database with sample consent history entries for performance testing
 */

define('PATH_site', dirname(__FILE__, 5) . '/');
//require_once PATH_site . 'typo3conf/LocalConfiguration.php';
require_once PATH_site . 'typo3conf/SiteConfiguration.php';

// Change the values here accordingly, should you have a different configuration
$host = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host'];
$db = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'];
$user = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user'];
$pass = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['password'];
$charset = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['charset'];

define('TABLE_NAME', 'tx_sgcookieoptin_domain_model_user_preference');
$fromDate = strtotime('2021-01-01 00:00:00');
$toDate = strtotime('2021-02-14 23:59:59');
$groups = ['essential', 'analytics', 'iframe'];
$totalEntries = ceil(1000000 / count($groups));
$userUuid = NULL;
$statement = NULL;

try {
	$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => FALSE,
	];
	$pdo = new PDO($dsn, $user, $pass, $options);

	for ($i = 1; $i <= $totalEntries; $i++) {
		$timestamp = random_int($fromDate, $toDate);
		if (!$userUuid || random_int(0, 3) !== 0) {
			$userUuid = uniqid('', TRUE);
		}
		$rows = createRows(1, $timestamp, $groups, random_int(0, 7), $userUuid);
		foreach ($rows as $row) {
			// Prepare the statement if not yet prepared
			if (!$statement) {
				$keys = array_keys($row);
				$query = 'INSERT INTO ' . TABLE_NAME . '(' . implode(',', $keys)
					. ") VALUES (" .
					implode(
						', ',
						array_map(
							function ($paramKey) {
							return ':' . $paramKey;
						},
							$keys
						)
					) . ')';
				$statement = $pdo->prepare($query);
			}

			$statement->execute($row);
		}
	}

	$pdo->query('CREATE TEMPORARY TABLE TEMP_' . TABLE_NAME . ' AS SELECT * FROM ' . TABLE_NAME);
	$pdo->query('TRUNCATE TABLE ' . TABLE_NAME);
	$pdo->query('INSERT INTO ' . TABLE_NAME . ' SELECT * FROM TEMP_' . TABLE_NAME . ' ORDER BY tstamp ASC');
} catch (Exception $exception) {
	echo $exception->getMessage();
	die();
}

/**
 * Creates a database row based on the data and some random magic
 *
 * @param int $pid
 * @param int $timestamp
 * @param array $groups
 * @param int $version
 * @param string $uuid
 * @return array
 * @throws Exception
 */
function createRows(int $pid, int $timestamp, array $groups, int $version, string $uuid): array {
	$preferenceHash = getUniqueId();
	$date = date('Y-m-d', $timestamp);
	$dateTime = date('Y-m-d H:i:s', $timestamp);
	$isAll = random_int(0, 1);
	$rows = [];

	foreach ($groups as $group) {
		$isAccepted = ($group === 'essential' || $isAll) ? 1 : random_int(0, 1);
		$rows[] = [
			'user_hash' => $uuid,
			'version' => $version,
			'tstamp' => $dateTime,
			'date' => $date,
			'preference_hash' => $preferenceHash,
			'item_identifier' => $group,
			'item_type' => 1,
			'is_all' => $isAll,
			'is_accepted' => $isAccepted,
			'pid' => $pid,
		];
	}

	return $rows;
}

function getUniqueId($prefix = '') {
	$uniqueId = uniqid($prefix, TRUE);
	return str_replace('.', '', $uniqueId);
}
