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

use SGalinski\SgCookieOptin\Service\LicensingService;
use SGalinski\SgCookieOptin\Service\MinificationService;
use SGalinski\SgCookieOptin\Service\TemplateService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageGenerator;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Adds the Cookie Optin JavaScript if it's generated for the current page.
 */
class GenerateFilesAfterTcaSave {
	const TABLE_NAME = 'tx_sgcookieoptin_domain_model_optin';

	const FOLDER_FILEADMIN = 'fileadmin/sg_cookie_optin/';
	const FOLDER_SITEROOT = 'siteroot-#PID#/';

	const TEMPLATE_JAVA_SCRIPT_PATH = 'typo3conf/ext/sg_cookie_optin/Resources/Public/JavaScript/';
	const TEMPLATE_JAVA_SCRIPT_NAME = 'cookieOptin.js';
	const TEMPLATE_JAVA_SCRIPT_NEW_NAME = 'cookieOptin_#LANG#_v2.js';
	const TEMPLATE_JAVA_SCRIPT_V1_NEW_NAME = 'cookieOptin_#LANG#.js';

	const TEMPLATE_STYLE_SHEET_PATH = 'typo3conf/ext/sg_cookie_optin/Resources/Public/StyleSheets/';
	const TEMPLATE_STYLE_SHEET_NAME = 'cookieOptin.css';

	/**
	 * Generates the files out of the TCA data.
	 *
	 * @param DataHandler $dataHandler
	 * @return void
	 * @throws \TYPO3\CMS\Core\Error\Http\PageNotFoundException
	 * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
	 * @throws \TYPO3\CMS\Core\Http\ImmediateResponseException
	 */
	public function processDatamap_afterAllOperations(DataHandler $dataHandler) {
		if (!isset($dataHandler->datamap[self::TABLE_NAME])) {
			return;
		}

		if (LicensingService::checkKey() !== LicensingService::STATE_LICENSE_VALID
			&& !LicensingService::isInDemoMode()
		) {
			return;
		}

		$originalRecord = [];
		foreach ($dataHandler->datamap[self::TABLE_NAME] as $uid => $data) {
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

			$originalRecord = BackendUtility::getRecord(self::TABLE_NAME, $uid);
			if (isset($originalRecord['l10n_parent']) && (int) $originalRecord['l10n_parent'] > 0) {
				$originalRecord = BackendUtility::getRecord(self::TABLE_NAME, (int) $originalRecord['l10n_parent']);
			}
		}

		$siteRoot = (int) $dataHandler->getPID(self::TABLE_NAME, $originalRecord['uid']);
		if ($siteRoot <= 0) {
			return;
		}

		$folderName = str_replace('#PID#', $siteRoot, self::FOLDER_FILEADMIN . self::FOLDER_SITEROOT);
		// First remove the folder with all files and then create it again. So no data artifacts are kept.
		GeneralUtility::rmdir(PATH_site . $folderName, TRUE);
		GeneralUtility::mkdir_deep(PATH_site . $folderName);
		$currentVersion = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);

		/** @var TypoScriptFrontendController $typoScriptFrontendController */
		$originalTSFE = $typoScriptFrontendController = $GLOBALS['TSFE'];
		if (!($typoScriptFrontendController instanceof TypoScriptFrontendController)) {
			$typoScriptFrontendController = $GLOBALS['TSFE'] = new TypoScriptFrontendController(
				$GLOBALS['TYPO3_CONF_VARS'], $siteRoot, 0
			);
		}

		// required in order to generate the menu links later on
		if ($currentVersion >= 9000000) {
			$typoScriptFrontendController->settingLanguage();
		} else {
			if (!is_object($GLOBALS['TT'])) {
				$GLOBALS['TT'] = new NullTimeTracker();
			}

			if ($currentVersion < 8000000) {
				// prevents a possible crash
				$typoScriptFrontendController->getPageRenderer()->setBackPath('');
			}

			$typoScriptFrontendController->initFEuser();
			$typoScriptFrontendController->initUserGroups();
			$typoScriptFrontendController->fetch_the_id();
			$typoScriptFrontendController->getPageAndRootline();
			$typoScriptFrontendController->initTemplate();
			$typoScriptFrontendController->no_cache = TRUE;
			$typoScriptFrontendController->getConfigArray();
			$typoScriptFrontendController->settingLanguage();
			$typoScriptFrontendController->settingLocale();
			$typoScriptFrontendController->convPOSTCharset();
			$typoScriptFrontendController->absRefPrefix = '/';
			PageGenerator::pagegenInit();
			$typoScriptFrontendController->newCObj();
		}

		$loadingScripts = [];
		$fullData = $this->getFullData($originalRecord, self::TABLE_NAME);

		$minifyFiles = (bool) $data['minify_generated_data'];
		$this->createCSSFile($folderName, $fullData, $minifyFiles);
		$loadingScripts['essential'] = [
			'html' => $this->getActivationHTML($fullData['essential_scripts']),
			'javaScript' => $this->createActivationScriptFile(
				$folderName, 'essential', $fullData['essential_scripts'], $minifyFiles
			),
		];

		foreach ($fullData['groups'] as $group) {
			$loadingScripts[$group['group_name']] = [
				'html' => $this->getActivationHTML($group['scripts']),
				'javaScript' => $this->createActivationScriptFile(
					$folderName, $group['group_name'], $group['scripts'], $minifyFiles
				),
			];
		}

		$languages = $this->getLanguages();
		foreach ($languages as $language) {
			$languageUid = (int) $language['uid'];
			if ($languageUid < 0) {
				continue;
			}

			$translatedRecord = $originalRecord;
			if ($languageUid > 0) {
				$pageRepository = GeneralUtility::makeInstance(PageRepository::class);
				$translatedRecord = $pageRepository->getRecordOverlay(self::TABLE_NAME, $originalRecord, $languageUid);
			}

			$fullData = $this->getFullData($translatedRecord, self::TABLE_NAME, $languageUid);
			if (count($fullData) <= 0) {
				return;
			}

			$this->createJavaScriptFile($folderName, $fullData, $loadingScripts, $languageUid, $minifyFiles);
		}

		GeneralUtility::fixPermissions(PATH_site . self::FOLDER_FILEADMIN, TRUE);

		// reset the TSFE to it's previous state to not influence remaining code
		$GLOBALS['TSFE'] = $originalTSFE;
	}

	/**
	 * Creates a JS file out of the given data array.
	 *
	 * @param string $folder
	 * @param array $data
	 * @param array $loadingScripts
	 * @param int $languageUid
	 * @param bool $minifyFile
	 * @return void
	 */
	protected function createJavaScriptFile(
		$folder, array $data, array $loadingScripts, $languageUid = 0, $minifyFile = TRUE
	) {
		$content = file_get_contents(PATH_site . self::TEMPLATE_JAVA_SCRIPT_PATH . self::TEMPLATE_JAVA_SCRIPT_NAME);

		$cookieGroups = [
			'essential' => [
				'label' => $data['essential_title'],
				'description' => $data['essential_description'],
				'required' => TRUE,
				'cookieData' => [],
				'loadingHTML' => isset($loadingScripts['essential']['html'])
					? $loadingScripts['essential']['html'] : '',
				'loadingJavaScript' => isset($loadingScripts['essential']['javaScript'])
					? $loadingScripts['essential']['javaScript'] : '',
			],
		];

		if ((boolean) $data['iframe_enabled']) {
			$cookieGroups['iframes'] = [
				'label' => $data['iframe_title'],
				'description' => $data['iframe_description'],
				'required' => FALSE,
				'cookieData' => [],
				'loadingHTML' => isset($loadingScripts['iframes']['html'])
					? $loadingScripts['iframes']['html'] : '',
				'loadingJavaScript' => isset($loadingScripts['iframes']['javaScript'])
					? $loadingScripts['iframes']['javaScript'] : '',
			];
		}

		foreach ($data['essential_cookies'] as $cookieData) {
			$cookieGroups['essential']['cookieData'][] = [
				'Name' => $cookieData['name'],
				'Provider' => $cookieData['provider'],
				'Purpose' => $cookieData['purpose'],
				'Lifetime' => $cookieData['lifetime'],
			];
		}

		foreach ($data['groups'] as $group) {
			$groupName = $group['group_name'];
			$cookieGroups[$groupName] = [
				'label' => $group['title'],
				'description' => $group['description'],
				'required' => FALSE,
				'cookieData' => [],
				'loadingHTML' => isset($loadingScripts[$groupName]['html'])
					? $loadingScripts[$groupName]['html'] : '',
				'loadingJavaScript' => isset($loadingScripts[$groupName]['javaScript'])
					? $loadingScripts[$groupName]['javaScript'] : '',
			];

			foreach ($group['cookies'] as $cookieData) {
				$cookieGroups[$groupName]['cookieData'][] = [
					'Name' => $cookieData['name'],
					'Provider' => $cookieData['provider'],
					'Purpose' => $cookieData['purpose'],
					'Lifetime' => $cookieData['lifetime'],
				];
			}
		}

		$footerLinks = [];
		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$contentObject = $objectManager->get(ContentObjectRenderer::class);
		foreach ($this->getPagesFromNavigation($data['navigation'], $languageUid) as $pageData) {
			try {
				$footerLinks[] = [
					'url' => $contentObject->getTypoLink_URL($pageData['uid'], '&disableOptIn=1&L=' . $languageUid),
					'name' => $contentObject->crop($pageData['title'], 35 . '|...|0'),
				];
			} catch (\Error $exception) {
				// Occurs on the first creation of the translation.
				continue;
			}
		}

		$settings = [
			'iframe_enabled' => (boolean) $data['iframe_enabled'],
			'cookie_lifetime' => (int) $data['cookie_lifetime'],
			'minify_generated_data' => (boolean) $data['minify_generated_data'],
		];

		$textEntries = [
			'header' => $data['header'],
			'description' => $data['description'],
			'accept_all_text' => $data['accept_all_text'],
			'accept_specific_text' => $data['accept_specific_text'],
			'accept_essential_text' => $data['accept_essential_text'],
			'extend_box_link_text' => $data['extend_box_link_text'],
			'extend_box_link_text_close' => $data['extend_box_link_text_close'],
			'extend_table_link_text' => $data['extend_table_link_text'],
			'extend_table_link_text_close' => $data['extend_table_link_text_close'],
			'cookie_name_text' => $data['cookie_name_text'],
			'cookie_provider_text' => $data['cookie_provider_text'],
			'cookie_purpose_text' => $data['cookie_purpose_text'],
			'cookie_lifetime_text' => $data['cookie_lifetime_text'],
			'iframe_button_allow_all_text' => $data['iframe_button_allow_all_text'],
			'iframe_button_allow_one_text' => $data['iframe_button_allow_one_text'],
			'iframe_button_load_one_text' => $data['iframe_button_load_one_text'],
			'iframe_open_settings_text' => $data['iframe_open_settings_text'],
		];

		$templateService = GeneralUtility::makeInstance(TemplateService::class);
		$content = str_replace(
			[
				'###SETTINGS###',
				'###COOKIE_GROUPS###',
				'###FOOTER_LINKS###',
				'###TEXT_ENTRIES###',
				'###MARKUP###'
			],
			[
				json_encode($settings),
				json_encode($cookieGroups),
				json_encode($footerLinks),
				json_encode($textEntries),
				// @todo Anpassen
				json_encode($templateService->renderTemplate('<h1>Hello {{planet}}</h1>', ['planet' => 'World!']))
			], $content
		);
		$file = PATH_site . $folder .
			str_replace('#LANG#', $data['sys_language_uid'], self::TEMPLATE_JAVA_SCRIPT_NEW_NAME);
		file_put_contents($file, $content);

		if ($minifyFile) {
			$minificationService = GeneralUtility::makeInstance(MinificationService::class);
			$minificationService->minifyJavaScriptFile($file);
		}

		GeneralUtility::fixPermissions($file);

		// remove deprecated old v1 file
		@unlink(PATH_site . $folder . self::TEMPLATE_JAVA_SCRIPT_V1_NEW_NAME);
	}

	/**
	 * Creates a CSS file out of the given data array.
	 *
	 * @param string $folder
	 * @param array $data
	 * @param array $minifyFile
	 * @return void
	 */
	protected function createCSSFile($folder, array $data, $minifyFile = TRUE) {
		$content = file_get_contents(PATH_site . self::TEMPLATE_STYLE_SHEET_PATH . self::TEMPLATE_STYLE_SHEET_NAME);
		$content = str_replace(
			[
				'###COLOR_BOX###',
				'###COLOR_HEADLINE###',
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
				'###COLOR_BUTTON_CLOSE###',
				'###COLOR_BUTTON_CLOSE_HOVER###',
				'###COLOR_BUTTON_CLOSE_TEXT###',
				'###COLOR_LIST###',
				'###COLOR_LIST_TEXT###',
				'###COLOR_TABLE###',
				'###COLOR_TABLE_HEADER_TEXT###',
				'###COLOR_TABLE_DATA_TEXT###',
				'###IFRAME_COLOR_CONSENT_BOX_BACKGROUND###',
				'###IFRAME_COLOR_BUTTON_LOAD_ONE###',
				'###IFRAME_COLOR_BUTTON_LOAD_ONE_HOVER###',
				'###IFRAME_COLOR_BUTTON_LOAD_ONE_TEXT###',
				'###IFRAME_COLOR_OPEN_SETTINGS###',
			],
			[
				$data['color_box'],
				$data['color_headline'],
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
				$data['color_button_close'],
				$data['color_button_close_hover'],
				$data['color_button_close_text'],
				$data['color_list'],
				$data['color_list_text'],
				$data['color_table'],
				$data['color_table_header_text'],
				$data['color_Table_data_text'],
				$data['iframe_color_consent_box_background'],
				$data['iframe_color_button_load_one'],
				$data['iframe_color_button_load_one_hover'],
				$data['iframe_color_button_load_one_text'],
				$data['iframe_color_open_settings'],
			], $content
		);
		$file = PATH_site . $folder . self::TEMPLATE_STYLE_SHEET_NAME;
		file_put_contents($file, $content);

		if ($minifyFile) {
			$minificationService = GeneralUtility::makeInstance(MinificationService::class);
			$minificationService->minifyCSSFile($file);
		}

		GeneralUtility::fixPermissions($file);
	}

	/**
	 * Creates a javascript file out of the given scripts array.
	 *
	 * @param string $folder
	 * @param string $groupName
	 * @param array $scripts
	 * @param bool $minifyFile
	 * @return string
	 */
	protected function createActivationScriptFile($folder, $groupName, array $scripts, $minifyFile = TRUE) {
		$content = '';
		foreach ($scripts as $script) {
			$scriptContent = trim($script['script']);
			if (!$scriptContent) {
				continue;
			}

			$content .= '// Script: ' . $script['title'] . "\n\n" . $scriptContent . "\n\n";
		}

		if ($content === '') {
			return '';
		}

		$file = $folder . $groupName . '.js';
		$groupFile = PATH_site . $folder . $groupName . '.js';
		file_put_contents($groupFile, $content);

		if ($minifyFile) {
			$minificationService = GeneralUtility::makeInstance(MinificationService::class);
			$minificationService->minifyJavaScriptFile($groupFile);
		}

		GeneralUtility::fixPermissions($groupFile);
		return '/' . $file;
	}

	/**
	 * Creates a html string out of the given scripts.
	 *
	 * @param array $scripts
	 * @return string
	 */
	protected function getActivationHTML(array $scripts) {
		$content = '';
		foreach ($scripts as $script) {
			$htmlContent = trim($script['html']);
			if (!$htmlContent) {
				continue;
			}

			$content .= $htmlContent . "\n\n";
		}

		return $content;
	}

	/**
	 * Returns an array with page data out of the given data string.
	 *
	 * @param string $navigationData
	 * @param int $languageUid
	 * @return array
	 */
	protected function getPagesFromNavigation($navigationData, $languageUid = 0) {
		if (!$navigationData) {
			return [];
		}

		$records = [];
		$navigationEntries = GeneralUtility::trimExplode(',', $navigationData);
		$pageRepository = GeneralUtility::makeInstance(PageRepository::class);
		$versionNumber = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
		foreach ($navigationEntries as $navigationEntry) {
			if (!$navigationEntry) {
				continue;
			}

			$record = BackendUtility::getRecord('pages', $navigationEntry);
			if (!$record) {
				continue;
			}

			if ($languageUid > 0) {
				if ($versionNumber >= 9000000) {
					$record = $pageRepository->getRecordOverlay('pages', $record, $languageUid);
				} else {
					$record = $pageRepository->getPageOverlay($record, $languageUid);
				}
			}

			$records[] = $record;
		}

		return $records;
	}

	/**
	 * Returns the full data for the given data array.
	 *
	 * @param array $data
	 * @param string $table
	 * @param int $language
	 *
	 * @return array
	 */
	protected function getFullData(array $data, $table, $language = 0) {
		$fullData = [];
		$parentUid = (!empty($data['l10n_parent']) ? (int) $data['l10n_parent'] : (int) $data['uid']);
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
			$inlineData = $this->getDataForInlineField($foreignTable, $foreignField, $parentUid, $language);
			if (\count($inlineData) > 0) {
				foreach ($inlineData as $index => $inlineDataEntry) {
					if (!isset($inlineDataEntry['uid'])) {
						continue;
					}

					$fullData[$fieldName][$index] = $this->getFullData($inlineDataEntry, $foreignTable, $language);
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
		$languageField = $this->getTCALanguageField($table);
		if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) <= 9000000) {
			/** @var DatabaseConnection $database */
			$database = $GLOBALS['TYPO3_DB'];
			$rows = $database->exec_SELECTgetRows(
				'*', $table, 'deleted=0 AND ' . $field . '=' . $parentUid .
				($languageField ? ' AND ' . $languageField . '=0' : ''), '', 'sorting ASC'
			);
		} else {
			$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
			$queryBuilder = $connectionPool->getQueryBuilderForTable($table);
			$queryBuilder->getRestrictions()
				->removeAll()
				->add(GeneralUtility::makeInstance(DeletedRestriction::class));
			$queryBuilder->select('*')
				->from($table)
				->orderBy('sorting', 'ASC')
				->where(
					$queryBuilder->expr()->eq(
						$field,
						$parentUid
					)
				);

			if ($languageField) {
				$queryBuilder->andWhere(
					$queryBuilder->expr()->eq(
						$languageField,
						0
					)
				);
			}

			$rows = $queryBuilder->execute()->fetchAll();
		}

		if (!is_array($rows)) {
			return [];
		}

		$translatedRows = [];
		$pageRepository = GeneralUtility::makeInstance(PageRepository::class);
		foreach ($rows as $row) {
			$translatedRows[] = $pageRepository->getRecordOverlay($table, $row, $language);
		}

		return $translatedRows;
	}

	/**
	 * Returns all system languages.
	 *
	 * @return array
	 */
	protected function getLanguages() {
		if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) <= 9000000) {
			/** @var DatabaseConnection $database */
			$database = $GLOBALS['TYPO3_DB'];
			$rows = $database->exec_SELECTgetRows('uid', 'sys_language', '');
		} else {
			$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
			$queryBuilder = $connectionPool->getQueryBuilderForTable('sys_language');
			$queryBuilder->getRestrictions()
				->removeAll()
				->add(GeneralUtility::makeInstance(DeletedRestriction::class));
			$queryBuilder->select('uid')->from('sys_language');
			$rows = $queryBuilder->execute()->fetchAll();
		}

		if (is_array($rows)) {
			$rows[] = [
				'uid' => 0,
			];
		} else {
			$rows = [[
				'uid' => 0,
			]];
		}

		return $rows;
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

	/**
	 * Returns the language field of the given table.
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	protected function getTCALanguageField($table) {
		$tableData = $GLOBALS['TCA'][$table];
		if (!is_array($tableData)) {
			return '';
		}

		if (!isset($tableData['ctrl'])) {
			return '';
		}

		return (isset($tableData['ctrl']['languageField']) ? $tableData['ctrl']['languageField'] : '');
	}
}
