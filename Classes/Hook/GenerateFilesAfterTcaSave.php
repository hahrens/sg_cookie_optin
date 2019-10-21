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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Adds the Cookie Optin JavaScript if it's generated for the current page.
 */
class GenerateFilesAfterTcaSave {
	const TABLE_NAME = 'tx_sgcookieoptin_domain_model_optin';

	const FOLDER = 'fileadmin/sg_cookie_optin/siteroot-#PID#/';

	const TEMPLATE_JAVA_SCRIPT_PATH = 'typo3conf/ext/sg_cookie_optin/Resources/Public/JavaScript/';
	const TEMPLATE_JAVA_SCRIPT_NAME = 'cookieOptin.js';
	const TEMPLATE_JAVA_SCRIPT_NEW_NAME = 'cookieOptin_#LANG#.js';

	const TEMPLATE_STYLE_SHEET_PATH = 'typo3conf/ext/sg_cookie_optin/Resources/Public/StyleSheets/';
	const TEMPLATE_STYLE_SHEET_NAME = 'cookieOptin.css';

	/**
	 * Generates the files out of the TCA data.
	 *
	 * @param DataHandler $dataHandler
	 * @return void
	 */
	public function processDatamap_afterAllOperations(DataHandler $dataHandler) {
		if (!isset($dataHandler->datamap[self::TABLE_NAME])) {
			return;
		}

		$fullData = [];
		foreach ($dataHandler->datamap[self::TABLE_NAME] as $uid => $data) {
			if (count($fullData) > 0) {
				break;
			}

			if (strpos($uid, 'NEW') === 0) {
				if (!isset($dataHandler->substNEWwithIDs[$uid])) {
					continue;
				}

				$uid = (int) $dataHandler->substNEWwithIDs[$uid];
			}

			$data['uid'] = $uid;
			$fullData = $this->getFullData($data, self::TABLE_NAME, $uid);
		}

		if (count($fullData) <= 0) {
			return;
		}
		
		$siteRoot = (int) $dataHandler->getPID(self::TABLE_NAME, $fullData['uid']);
		if ($siteRoot <= 0) {
			return;
		}

		$folderName = str_replace('#PID#', $siteRoot, self::FOLDER);
		GeneralUtility::mkdir_deep(PATH_site . $folderName);

		$this->createCSSFile($folderName, $fullData);
		$this->createJavaScriptFile($folderName, $fullData);
		$this->createScriptFile($folderName, 'essential', $fullData['essential_scripts']);
		foreach ($fullData['groups'] as $group) {
			$this->createScriptFile($folderName, $group['group_name'], $group['scripts']);
		}
	}

	/**
	 * Creates a CSS file out of the given data array.
	 *
	 * @param string $folder
	 * @param array $data
	 * @return void
	 */
	protected function createJavaScriptFile($folder, array $data) {
		$content = file_get_contents(PATH_site . self::TEMPLATE_JAVA_SCRIPT_PATH . self::TEMPLATE_JAVA_SCRIPT_NAME);

		$cookieGroups = [
			'essential' => [
				'label' => $data['essential_title'],
				'description' => $data['essential_description'],
				'required' => TRUE,
				'loadingJavaScript' => '/' . $folder . 'essential.js',
				'cookieData' => [],
			],
		];
		
		foreach ($data['essential_cookies'] as $cookieData) {
			$cookieGroups['essential']['cookieData'][] = [
				'provider' => $cookieData['provider'],
				'purpose' => $cookieData['purpose'],
				'lifetime' => $cookieData['lifetime'],
			];
		}

		foreach ($data['groups'] as $group) {
			$groupName = $group['group_name'];
			$cookieGroups[$groupName] = [
				'label' => $group['title'],
				'description' => $group['description'],
				'required' => FALSE,
				'loadingJavaScript' => '/' . $folder . $groupName . '.js',
				'cookieData' => [],
			];

			foreach ($group['cookies'] as $cookieData) {
				$cookieGroups[$groupName]['cookieData'][] = [
					'provider' => $cookieData['provider'],
					'purpose' => $cookieData['purpose'],
					'lifetime' => $cookieData['lifetime'],
				];
			}
		}

		$footerLinks = [];
		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$uriBuilder = $objectManager->get(UriBuilder::class);
		$contentObject = $objectManager->get(ContentObjectRenderer::class);
		foreach ($this->getPagesFromNavigation($data['navigation']) as $pageData) {
			$footerLinks[] = [
				'url' => $uriBuilder->reset()
					->setCreateAbsoluteUri(FALSE)
					->setTargetPageUid($pageData['uid'])
					->buildFrontendUri(),
				'name' => $contentObject->crop($pageData['title'], 15 . '|...|0'),
			];
		}

		$textEntries = [
			'header' => $data['header'],
			'description' => $data['description'],
			'accept_all_text' => $data['accept_all_text'],
			'accept_specific_text' => $data['accept_specific_text'],
			'accept_essential_text' => $data['accept_essential_text'],
			'extend_box_link_text' => $data['extend_box_link_text'],
			'extend_table_link_text' => $data['extend_table_link_text'],
		];
		$content = str_replace([
			'###COOKIE_GROUPS###',
			'###FOOTER_LINKS###',
			'###TEXT_ENTRIES###',
		], [
			json_encode($cookieGroups),
			json_encode($footerLinks),
			json_encode($textEntries)
		], $content);
		file_put_contents(
			PATH_site . $folder . str_replace('#LANG#', $data['sys_language_uid'], self::TEMPLATE_JAVA_SCRIPT_NEW_NAME),
			$content
		);
	}

	/**
	 * Creates a CSS file out of the given data array.
	 *
	 * @param string $folder
	 * @param array $data
	 * @return void
	 */
	protected function createCSSFile($folder, array $data) {
		$content = file_get_contents(PATH_site . self::TEMPLATE_STYLE_SHEET_PATH . self::TEMPLATE_STYLE_SHEET_NAME);
		$content = str_replace([
			'###COLOR_BOX###',
			'###COLOR_TEXT###',
			'###COLOR_CHECKBOX###',
			'###COLOR_CHECKBOX_REQUIRED###',
			'###COLOR_BUTTON_ALL###',
			'###COLOR_BUTTON_ALL_HOVER###',
			'###COLOR_BUTTON_ALL_TEXT###',
			'###COLOR_BUTTON_SPECIFIC###',
			'###COLOR_BUTTON_SPECIFIC_HOVER###',
			'###COLOR_BUTTON_SPECIFIC_TEXT###',
			'###COLOR_BUTTON_ESSENTIAL###',
			'###COLOR_BUTTON_ESSENTIAL_HOVER###',
			'###COLOR_BUTTON_ESSENTIAL_TEXT###',
			'###COLOR_LIST###',
			'###COLOR_LIST_TEXT###',
			'###COLOR_TABLE###',
			'###COLOR_TABLE_HEADER_TEXT###',
			'###COLOR_TABLE_DATA_TEXT###',
		], [
			$data['color_box'],
			$data['color_text'],
			$data['color_checkbox'],
			$data['color_checkbox_required'],
			$data['color_button_all'],
			$data['color_button_all_hover'],
			$data['color_button_all_text'],
			$data['color_button_specific'],
			$data['color_button_specific_hover'],
			$data['color_button_specific_text'],
			$data['color_button_essential'],
			$data['color_button_essential_hover'],
			$data['color_button_essential_text'],
			$data['color_list'],
			$data['color_list_text'],
			$data['color_table'],
			$data['color_table_header_text'],
			$data['color_Table_data_text'],
		], $content);
		file_put_contents(PATH_site . $folder . self::TEMPLATE_STYLE_SHEET_NAME, $content);
	}

	/**
	 * Creates a javascript file out of the given scripts array.
	 *
	 * @param string $folder
	 * @param string $groupName
	 * @param array $scripts
	 * @return void
	 */
	protected function createScriptFile($folder, $groupName, array $scripts) {
		$content = '';
		foreach ($scripts as $script) {
			$scriptContent = trim($script['script']);
			if (!$scriptContent) {
				continue;
			}

			$content .= '//Script: ' . $script['title'] . "\n\n" . $scriptContent . "\n\n";
		}

		file_put_contents(PATH_site . $folder . $groupName . '.js', $content);
	}

	/**
	 * Returns an array with page data out of the given data string.
	 *
	 * @param string $navigationData
	 * @return array
	 */
	protected function getPagesFromNavigation($navigationData) {
		if (!$navigationData) {
			return [];
		}

		$records= [];
		$navigationEntries = explode(',', $navigationData);
		foreach ($navigationEntries as $navigationEntry) {
			if (!$navigationEntry) {
				continue;
			}

			$navigationEntryData = explode('_', $navigationEntry);
			if (count($navigationEntryData) !== 2) {
				continue;
			}

			$record = BackendUtility::getRecord($navigationEntryData[0], $navigationEntryData[1]);
			if (!$record) {
				continue;
			}

			$records[] = $record;
		}

		return $records;
	}

	/**
	 * Returns the full data for the given data array.
	 *
	 * @param array $data
	 * @param int $parentUid
	 *
	 * @return array
	 */
	protected function getFullData(array $data, $table, $parentUid) {
		$fullData = [];
		foreach ($data as $fieldName => $value) {
			$tcaConfig = $this->getTCAConfigForInlineField($table, $fieldName);
			if (count($tcaConfig) <= 0) {
				$fullData[$fieldName] = $value;
				continue;
			}

			$tcaConfig = $this->getTCAConfigForInlineField($table, $fieldName);
			$foreignTable = $tcaConfig['foreign_table'];
			$foreignField = $tcaConfig['foreign_field'];
			if (empty($foreignTable) || empty($foreignField)) {
				$fullData[$fieldName] = [];
				continue;
			}

			$fullData[$fieldName] = [];
			$inlineData = $this->getDataForInlineField($foreignTable, $foreignField, $parentUid);
			if (\count($inlineData) > 0) {
				foreach ($inlineData as $index => $inlineDataEntry) {
					if (!isset($inlineDataEntry['uid'])) {
						continue;
					}

					$fullData[$fieldName][$index] = $this->getFullData(
						$inlineDataEntry, $foreignTable, (int) $inlineDataEntry['uid']
					);
				}
			}
		}

		return $fullData;
	}

	/**
	 * Returns the data for the given field, table configuration.
	 *
	 * @param string $table
	 * @param string $field
	 * @param int $parentUid
	 * @param int $language
	 *
	 * @return array
	 */
	protected function getDataForInlineField($table, $field, $parentUid, $language = 0) {
		if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) <= 9000000) {
			/** @var DatabaseConnection $database */
			$database = $GLOBALS['TYPO3_DB'];
			$rows = $database->exec_SELECTgetRows(
				'*', $table, 'deleted=0 AND ' . $field . '=' . $parentUid . ' AND sys_language_uid=' . $language
			);
		} else {
			$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
			$queryBuilder = $connectionPool->getQueryBuilderForTable($table);
			$queryBuilder->getRestrictions()
				->removeAll()
				->add(GeneralUtility::makeInstance(DeletedRestriction::class));
			$queryBuilder->select('*')
				->from($table)
				->where(
					$queryBuilder->expr()->eq(
						$field,
						$parentUid
					),
					$queryBuilder->expr()->eq(
						'sys_language_uid',
						$language
					)
				);

			$rows = $queryBuilder->execute()->fetchAll();
		}

		if (!is_array($rows)) {
			return [];
		}

		$translatedRows = [];
		$pageRepository = GeneralUtility::makeInstance(PageRepository::class);
		foreach ($rows as $row) {
			$translatedRows[] = $row;
		}

		return $translatedRows;
	}

	/**
	 * Returns the table of the given inline field from the given table.
	 *
	 * @param string $table
	 * @param string $field
	 *
	 * @return array
	 */
	protected function getTCAConfigForInlineField($table, $field) {
		$tableData = $GLOBALS['TCA'][$table];
		if (!is_array($tableData)) {
			return [];
		}

		$tableColumn = $tableData['columns'][$field];
		if (!is_array($tableColumn)) {
			return [];
		}

		if (!isset($tableColumn['config'])) {
			return [];
		}

		if ($tableColumn['config']['type'] !== 'inline') {
			return [];
		}

		return $tableColumn['config'];
	}
}
