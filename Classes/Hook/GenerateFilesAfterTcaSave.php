<?php

namespace SGalinski\SgCookieOptin\Hook;

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

use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use SGalinski\SgCookieOptin\Service\StaticFileGenerationService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Adds the Cookie Optin JavaScript if it's generated for the current page.
 */
class GenerateFilesAfterTcaSave {

	/**
	 * Generates the files out of the TCA data.
	 *
	 * @param DataHandler $dataHandler
	 *
	 * @return void
	 * @throws \TYPO3\CMS\Core\Error\Http\PageNotFoundException
	 * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
	 * @throws \TYPO3\CMS\Core\Http\ImmediateResponseException
	 * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
	 */
	public function processDatamap_afterAllOperations(DataHandler $dataHandler) {
		$this->handleFlashMessage($dataHandler);

		if (!isset($dataHandler->datamap[StaticFileGenerationService::TABLE_NAME])) {
			return;
		}

		if (!LicenceCheckService::hasValidLicense()
			&& !LicenceCheckService::isInDemoMode()
		) {
			return;
		}

		$originalRecord = [];
		foreach ($dataHandler->datamap[StaticFileGenerationService::TABLE_NAME] as $uid => $data) {
			if (count($originalRecord) > 0) {
				break;
			}

			if (strpos($uid, 'NEW') === 0) {
				if (!isset($dataHandler->substNEWwithIDs[$uid])) {
					continue;
				}

				$uid = (int) $dataHandler->substNEWwithIDs[$uid];
			}

			$uid = (isset($data['l10n_parent']) ? (int) $data['l10n_parent'] : $uid);
			if ($uid <= 0) {
				continue;
			}

			$originalRecord = BackendUtility::getRecord(StaticFileGenerationService::TABLE_NAME, $uid);
			if (isset($originalRecord['l10n_parent']) && (int) $originalRecord['l10n_parent'] > 0) {
				$originalRecord = BackendUtility::getRecord(
					StaticFileGenerationService::TABLE_NAME, (int) $originalRecord['l10n_parent']
				);
			}
		}

		$this->siteRoot = (int) $dataHandler->getPID(StaticFileGenerationService::TABLE_NAME, $originalRecord['uid']);
		if ($this->siteRoot <= 0) {
			return;
		}

		$service = GeneralUtility::makeInstance(StaticFileGenerationService::class);
		$service->generateFiles($this->siteRoot, $originalRecord);
	}

	/**
	 * Checks if we edited/deleted something and saves data in the session for the controller to make a flash message
	 *
	 * @param DataHandler $dataHandler
	 */
	protected function handleFlashMessage(DataHandler $dataHandler) {
		if (isset($dataHandler->cmdmap[StaticFileGenerationService::TABLE_NAME]) || isset($dataHandler->datamap[StaticFileGenerationService::TABLE_NAME])) {
			session_start();
			$_SESSION['tx_sgcookieoptin']['configurationChanged'] = TRUE;
		}
	}
}
