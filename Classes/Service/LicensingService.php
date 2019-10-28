<?php

namespace SGalinski\SgCookieOptin\Service;

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

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
 * Class SGalinski\SgRoutes\Service\LicensingService
 */
class LicensingService {
	const STATE_LICENSE_VALID = 2;
	const STATE_LICENSE_INVALID = 1;
	const STATE_LICENSE_NOT_SET = 0;

	/**
	 * Returns one of the state constants of this class.
	 *
	 * @return boolean
	 */
	public static function checkKey() {
		if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 9000000) {
			$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sg_cookie_optin'], [FALSE]);
		} else {
			$configuration = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sg_cookie_optin'];
		}

		if (!isset($configuration['key'])) {
			return self::STATE_LICENSE_NOT_SET;
		}

		$key = trim($configuration['key']);
		if (empty($key)) {
			return self::STATE_LICENSE_NOT_SET;
		}

		if ((bool) preg_match('/^([A-Z\d]{6}-?){4}$/', $key)) {
			return self::STATE_LICENSE_VALID;
		}

		return self::STATE_LICENSE_INVALID;
	}
}
