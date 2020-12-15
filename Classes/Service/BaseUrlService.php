<?php
namespace SGalinski\SgCookieOptin\Service;

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class BaseUrlService {
	/** @var SiteFinder|null */
	private static $siteFinder;

	/**
	 * Gets the base Url for this site root
	 *
	 * @param $rootPid
	 * @return string
	 */
	public static function getSiteBaseUrl($rootPid) {
		if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) <= 9000000) {
			return '/';
		}

		if (self::$siteFinder === null) {
			self::$siteFinder = GeneralUtility::makeInstance( SiteFinder::class );
		}

		try {
			$site     = self::$siteFinder->getSiteByPageId( $rootPid );
			$basePath = (string) $site->getBase();
		} catch(SiteNotFoundException $e) {
			$basePath = '/';
		}

		if ($basePath[strlen($basePath) - 1] !== '/') {
			$basePath .= '/';
		}

		return $basePath;
	}
}
