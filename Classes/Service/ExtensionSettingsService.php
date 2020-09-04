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

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class SGalinski\SgCookieOptin\Service\ExtensionSettingsService
 */
class ExtensionSettingsService {
	const SETTING_LICENSE = 'key';
	const SETTING_FOLDER = 'folder';
	const SETTING_HIDE_MODULE_IN_PRODUCTION_CONTEXT = 'hideModuleInProductionContext';

	/**
	 * @var array Default settings mapped to constants.
	 */
	protected static $defaultValueMap = [
		self::SETTING_FOLDER => 'fileadmin/sg_cookie_optin/',
		self::SETTING_HIDE_MODULE_IN_PRODUCTION_CONTEXT => FALSE,
	];

	/**
	 * Returns the setting of one of the constants of this class.
	 *
	 * @param string $settingKey
	 * @return mixed
	 */
	public static function getSetting($settingKey) {
		if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 9000000) {
			// the "options" parameter of unserialize exists since PHP 7.0.0
			if (version_compare(phpversion(), '7.0.0', '>=')) {
				$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sg_cookie_optin'], [FALSE]);
			} else {
				$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sg_cookie_optin']);
			}
		} else {
			$configuration = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sg_cookie_optin'];
		}

		$setting = '';
		if (isset(self::$defaultValueMap[$settingKey])) {
			$setting = self::$defaultValueMap[$settingKey];
		}

		if (isset($configuration[$settingKey])) {
			$setting = self::postProcessSetting($configuration[$settingKey], $settingKey);
		}

		return $setting;
	}

	/**
	 * Post process of the given setting, by the given setting key.
	 *
	 * @param mixed $value
	 * @param string $settingKey
	 * @return mixed
	 */
	protected static function postProcessSetting($value, $settingKey) {
		if ($settingKey === self::SETTING_FOLDER) {
			$value = trim($value, " \t\n\r\0\x0B\/") . '/';

			if (strpos($value, 'EXT:') === 0) {
				$value = 'typo3conf/ext/' . substr($value, 4);
			}
		}

		// TYPO3 6 stores all settings as strings, some are expected to be booleans, though.
		$value = ($value === 'FALSE') ? FALSE : $value;

		return $value;
	}
}
