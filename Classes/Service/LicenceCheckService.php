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

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class LicenceCheckService
 *
 * @package SGalinski\SgCookieOptin\Service
 */
class LicenceCheckService {

	const STATE_LICENSE_VALID = 2;
	const STATE_LICENSE_INVALID = 1;
	const STATE_LICENSE_NOT_SET = 0;

	const DEMO_MODE_KEY = 'demo_mode';
	const DEMO_MODE_LIFETIME = 86400;
	const DEMO_MODE_MAX_AMOUNT = 3;

	/**
	 * The product key from ShopWare
	 */
	const PRODUCT_KEY = 'sg_cookie_optin';

	/**
	 * Namespace for the sys registry
	 */
	const REGISTRY_NAMESPACE = 'tx_sgcookieoptin';

	/**
	 * Keys for the sys registry
	 */
	const IS_KEY_VALID_KEY = 'isKeyValid';
	const LAST_WARNING_TIMESTAMP_KEY = 'lastWarningTimestamp';
	const HAS_VALID_LICENSE_UNTIL_TIMESTAMP_KEY = 'hasValidLicenseUntilTimestamp';
	const LICENSE_CHECKED_IN_VERSION_KEY = 'licenceCheckedInVersion';
	const LAST_CHECKED_TIMESTAMP_KEY = 'lastCheckedTimestamp';
	const LAST_AJAX_TIMESTAMP_KEY = 'lastAjaxTimestamp';
	const LAST_LICENSE_KEY_CHECKED_KEY = 'lastLicenseKeyChecked';

	/**
	 * Error codes
	 */
	const ERROR_INVALID_RESPONSE_CODE = -1;
	const ERROR_INVALID_RESPONSE_DATA = -2;
	const ERROR_INVALID_LICENSE_KEY = -3;
	const ERROR_INVALID_LICENSE_STRUCTURE = -4;
	const ERROR_TIMESTAMP_INVALID = -5;
	const ERROR_LICENSE_CHECK_EXCEPTION = -6;

	/**
	 * Earliest TYPO3 Version that we support
	 */
	const EARLIEST_SUPPORTED_VERSION = 8000000;

	/**
	 * Last response code from server
	 *
	 * @var int
	 */
	protected static $lastHttpResponseCode = 0;

	/**
	 * The last exception from the server
	 *
	 * @var string
	 */
	protected static $lastException = '';

	/**
	 * The validUntil timestamp
	 *
	 * @var null|int
	 */
	protected static $validUntil;

	/**
	 * Check the license key once per how many days
	 */
	const AMOUNT_OF_DAYS_UNTIL_NEXT_CHECK = 1;

	/**
	 * Show a warning if the license has expired but we are still in the same version once per how many days
	 */
	const AMOUNT_OF_DAYS_UNTIL_WARNING = 30;

	/**
	 * License server credentials
	 */
	const API_USER = 'license_check';
	const API_PASSWORD = 'lGKLiHc5We6gBqsggVlwdLNoWv9CEKnWiy7cgMUO';
	const API_URL = 'https://shop.sgalinski.de/api/license';

	/**
	 * The current extension version
	 */
	const CURRENT_VERSION = '4.2';

	/**
	 * @var array
	 */
	private static $versionToReleaseTimestamp = [
		'1.0' => 1571350984, // 2019-10-17T22:23:04Z
		'1.1' => 1571695928, // 2019-10-21T22:12:08Z
		'1.2' => 1571701161, // 2019-10-21T23:39:21Z
		'1.3' => 1571703014, // 2019-10-22T00:10:14Z
		'1.4' => 1571705101, // 2019-10-22T00:45:01Z
		'1.5' => 1571711597, // 2019-10-22T02:33:17Z
		'1.6' => 1571770960, // 2019-10-22T19:02:40Z
		'1.7' => 1571788178, // 2019-10-22T23:49:38Z
		'1.8' => 1572302073, // 2019-10-28T22:34:33Z
		'2.0' => 1575661906, // 2019-12-06T19:51:46Z
		'3.0' => 1583361764, // 2020-03-04T22:42:44Z
		'3.1' => 1588958065, // 2020-05-08T17:14:25Z
		'3.2' => 1600032423, // 2020-09-13T21:27:03Z
		'3.3' => 1610914552, // 2021-01-17T20:15:52Z
		'4.0' => 1614345302, // 2021-02-26T13:15:02Z
		'4.1' => 1619200227, // 2021-04-23T17:50:27Z
		'4.2' => 1621515339, // 2021-05-20T12:55:39Z
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
	public static function shouldCheckKey($licenseKey) {
		if ($licenseKey !== self::getLastKey()) {
			return TRUE;
		}

		if (self::getLicenseCheckedInVersion() !== self::CURRENT_VERSION) {
			return TRUE;
		}

		// the license was valid last time we checked, but it has expired and we haven't done another check since it expired
		// let's make sure we don't have the wrong state in this case
		$licenseExpirationDate = self::getValidLicenseUntilTimestamp();
		/** @noinspection NotOptimalIfConditionsInspection */
		return self::getValidLicense() && $licenseExpirationDate < $GLOBALS['EXEC_TIME']
			&& $licenseExpirationDate >= self::getLastLicenseCheckTimestamp();
	}

	/**
	 * Returns the license key that has been set
	 *
	 * @return string
	 */
	public static function getLicenseKey() {
		return (string) ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_LICENSE);
	}

	/**
	 * Checks whether the system has a valid license
	 *
	 * @return bool
	 */
	public static function hasValidLicense() {

		$licenseKey = self::getLicenseKey();
		if (!self::shouldCheckKey($licenseKey)) {
			return self::getValidLicense();
		}
		self::clearRegistryValues();

		if (!self::isLicenseServerReachable()) {
			return TRUE;
		}

		if (!self::isLicenseValid($licenseKey)) {
			self::setLastKey($licenseKey);
			self::setValidLicense(FALSE);
			self::setLicenseCheckedInVersion(self::CURRENT_VERSION);
			self::setValidLicenseUntilTimestamp(0);
			self::setLastLicenseCheckTimestamp();
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
	 * @param string $licenseKey
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
	public static function getLastKey() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		return $registry->get(self::REGISTRY_NAMESPACE, self::LAST_LICENSE_KEY_CHECKED_KEY);
	}

	/**
	 * Sets if the license is valid in the registry
	 *
	 * @param bool $isValid
	 */
	protected static function setValidLicense($isValid) {
		$isValid = (bool) $isValid;
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::IS_KEY_VALID_KEY, $isValid);
	}

	/**
	 * Gets the isValid from the registry
	 *
	 * @return bool
	 */
	protected static function getValidLicense() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		return (bool) $registry->get(self::REGISTRY_NAMESPACE, self::IS_KEY_VALID_KEY);
	}

	/**
	 * Stores the last warning timestamp
	 *
	 * @param int $timestamp
	 */
	protected static function setLastWarningTimestamp($timestamp) {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::LAST_WARNING_TIMESTAMP_KEY, $timestamp);
	}

	/**
	 * Gets the last warning timestamp
	 *
	 * @return mixed|null
	 */
	protected static function getLastWarningTimestamp() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		return $registry->get(self::REGISTRY_NAMESPACE, self::LAST_WARNING_TIMESTAMP_KEY);
	}

	/**
	 * Stores the valid until timestamp in the registry
	 *
	 * @param mixed $validUntil
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
	 * @param string $version
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
	public static function getLastLicenseCheckTimestamp() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		return $registry->get(self::REGISTRY_NAMESPACE, self::LAST_CHECKED_TIMESTAMP_KEY);
	}

	/**
	 * Sets the timestamp of the last AJAX Notification check in the registry
	 */
	public static function setLastAjaxNotificationCheckTimestamp() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::LAST_AJAX_TIMESTAMP_KEY, $GLOBALS['EXEC_TIME']);
	}

	/**
	 * Gets the timestamp of the last AJAX Notification check from the registry
	 *
	 * @return mixed|null
	 */
	protected static function getLastAjaxNotificationCheckTimestamp() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		return $registry->get(self::REGISTRY_NAMESPACE, self::LAST_AJAX_TIMESTAMP_KEY);
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
		$registry->remove(self::REGISTRY_NAMESPACE, self::LAST_WARNING_TIMESTAMP_KEY);
		$registry->remove(self::REGISTRY_NAMESPACE, self::LAST_AJAX_TIMESTAMP_KEY);
	}

	/**
	 * Gets the validUntil date from this current check
	 *
	 * @return mixed
	 */
	public static function getValidUntil() {
		if (self::$validUntil === NULL) {
			self::$validUntil = self::getValidLicenseUntilTimestamp();
		}
		return self::$validUntil;
	}

	/**
	 * The timestamp of the key lifetime, if the given license key is valid, or -1 if invalid.
	 *
	 * @param string $licenseKey A license key, which should be validated.
	 * @return
	 */
	public static function isLicenseValid($licenseKey) {
		if (!self::checkLicenseKeyStructure($licenseKey)) {
			return FALSE;
		}

		$validUntil = self::getValidUntilTimestampByLicenseKey($licenseKey);
		return !self::isTimestampInvalid($validUntil);
	}

	/**
	 * Check if the given license key is valid.
	 *
	 * @param string $licenseKey A license key, which should be validated.
	 * @return boolean
	 */
	public static function checkLicenseKeyStructure($licenseKey) {
		// Structure: XXXXXX-XXXXXX-XXXXXX-XXXXXX | All upper case
		if (substr_count($licenseKey, '-') !== 3) {
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
			$requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
			$response = $requestFactory->request(
				self::API_URL, 'GET', [
					'auth' => [self::API_USER, self::API_PASSWORD],
					'timeout' => 1,
					'connect_timeout' => 1,
				]
			);

			self::$lastHttpResponseCode = (int) $response->getStatusCode();

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
	 * @param string $licenseKey
	 * @return int
	 */
	private static function getValidUntilTimestampByLicenseKey($licenseKey) {
		try {
			$url = self::API_URL . '/' . urldecode($licenseKey) . '?product='
				. self::PRODUCT_KEY;
			$requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
			$response = $requestFactory->request(
				$url, 'GET', [
					'auth' => [self::API_USER, self::API_PASSWORD],
					'timeout' => 1,
					'connect_timeout' => 1,
				]
			);

			self::$lastHttpResponseCode = (int) $response->getStatusCode();

			if (self::$lastHttpResponseCode !== 200 && self::$lastHttpResponseCode !== 201) {
				return self::ERROR_INVALID_RESPONSE_CODE;
			}

			if (!$response->getBody()) {
				return self::ERROR_INVALID_RESPONSE_DATA;
			}

			$jsonData = json_decode($response->getBody(), TRUE);
			if (!$jsonData['serial']['valid']) {
				return self::ERROR_INVALID_LICENSE_KEY;
			}

			return (int) $jsonData['serial']['validUntil'];
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
			$context = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext();
		}
		return $context->isDevelopment();
	}

	/**
	 * Checks if the current TYPO3 version is supported for the license check
	 *
	 * @return bool
	 */
	public static function isTYPO3VersionSupported() {
		$versionNumber = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
		return $versionNumber >= self::EARLIEST_SUPPORTED_VERSION;
	}

	/**
	 * Checks if the time for the next check has expired.
	 * error = 0 means no error
	 * error = 1 is an error
	 * error = 2 is a warning
	 *
	 * @return bool
	 */
	public static function isTimeForNextCheck() {
		return self::getLastAjaxNotificationCheckTimestamp()
			+ self::AMOUNT_OF_DAYS_UNTIL_NEXT_CHECK * 24 * 60 * 60 < $GLOBALS['EXEC_TIME'];
	}

	/**
	 * Performs the license check and returns the output data to the frontend
	 *
	 * @param bool $isAjaxCheck
	 * @return array
	 */
	public static function getLicenseCheckResponseData($isAjaxCheck = false) {
		// if the key is empty - error
		if (!self::getLicenseKey()) {
			return [
				'error' => 1,
				'title' => LocalizationUtility::translate('backend.licenceCheck.error.title', 'sg_cookie_optin'),
				'message' => LocalizationUtility::translate(
					'backend.licenceCheck.noLicenseKey', 'sg_cookie_optin', [
						LocalizationUtility::translate('backend.licenceCheck.shopLink', 'sg_cookie_optin')
					]
				)
			];
		}

		// if not valid - error
		if (!self::hasValidLicense()) {
			return [
				'error' => 1,
				'title' => LocalizationUtility::translate('backend.licenceCheck.error.title', 'sg_cookie_optin'),
				'message' => LocalizationUtility::translate(
					'backend.licenceCheck.expiredError.message', 'sg_cookie_optin', [
						LocalizationUtility::translate('backend.licenceCheck.shopLink', 'sg_cookie_optin')
					]
				)
			];
		}

		// if it's valid - check validUntil and throw a warning if the license has expired but you are still
		// on the valid version
		if (self::getValidUntil() < $GLOBALS['EXEC_TIME']) {
			$date = date('d.m.Y', self::getValidUntil());

			if ($isAjaxCheck) {
				$lastWarningTimestamp = (int) self::getLastWarningTimestamp(
				); // relevant only for the AJAX notifications
			}

			if (!$isAjaxCheck || ($lastWarningTimestamp + self::AMOUNT_OF_DAYS_UNTIL_WARNING * 24 * 60 * 60 < $GLOBALS['EXEC_TIME'])) {

				if ($isAjaxCheck) {
					self::setLastWarningTimestamp($GLOBALS['EXEC_TIME']);
				}

				return [
					'error' => 2,
					'title' => LocalizationUtility::translate('backend.licenceCheck.warning.title', 'sg_cookie_optin'),
					'message' => LocalizationUtility::translate(
						'backend.licenceCheck.expiringWarning.message', 'sg_cookie_optin', [
							$date, LocalizationUtility::translate('backend.licenceCheck.shopLink', 'sg_cookie_optin')
						]
					)
				];
			}
		}

		/** @noinspection SuspiciousAssignmentsInspection */
		$date = date('d.m.Y', self::getValidUntil());
		// 19.01.2038 == lifetime license
		if ($date === '19.01.2038') {
			$date = LocalizationUtility::translate(
				'backend.licenceCheck.status.lifetime', 'sg_cookie_optin'
			);
		}

		return [
			'error' => 0,
			'title' => LocalizationUtility::translate('backend.licenceCheck.status.title', 'sg_cookie_optin'),
			'message' => LocalizationUtility::translate(
				'backend.licenceCheck.status.okMessage', 'sg_cookie_optin', [
					$date
				]
			)
		];
	}

	/**
	 * Returns one of the state constants of this class.
	 *
	 * @return boolean
	 */
	public static function checkKey() {
		$key = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_LICENSE);
		if (empty($key)) {
			return self::STATE_LICENSE_NOT_SET;
		}

		if ((bool) preg_match('/^([A-Z\d]{6}-?){4}$/', $key)) {
			return self::STATE_LICENSE_VALID;
		}

		return self::STATE_LICENSE_INVALID;
	}

	/**
	 * Checks if this instance is in the demo mode.
	 *
	 * @return bool
	 */
	public static function isInDemoMode() {
		$demoData = self::getDemoModeData();
		if (!$demoData) {
			return FALSE;
		}

		if (self::getRemainingTimeInDemoMode() <= 0) {
			return FALSE;
		}

		if ((int) $demoData['amount'] > self::DEMO_MODE_MAX_AMOUNT) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Returns the remaining time in seconds for the demo mode.
	 *
	 * @return int
	 */
	public static function getRemainingTimeInDemoMode() {
		$demoData = self::getDemoModeData();
		if (!$demoData) {
			return 0;
		}

		return $demoData['lastActivation'] + self::DEMO_MODE_LIFETIME - $GLOBALS['EXEC_TIME'];
	}

	/**
	 * Activates the demo mode for this instance.
	 *
	 * @return void
	 */
	public static function activateDemoMode() {
		$amount = 1;
		$demoData = self::getDemoModeData();
		if ($demoData && isset($demoData['amount'])) {
			$amount += (int) $demoData['amount'];
		}

		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::DEMO_MODE_KEY, [
			'lastActivation' => $GLOBALS['EXEC_TIME'],
			'amount' => $amount,
		]);
	}

	/**
	 * Returns true, if this instance can use the demo mode.
	 *
	 * @return bool
	 */
	public static function isDemoModeAcceptable() {
		$demoData = self::getDemoModeData();
		if (!$demoData) {
			return TRUE;
		}

		return (int) $demoData['amount'] < self::DEMO_MODE_MAX_AMOUNT;
	}

	/**
	 * Removes all files within the specific generated file folder folder.
	 *
	 * @return void
	 */
	public static function removeAllCookieOptInFiles() {
		$folder = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_FOLDER);
		if (!$folder) {
			return;
		}

		$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . '/';
		GeneralUtility::rmdir($sitePath . $folder, TRUE);
	}

	/**
	 * Returns the demo mode data, or an empty FALSE on error.
	 *
	 * @return array|FALSE
	 */
	protected static function getDemoModeData() {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$demoData = $registry->get(self::REGISTRY_NAMESPACE, self::DEMO_MODE_KEY);
		if (!isset($demoData['lastActivation'], $demoData['amount'])) {
			return FALSE;
		}

		return $demoData;
	}
}
