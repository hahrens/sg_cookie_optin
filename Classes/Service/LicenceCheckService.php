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

use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class LicenceCheckService
 *
 * @package SGalinski\SgCookieOptin\Service
 */
class LicenceCheckService {

	const PRODUCT_KEY = "sg_cookie_optin";
	const REGISTRY_NAMESPACE = 'tx_sgcookieoptin';
	const EXTENSION_NAMESPACE = "SgCookieOptin";
	const IS_KEY_VALID_KEY = 'isKeyValid';

	const ERROR_INVALID_RESPONSE_CODE = -1;
	const ERROR_INVALID_RESPONSE_DATA = -2;
	const ERROR_INVALID_LICENSE_KEY = -3;
	const ERROR_INVALID_LICENSE_STRUCTURE = -4;
	const ERROR_TIMESTAMP_INVALID = -5;
	const ERROR_LICENSE_CHECK_EXCEPTION = -6;

	protected static $lastHttpResponseCode = 0;
	protected static $lastException = "";

	protected static $validUntil;

	const HAS_VALID_LICENSE_UNTIL_TIMESTAMP_KEY = 'hasValidLicenseUntilTimestamp';
	const LICENSE_CHECKED_IN_VERSION_KEY = 'licenceCheckedInVersion';
	const LAST_CHECKED_TIMESTAMP_KEY = 'lastCheckedTimestamp';
	const LAST_LICENSE_KEY_CHECKED_KEY = 'lastLicenseKeyChecked';

	const AMOUNT_OF_DAYS_UNTIL_NEXT_CHECK = 1;
	const AMOUNT_OF_DAYS_UNTIL_WARNING = 30;

	const API_USER = "license_check";
	const API_PASSWORD = "lGKLiHc5We6gBqsggVlwdLNoWv9CEKnWiy7cgMUO";
	const API_URL = "https://shop.sgalinski.de/api/license";

	const CURRENT_VERSION = "3.2";

	/**
	 * @var array
	 */
	private static $versionToReleaseTimestamp = [
		'1.0' => 1571350984,
		'1.1' => 1571695928,
		'1.2' => 1571701161,
		'1.3' => 1571703014,
		'1.4' => 1571705101,
		'1.5' => 1571711597,
		'1.6' => 1571770960,
		'1.7' => 1571788178,
		'1.8' => 1572302073,
		'2.0' => 1575661906,
		'3.0' => 1583361764,
		'3.1' => 1588958065,
		'3.2' => 1600032423,
	];

	/**
	 * @param mixed $validUntil A timestamp, which says the lifetime of this key.
	 * @return boolean True, if the timestamp is invalid.
	 */
	public static function isTimestampInvalid($validUntil) {
		if ($validUntil < 0) {
			return TRUE;
		}
		$releaseTimestampOfCurrentVersion = self::$versionToReleaseTimestamp[self::CURRENT_VERSION];
		if ($releaseTimestampOfCurrentVersion === NULL || $validUntil < $releaseTimestampOfCurrentVersion) {
			return TRUE;
		}

		self::$validUntil = $validUntil;
		return FALSE;
	}

	/**
	 * Should we perform the license check for this key and in this version at this point of time
	 *
	 * @param string $licenseKey
	 * @return bool
	 */
	private static function shouldCheckKey($licenseKey) {
		if ($licenseKey !== self::getLastKey()) {
			self::clearRegistryValues();
			return TRUE;
		}

		if (self::getLicenseCheckedInVersion() !== self::CURRENT_VERSION) {
			return TRUE;
		}

		if (self::getValidLicenseUntilTimestamp() < $GLOBALS['EXEC_TIME']) {
			return TRUE;
		}

		return FALSE;
	}

	private static function getLicenseKey() {
//		return '5NEBTB-FS3QR3-IP2SQI-57ADYR'; // lifetime
//		return 'T6VCXM-DMBDJA-UP8B4L-LDUSRK'; // expiring 18 dec
//		return 'XEALXA-NCLA5K-U6XGX1-VD9DVR'; // valid until end of next year
		return 'E17QZO-Z1GGE4-HT7DVE-IJ6T7R'; // expired
		return ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_LICENSE);
	}

	/**
	 * @return bool|string
	 */
	public static function hasValidLicense() {
		$licenseKey = self::getLicenseKey();
		if (!self::shouldCheckKey($licenseKey)) {
			return TRUE;
		}

		if (!self::isLicenseServerReachable()) {
			return TRUE;
		}

		if (!self::isLicenseValid($licenseKey)) {
			self::setValidLicense(FALSE);
			self::setValidLicenseUntilTimestamp(0);
		} else {
			self::setValidLicenseUntilTimestamp(self::getValidUntil());
			self::setValidLicense(TRUE);
			self::setLastKey($licenseKey);
			self::setLicenseCheckedInVersion(self::CURRENT_VERSION);
			self::setLastLicenseCheckTimestamp();
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Sets the last key checked for from the registry
	 *
	 * @param $licenseKey
	 */
	protected static function setLastKey($licenseKey) {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::LAST_LICENSE_KEY_CHECKED_KEY, $licenseKey);
	}

	/**
	 * Gets the last key checked for from the registry
	 *
	 * @return mixed|null
	 */
	protected static function getLastKey() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		return $registry->get(self::REGISTRY_NAMESPACE, self::LAST_LICENSE_KEY_CHECKED_KEY);
	}

	/**
	 * Sets if the license is valid in the registry
	 *
	 * @param $isValid
	 */
	protected static function setValidLicense($isValid) {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::IS_KEY_VALID_KEY, $isValid);
	}

	/**
	 * Gets the isValid from the registry
	 *
	 * @return mixed|null
	 */
	protected static function getValidLicense() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		return $registry->get(self::REGISTRY_NAMESPACE, self::IS_KEY_VALID_KEY);
	}

	/**
	 * Stores the valid until timestamp in the registry
	 *
	 * @param $validUntil
	 */
	protected static function setValidLicenseUntilTimestamp($validUntil) {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::HAS_VALID_LICENSE_UNTIL_TIMESTAMP_KEY, $validUntil);
	}

	/**
	 * Gets the valid until timestamp from the registry
	 *
	 * @return mixed|null
	 */
	protected static function getValidLicenseUntilTimestamp() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		return $registry->get(self::REGISTRY_NAMESPACE, self::HAS_VALID_LICENSE_UNTIL_TIMESTAMP_KEY);
	}

	/**
	 * Sets the version that the license was last valid for
	 *
	 * @param $version
	 */
	protected static function setLicenseCheckedInVersion($version) {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::LICENSE_CHECKED_IN_VERSION_KEY, $version);
	}

	/**
	 * Gets the version that the license was last valid for
	 *
	 * @return mixed|null
	 */
	protected static function getLicenseCheckedInVersion() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		return $registry->get(self::REGISTRY_NAMESPACE, self::LICENSE_CHECKED_IN_VERSION_KEY);
	}

	/**
	 * Sets the timestamp of the last check in the registry
	 */
	protected static function setLastLicenseCheckTimestamp() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::LAST_CHECKED_TIMESTAMP_KEY, $GLOBALS['EXEC_TIME']);
	}

	/**
	 * Gets the timestamp of the last check from the registry
	 *
	 * @return mixed|null
	 */
	protected static function getLastLicenseCheckTimestamp() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		return $registry->get(self::REGISTRY_NAMESPACE, self::LAST_CHECKED_TIMESTAMP_KEY);
	}

	/**
	 * Clears the registry values
	 */
	protected static function clearRegistryValues() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->remove(self::REGISTRY_NAMESPACE, self::LAST_CHECKED_TIMESTAMP_KEY);
		$registry->remove(self::REGISTRY_NAMESPACE, self::HAS_VALID_LICENSE_UNTIL_TIMESTAMP_KEY);
		$registry->remove(self::REGISTRY_NAMESPACE, self::LICENSE_CHECKED_IN_VERSION_KEY);
		$registry->remove(self::REGISTRY_NAMESPACE, self::IS_KEY_VALID_KEY);
		$registry->remove(self::REGISTRY_NAMESPACE, self::LAST_LICENSE_KEY_CHECKED_KEY);
	}

	/**
	 * Gets the validUntil date from this current check
	 *
	 * @return mixed
	 */
	public static function getValidUntil() {
		if (self::$validUntil === null) {
			self::$validUntil = self::getValidLicenseUntilTimestamp();
		}
		return self::$validUntil;
	}

	/**
	 * The timestamp of the key lifetime, if the given license key is valid, or -1 if invalid.
	 *
	 * @param $licenseKey A license key, which should be validated.
	 * @return
	 */
	public static function isLicenseValid($licenseKey) {
		if (!self::checkLicenseKeyStructure($licenseKey)) {
			return self::ERROR_INVALID_LICENSE_STRUCTURE;
		}

		$validUntil = self::getValidUntilTimestampByLicenseKey($licenseKey);
		return !self::isTimestampInvalid($validUntil);
	}

	/**
	 * Check if the given license key is valid.
	 *
	 * @param $licenseKey A license key, which should be validated.
	 * @return boolean
	 */
	public static function checkLicenseKeyStructure($licenseKey) {
		// Structure: XXXXXX-XXXXXX-XXXXXX-XXXXXX | All upper case
		if (substr_count($licenseKey, "-") != 3) {
			return FALSE;
		}

		$caseControl = strtoupper($licenseKey);
		return $licenseKey === $caseControl && strlen($licenseKey) === 27;
	}

	/**
	 * True, if the license server is reachable.
	 *
	 * @return boolean
	 */
	public static function isLicenseServerReachable() {
		try {
			$ch = curl_init(self::API_URL);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_USERPWD, self::API_USER . ":" . self::API_PASSWORD);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_exec($ch);
			self::$lastHttpResponseCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if (self::$lastHttpResponseCode !== 200 && self::$lastHttpResponseCode !== 201) {
				return FALSE;
			}
		} catch (\Exception $exception) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Returns The timestamp of the key lifetime, if the given license key is valid, on the server, or -1 if invalid.
	 *
	 * @param string $licenceKey
	 * @return int
	 */
	private static function getValidUntilTimestampByLicenseKey($licenseKey) {
		try {
			$url = self::API_URL . "/" . urldecode($licenseKey) . "?product="
				. self::PRODUCT_KEY;
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_USERPWD, self::API_USER . ":" . self::API_PASSWORD);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$result = curl_exec($ch);
			self::$lastHttpResponseCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if (self::$lastHttpResponseCode !== 200 && self::$lastHttpResponseCode !== 201) {
				return self::ERROR_INVALID_RESPONSE_CODE;
			}

			if (!$result) {
				return self::ERROR_INVALID_RESPONSE_DATA;
			}

			$jsonData = json_decode($result, TRUE);
			if (!$jsonData['serial']['valid']) {
				return self::ERROR_INVALID_LICENSE_KEY;
			}

			return (int) $jsonData['serial']["validUntil"];
		} catch (\Exception $exception) {
			self::$lastException = $exception->getMessage();
		}

		return self::ERROR_LICENSE_CHECK_EXCEPTION;
	}

	/**
	 * Checks whether we are in development context
	 *
	 * @return bool
	 */
	public static function isInDevelopmentContext() {
		$versionNumber = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
		if ($versionNumber >= 9000000) {
			// Since TYPO3 9LTS
			$context = \TYPO3\CMS\Core\Core\Environment::getContext();
		} else {
			// Prior to TYPO3 9LTS
			$context = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()
		}
		return $context->isDevelopment();
	}
}
