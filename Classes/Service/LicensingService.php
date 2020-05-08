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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Registry;

/**
 * Class SGalinski\SgCookieOptin\Service\LicensingService
 */
class LicensingService {
	const STATE_LICENSE_VALID = 2;
	const STATE_LICENSE_INVALID = 1;
	const STATE_LICENSE_NOT_SET = 0;

	const DEMO_MODE_NAMESPACE = 'tx_sgcookieoptin';
	const DEMO_MODE_KEY = 'demo_mode';
	const DEMO_MODE_LIFETIME = 86400;
	const DEMO_MODE_MAX_AMOUNT = 3;

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
		$registry->set(self::DEMO_MODE_NAMESPACE, self::DEMO_MODE_KEY, [
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
		$demoData = $registry->get(self::DEMO_MODE_NAMESPACE, self::DEMO_MODE_KEY);
		if (!isset($demoData['lastActivation'], $demoData['amount'])) {
			return FALSE;
		}

		return $demoData;
	}
}
