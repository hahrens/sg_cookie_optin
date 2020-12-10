<?php
/**
 *
 * Copyright notice
 *
 * (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace SGalinski\SgCookieOptin\Hook;

use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use TYPO3\CMS\Backend\Controller\BackendController;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class BackendControllerHook
 *
 * @package SGalinski\ProjectBase\Hook
 * @author Kevin Ditscheid <kevin.ditscheid@sgalinski.de>
 */
class LicenceCheckHook {
	/**
	 * Add JavaScript to display the expiring license warning
	 *
	 * @param string $date
	 */
	protected function addExpiringWarningJavaScript($date) {
		// build text
		$warningText = LocalizationUtility::translate(
			'backend.licenceCheck.expiringWarning.message', 'sg_cookie_optin', [
				// ToDo inject HTML somehow
				$date, LocalizationUtility::translate('backend.licenceCheck.shopLink', 'sg_cookie_optin')
			]
		);
		$warningTitle = LocalizationUtility::translate('backend.licenceCheck.expiringWarning.title', 'sg_cookie_optin');
		// check
		$evalText = 'Notification.warning("' . $warningTitle . '", "' . $warningText . '");';
		$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		$pageRenderer->addJsInlineCode(
			'LicenseCheckNotification',
			"if (typeof SgCookieOptinLicenseCheck !== 'Object') {
				SgCookieOptinLicenseCheck = {};
			}
			SgCookieOptinLicenseCheck.licenseWarning = {
				evalScript: '$evalText'
			}"
		);
		$pageRenderer->loadRequireJsModule('TYPO3/CMS/SgCookieOptin/Backend/LicenseNotification');
	}

	/**
	 * Add JavaScript to display the expired license error
	 */
	protected function addExpiredErrorJavaScript() {
		// build text
		$warningText = LocalizationUtility::translate(
			'backend.licenceCheck.expiredError.message', 'sg_cookie_optin', [
				LocalizationUtility::translate('backend.licenceCheck.shopLink', 'sg_cookie_optin')
			]
		);
		$warningTitle = LocalizationUtility::translate('backend.licenceCheck.expiredError.title', 'sg_cookie_optin');
		// check
		$evalText = 'Notification.error("' . $warningTitle . '", "' . $warningText . '");';
		$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		$pageRenderer->addJsInlineCode(
			'LicenseCheckNotification',
			"if (typeof SgCookieOptinLicenseCheck !== 'Object') {
					SgCookieOptinLicenseCheck = {};
				}
				SgCookieOptinLicenseCheck.licenseWarning = {
					evalScript: '$evalText'
				}"
		);
		$pageRenderer->loadRequireJsModule('TYPO3/CMS/SgCookieOptin/Backend/LicenseNotification');
	}

	/**
	 * Checks if the license key is OK
	 *
	 * @param array $configuration
	 * @param BackendController $parentBackendController
	 */
	public function performLicenseCheck(array $configuration, BackendController $parentBackendController) {
		// has it been checked already this session
		session_start();
		if (isset($_SESSION[LicenceCheckService::REGISTRY_NAMESPACE]['keyChecked'])
			|| LicenceCheckService::isInDevelopmentContext()
		) {
			return;
		}

		// if not valid - error
		if (!LicenceCheckService::hasValidLicense()) {
			$this->addExpiredErrorJavaScript();
		} else {
			// if it's valid - check validUntil and throw a warning
			if (LicenceCheckService::getValidUntil() < $GLOBALS['EXEC_TIME']
				+ LicenceCheckService::AMOUNT_OF_DAYS_UNTIL_WARNING * 24 * 60 * 60) {
				$date = date('d.m.Y', LicenceCheckService::getValidUntil());
				$this->addExpiringWarningJavaScript($date);
			}
		}

		$_SESSION[LicenceCheckService::REGISTRY_NAMESPACE] = array(
			'keyChecked' => TRUE
		);

	}
}
