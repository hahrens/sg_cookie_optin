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

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class SGalinski\SgCookieOptin\Service\LanguageService
 */
class LanguageService {

	/**
	 * Returns all system languages.
	 *
	 * @param int $siteRootUid
	 * @return array
	 * @throws SiteNotFoundException
	 */
	public static function getLanguages($siteRootUid) {
		if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 9000000) {
			/** @var DatabaseConnection $database */
			$database = $GLOBALS['TYPO3_DB'];
			$rows = $database->exec_SELECTgetRows('uid', 'sys_language', '');

			// Add the default language because it's not in the table
			if (is_array($rows)) {
				$rows[] = [
					'uid' => 0,
				];
			} else {
				$rows = [[
					'uid' => 0,
				]];
			}
		} else {
			$site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($siteRootUid);
			$rows = [];
			foreach ($site->getAllLanguages() as $siteLanguage) {
				$rows[] = [
					'uid' => $siteLanguage->getLanguageId(),
					'locale' => $siteLanguage->getLocale(),
					'title' => $siteLanguage->getTitle(),
					'flagIdentifier' => $siteLanguage->getFlagIdentifier(),
				];
			}
		}

		return $rows;
	}

	/**
	 * Returns the languageUid and Locale by fileName
	 *
	 * @param string $fileName
	 * @return array
	 */
	public static function getLocaleByFileName($fileName) {
		$parts = explode(JsonImportService::LOCALE_SEPARATOR, $fileName);
		$parts = array_reverse($parts);
		return $parts[1];
	}

	/**
	 * Returns the language UID by the locale string or NULL if it was not found
	 *
	 * @param string $locale
	 * @param array $languages
	 * @return int|null
	 */
	public static function getLanguageIdByLocale($locale, array $languages) {
		foreach ($languages as $language) {
			if (strpos($language['locale'], $locale) !== FALSE) {
				return (int) $language['uid'];
			}
		}
		return NULL;
	}
}
