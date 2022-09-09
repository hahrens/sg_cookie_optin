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

use SGalinski\SgCookieOptin\Service\TemplateService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Handles the template related changes in the TCA.
 */
class HandleTemplateAfterTcaSave {
	const TABLE_NAME = 'tx_sgcookieoptin_domain_model_optin';

	/**
	 * Hook method for updating the template field in the optin TCA
	 *
	 * @param string $status
	 * @param string $table
	 * @param int $id
	 * @param array $fieldArray
	 * @param DataHandler $dataHandler
	 * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientFolderWritePermissionsException
	 * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
	 * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
	 * @throws \TYPO3\CMS\Core\Exception
	 * @throws \InvalidArgumentException
	 */
	public function processDatamap_afterDatabaseOperations(
		$status,
		$table,
		$id,
		array $fieldArray,
		DataHandler $dataHandler
	) {
		if (
			($status !== 'update' && $status !== 'new') || $table !== self::TABLE_NAME ||
			!isset($dataHandler->datamap[self::TABLE_NAME])
		) {
			return;
		}

		// If it's a new object - get it's real ID otherwise the update will not work anyway
		if (strpos($id, 'NEW') === 0) {
			if (!isset($dataHandler->substNEWwithIDs[$id])) {
				return;
			}

			$id = (int) $dataHandler->substNEWwithIDs[$id];
		}

		$templateService = GeneralUtility::makeInstance(TemplateService::class);
		foreach ($dataHandler->datamap[self::TABLE_NAME] as $data) {
			if (!isset($data['template_html'], $data['banner_html'], $data['iframe_html'])) {
				continue;
			}

			if (isset($data['template_overwritten']) && $data['template_overwritten']) {
				$template = $data['template_html'];
			} else {
				if (!isset($data['template_selection'])) {
					$data['template_selection'] = 0;
				}

				$template = $templateService->getMustacheContent(
					TemplateService::TYPE_TEMPLATE,
					(int) $data['template_selection']
				);
			}

			if (isset($data['banner_overwritten']) && $data['banner_overwritten']) {
				$bannerTemplate = $data['banner_html'];
			} else {
				if (!isset($data['banner_selection'])) {
					$data['banner_selection'] = 0;
				}

				$bannerTemplate = $templateService->getMustacheContent(
					TemplateService::TYPE_BANNER,
					(int) $data['banner_selection']
				);
			}

			if (isset($data['iframe_overwritten']) && $data['iframe_overwritten']) {
				$iframeTemplate = $data['iframe_html'];
			} else {
				if (!isset($data['iframe_selection'])) {
					$data['iframe_selection'] = 0;
				}

				$iframeTemplate = $templateService->getMustacheContent(
					TemplateService::TYPE_IFRAME,
					(int) $data['iframe_selection']
				);
			}

			if (isset($data['iframe_replacement_overwritten']) && $data['iframe_replacement_overwritten']) {
				$iframeReplacementTemplate = $data['iframe_replacement_html'];
			} else {
				if (!isset($data['iframe_replacement_selection'])) {
					$data['iframe_replacement_selection'] = 0;
				}

				$iframeReplacementTemplate = $templateService->getMustacheContent(
					TemplateService::TYPE_IFRAME_REPLACEMENT,
					(int) $data['iframe_replacement_selection']
				);
			}

			if (isset($data['iframe_whitelist_overwritten']) && $data['iframe_whitelist_overwritten']) {
				$iframeWhitelistTemplate = $data['iframe_whitelist_regex'];
			} else {
				if (!isset($data['iframe_whitelist_selection'])) {
					$data['iframe_whitelist_selection'] = 0;
				}

				$iframeWhitelistTemplate = $templateService->getMustacheContent(
					TemplateService::TYPE_IFRAME_WHITELIST,
					(int) $data['iframe_whitelist_selection']
				);
			}

			if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) <= 9000000) {
				/** @var DatabaseConnection $database */
				$database = $GLOBALS['TYPO3_DB'];
				$database->exec_UPDATEquery(self::TABLE_NAME, 'uid=' . (int) $id, [
					'template_html' => $template,
					'banner_html' => $bannerTemplate,
					'iframe_html' => $iframeTemplate,
					'iframe_replacement_html' => $iframeReplacementTemplate,
					'iframe_whitelist_regex' => $iframeWhitelistTemplate,
				]);
			} else {
				$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
				$queryBuilder = $connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
				$queryBuilder
					->update(self::TABLE_NAME)
					->set('template_html', $template)
					->set('banner_html', $bannerTemplate)
					->set('iframe_html', $iframeTemplate)
					->set('iframe_replacement_html', $iframeReplacementTemplate)
					->set('iframe_whitelist_regex', $iframeWhitelistTemplate)
					->where(
						$queryBuilder->expr()->eq(
							'uid',
							$queryBuilder->createNamedParameter((int) $id, \PDO::PARAM_INT)
						)
					)->execute();
			}
		}
	}
}
