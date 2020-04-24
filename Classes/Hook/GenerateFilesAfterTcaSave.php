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

use SGalinski\SgCookieOptin\Service\ExtensionSettingsService;
use SGalinski\SgCookieOptin\Service\LicensingService;
use SGalinski\SgCookieOptin\Service\MinificationService;
use SGalinski\SgCookieOptin\Service\TemplateService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
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

	const FOLDER_SITEROOT = 'siteroot-#PID#/';

	const TEMPLATE_JAVA_SCRIPT_PATH = 'typo3conf/ext/sg_cookie_optin/Resources/Public/JavaScript/';
	const TEMPLATE_JAVA_SCRIPT_NAME = 'cookieOptin.js';

	const TEMPLATE_JSON_NAME = 'cookieOptinData_#LANG#.json';

	const TEMPLATE_STYLE_SHEET_PATH = 'typo3conf/ext/sg_cookie_optin/Resources/Public/StyleSheets/';
	const TEMPLATE_STYLE_SHEET_NAME = 'cookieOptin.css';

	/**
	 * Generates the files out of the TCA data.
	 *
	 * @param DataHandler $dataHandler
	 *
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

		$folder = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_FOLDER);
		if (!$folder) {
			return;
		}

		$folderName = str_replace('#PID#', $siteRoot, $folder . self::FOLDER_SITEROOT);
		$sitePath = defined(PATH_site) ? PATH_site : \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/';
		// First remove the folder with all files and then create it again. So no data artifacts are kept.
		GeneralUtility::rmdir($sitePath . $folderName, TRUE);
		GeneralUtility::mkdir_deep($sitePath . $folderName);
		GeneralUtility::fixPermissions($sitePath . $folder, TRUE);
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

			if ($currentVersion < 8000000) {
				// prevents a possible crash, where the whole backend is loaded within the same frame and the files
				// aren't generated.
				$typoScriptFrontendController->getConfigArray();
			}

			$typoScriptFrontendController->settingLanguage();
			$typoScriptFrontendController->settingLocale();
			$typoScriptFrontendController->convPOSTCharset();
			$typoScriptFrontendController->absRefPrefix = '/';
			PageGenerator::pagegenInit();
			$typoScriptFrontendController->newCObj();
		}

		$fullData = $this->getFullData($originalRecord, self::TABLE_NAME);
		$minifyFiles = (bool) $fullData['minify_generated_data'];
		$cssData = [
			'color_box' => $fullData['color_box'],
			'color_headline' => $fullData['color_headline'],
			'color_text' => $fullData['color_text'],
			'color_checkbox' => $fullData['color_checkbox'],
			'color_checkbox_required' => $fullData['color_checkbox_required'],
			'color_button_all' => $fullData['color_button_all'],
			'color_button_all_hover' => $fullData['color_button_all_hover'],
			'color_button_all_text' => $fullData['color_button_all_text'],
			'color_button_specific' => $fullData['color_button_specific'],
			'color_button_specific_hover' => $fullData['color_button_specific_hover'],
			'color_button_specific_text' => $fullData['color_button_specific_text'],
			'color_button_essential' => $fullData['color_button_essential'],
			'color_button_essential_hover' => $fullData['color_button_essential_hover'],
			'color_button_essential_text' => $fullData['color_button_essential_text'],
			'color_button_close' => $fullData['color_button_close'],
			'color_button_close_hover' => $fullData['color_button_close_hover'],
			'color_button_close_text' => $fullData['color_button_close_text'],
			'color_list' => $fullData['color_list'],
			'color_list_text' => $fullData['color_list_text'],
			'color_table' => $fullData['color_table'],
			'color_Table_data_text' => $fullData['color_Table_data_text'],
			'color_table_header' => $fullData['color_table_header'],
			'color_table_header_text' => $fullData['color_table_header_text'],
			'color_full_box' => $fullData['color_full_box'],
			'color_full_headline' => $fullData['color_full_headline'],
			'color_full_text' => $fullData['color_full_text'],
			'color_full_button_close' => $fullData['color_full_button_close'],
			'color_full_button_close_hover' => $fullData['color_full_button_close_hover'],
			'color_full_button_close_text' => $fullData['color_full_button_close_text'],
			'iframe_color_consent_box_background' => $fullData['iframe_color_consent_box_background'],
			'iframe_color_button_load_one' => $fullData['iframe_color_button_load_one'],
			'iframe_color_button_load_one_hover' => $fullData['iframe_color_button_load_one_hover'],
			'iframe_color_button_load_one_text' => $fullData['iframe_color_button_load_one_text'],
			'iframe_color_open_settings' => $fullData['iframe_color_open_settings'],
			'banner_color_box' => $fullData['banner_color_box'],
			'banner_color_text' => $fullData['banner_color_text'],
			'banner_color_link_text' => $fullData['banner_color_link_text'],
			'banner_color_button_settings' => $fullData['banner_color_button_settings'],
			'banner_color_button_settings_hover' => $fullData['banner_color_button_settings_hover'],
			'banner_color_button_settings_text' => $fullData['banner_color_button_settings_text'],
			'banner_color_button_accept' => $fullData['banner_color_button_accept'],
			'banner_color_button_accept_hover' => $fullData['banner_color_button_accept_hover'],
			'banner_color_button_accept_text' => $fullData['banner_color_button_accept_text'],
		];
		$this->createCSSFile($fullData, $folderName, $cssData, $minifyFiles);

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

			$translatedFullData = $this->getFullData($translatedRecord, self::TABLE_NAME, $languageUid);
			if (count($translatedFullData) <= 0) {
				continue;
			}

			$this->createJavaScriptFile($folderName, $minifyFiles);
			$this->createJsonFile($folderName, $fullData, $translatedFullData, $cssData, $minifyFiles, $languageUid);
		}

		GeneralUtility::fixPermissions($sitePath . $folder, TRUE);

		// reset the TSFE to it's previous state to not influence remaining code
		$GLOBALS['TSFE'] = $originalTSFE;
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
				'*', $table, 'deleted=0 AND hidden=0 AND ' . $field . '=' . $parentUid .
				($languageField ? ' AND ' . $languageField . '=0' : ''), '', 'sorting ASC'
			);
		} else {
			$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
			$queryBuilder = $connectionPool->getQueryBuilderForTable($table);
			$queryBuilder->getRestrictions()
				->removeAll()
				->add(GeneralUtility::makeInstance(HiddenRestriction::class))
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

	/**
	 * Creates a CSS file out of the given data array.
	 *
	 * @param array $data
	 * @param string $folder
	 * @param array $cssData
	 * @param boolean $minifyFile
	 *
	 * @return void
	 */
	protected function createCSSFile(array $data, $folder, array $cssData, $minifyFile = TRUE) {
		$sitePath = defined(PATH_site) ? PATH_site : \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/';
		$content = '/* Base styles: ' . self::TEMPLATE_STYLE_SHEET_NAME . " */\n\n" .
			file_get_contents($sitePath . self::TEMPLATE_STYLE_SHEET_PATH . self::TEMPLATE_STYLE_SHEET_NAME);

		$templateService = GeneralUtility::makeInstance(TemplateService::class);
		$content .= " \n\n" . $templateService->getCSSContent(TemplateService::TYPE_TEMPLATE, $data['template_selection']);
		if ((boolean) $data['banner_enable']) {
			$content .= " \n\n" . $templateService->getCSSContent(TemplateService::TYPE_BANNER, $data['banner_selection']);
		}

		if ((boolean) $data['iframe_enabled']) {
			$content .= " \n\n" . $templateService->getCSSContent(TemplateService::TYPE_IFRAME, $data['iframe_selection']);
			$content .= " \n\n" . $templateService->getCSSContent(TemplateService::TYPE_IFRAME_REPLACEMENT, $data['iframe_replacement_selection']);
		}

		$keys = $data = [];
		foreach ($cssData as $key => $value) {
			$keys[] = '%23###' . $key . '###';
			$data[] = '%23' . ltrim($value, '#');

			$keys[] = '###' . $key . '###';
			$data[] = $value;
		}

		$file = $sitePath . $folder . self::TEMPLATE_STYLE_SHEET_NAME;
		file_put_contents($file, str_replace($keys, $data, $content));

		if ($minifyFile) {
			$minificationService = GeneralUtility::makeInstance(MinificationService::class);
			$minificationService->minifyCSSFile($file);
		}

		GeneralUtility::fixPermissions($file);
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
	 * Creates a html string out of the given scripts.
	 *
	 * @param array $scripts
	 *
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
	 * Creates a javascript file out of the given scripts array.
	 *
	 * @param string $folder
	 * @param string $groupName
	 * @param array $scripts
	 * @param int $languageUid
	 * @param bool $minifyFile
	 *
	 * @return string
	 */
	protected function createActivationScriptFile(
		$folder, $groupName, array $scripts, $languageUid = 0, $minifyFile = TRUE
	) {
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

		$file = $folder . $groupName . '-' . $languageUid . '.js';
		$sitePath = defined(PATH_site) ? PATH_site : \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/';
		$groupFile = $sitePath . $file;
		file_put_contents($groupFile, $content);

		if ($minifyFile) {
			$minificationService = GeneralUtility::makeInstance(MinificationService::class);
			$minificationService->minifyJavaScriptFile($groupFile);
		}

		GeneralUtility::fixPermissions($groupFile);
		return '/' . $file;
	}

	/**
	 * Creates a JS file out of the given data array.
	 *
	 * @param string $folder
	 * @param bool $minifyFile
	 *
	 * @return void
	 */
	protected function createJavaScriptFile($folder, $minifyFile = TRUE) {
		$sitePath = defined(PATH_site) ? PATH_site : \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/';
		$file = $sitePath . $folder . self::TEMPLATE_JAVA_SCRIPT_NAME;
		copy($sitePath . self::TEMPLATE_JAVA_SCRIPT_PATH . self::TEMPLATE_JAVA_SCRIPT_NAME, $file);

		if ($minifyFile) {
			$minificationService = GeneralUtility::makeInstance(MinificationService::class);
			$minificationService->minifyJavaScriptFile($file);
		}

		GeneralUtility::fixPermissions($file);
	}

	/**
	 * Creates a JSON file out of the given data array.
	 *
	 * @param string $folder
	 * @param array $data
	 * @param array $translatedData
	 * @param array $cssData
	 * @param bool $minifyFiles
	 * @param int $languageUid
	 *
	 * @return void
	 */
	protected function createJsonFile(
		$folder, array $data, array $translatedData, array $cssData, $minifyFiles, $languageUid = 0
	) {
		$essentialCookieData = [];
		$pseudoElements = 0;
		$groupIndex = 0;
		foreach ($translatedData['essential_cookies'] as $index => $cookieData) {
			$essentialCookieData[] = [
				'Name' => $cookieData['name'],
				'Provider' => $cookieData['provider'],
				'Purpose' => $cookieData['purpose'],
				'Lifetime' => $cookieData['lifetime'],
				'index' => $groupIndex,
				'pseudo' => FALSE,
			];
			++$groupIndex;
			$pseudoElements = $groupIndex % 3;
		}

		for ($index = 1; $index < $pseudoElements; ++$index) {
			$essentialCookieData[] = [
				'Name' => '',
				'Provider' => '',
				'Purpose' => '',
				'Lifetime' => '',
				'index' => $groupIndex,
				'pseudo' => TRUE,
			];
			++$groupIndex;
		}

		$essentialScriptData = [];
		foreach ($translatedData['essential_scripts'] as $index => $scriptData) {
			$essentialScriptData[] = [
				'title' => $scriptData['title'],
				'script' => $scriptData['script'],
				'html' => $scriptData['html'],
				'index' => $index,
			];
		}

		$cookieGroups = [
			[
				'groupName' => 'essential',
				'label' => $translatedData['essential_title'],
				'description' => $translatedData['essential_description'],
				'required' => TRUE,
				'cookieData' => $essentialCookieData,
				'scriptData' => $essentialScriptData,
				'loadingHTML' => $this->getActivationHTML($translatedData['essential_scripts']),
				'loadingJavaScript' => $this->createActivationScriptFile(
					$folder, 'essential', $translatedData['essential_scripts'], $languageUid, $minifyFiles
				),
			],
		];

		foreach ($translatedData['groups'] as $group) {
			$groupCookieData = [];
			$pseudoElements = 0;
			$groupIndex = 0;
			foreach ($group['cookies'] as $index => $cookieData) {
				$groupCookieData[] = [
					'Name' => $cookieData['name'],
					'Provider' => $cookieData['provider'],
					'Purpose' => $cookieData['purpose'],
					'Lifetime' => $cookieData['lifetime'],
					'index' => $groupIndex,
					'pseudo' => FALSE,
				];
				++$groupIndex;
				$pseudoElements = $groupIndex % 3;
			}

			for ($index = 1; $index < $pseudoElements; ++$index) {
				$groupCookieData[] = [
					'Name' => '',
					'Provider' => '',
					'Purpose' => '',
					'Lifetime' => '',
					'index' => $groupIndex,
					'pseudo' => TRUE,
				];
				++$groupIndex;
			}

			$groupScriptData = [];
			foreach ($group['scripts'] as $index => $scriptData) {
				$groupScriptData[] = [
					'title' => $scriptData['title'],
					'script' => $scriptData['script'],
					'html' => $scriptData['html'],
					'index' => $index,
				];
			}

			$groupName = $group['group_name'];
			$cookieGroups[] = [
				'groupName' => $groupName,
				'label' => $group['title'],
				'description' => $group['description'],
				'required' => FALSE,
				'cookieData' => $groupCookieData,
				'scriptData' => $groupScriptData,
				'loadingHTML' => $this->getActivationHTML($group['scripts']),
				'loadingJavaScript' => $this->createActivationScriptFile(
					$folder, $group['group_name'], $group['scripts'], $languageUid, $minifyFiles
				),
			];
		}

		$iFrameGroup = [
			'groupName' => 'iframes',
			'label' => $translatedData['iframe_title'],
			'description' => $translatedData['iframe_description'],
			'required' => FALSE,
			'cookieData' => [],
		];
		if ((boolean) $translatedData['iframe_enabled']) {
			$cookieGroups[] = $iFrameGroup;
		}

		$navigationEntries = $this->getPagesFromNavigation($translatedData['navigation'], $languageUid);
		if (count($navigationEntries) <= 0) {
			$navigationEntries = $this->getPagesFromNavigation($data['navigation'], $languageUid);
		}

		$footerLinks = [];
		$index = 0;
		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$contentObject = $objectManager->get(ContentObjectRenderer::class);
		foreach ($navigationEntries as $pageData) {
			try {
				$footerLinks[$index] = [
					'url' => $contentObject->getTypoLink_URL($pageData['uid'], '&disableOptIn=1&L=' . $languageUid),
					'name' => $contentObject->crop($pageData['title'], 35 . '|...|0'),
					'uid' => $pageData['uid'],
					'index' => $index,
				];
				++$index;
			} catch (\Error $exception) {
				// Occurs on the first creation of the translation.
				continue;
			}
		}

		$settings = [
			'banner_enable' => (boolean) $translatedData['banner_enable'],
			'banner_position' => (int) $translatedData['banner_position'],
			'banner_show_settings_button' => (boolean) $translatedData['banner_show_settings_button'],
			'cookie_lifetime' => (int) $translatedData['cookie_lifetime'],
			'iframe_enabled' => (boolean) $translatedData['iframe_enabled'],
			'minify_generated_data' => (boolean) $translatedData['minify_generated_data'],
			'show_button_close' => (boolean) $translatedData['show_button_close'],
			'activate_testing_mode' => (boolean) $translatedData['activate_testing_mode'],
		];

		$textEntries = [
			'header' => $translatedData['header'],
			'description' => $translatedData['description'],
			'accept_all_text' => $translatedData['accept_all_text'],
			'accept_specific_text' => $translatedData['accept_specific_text'],
			'accept_essential_text' => $translatedData['accept_essential_text'],
			'extend_box_link_text' => $translatedData['extend_box_link_text'],
			'extend_box_link_text_close' => $translatedData['extend_box_link_text_close'],
			'extend_table_link_text' => $translatedData['extend_table_link_text'],
			'extend_table_link_text_close' => $translatedData['extend_table_link_text_close'],
			'cookie_name_text' => $translatedData['cookie_name_text'],
			'cookie_provider_text' => $translatedData['cookie_provider_text'],
			'cookie_purpose_text' => $translatedData['cookie_purpose_text'],
			'cookie_lifetime_text' => $translatedData['cookie_lifetime_text'],
			'iframe_button_allow_all_text' => $translatedData['iframe_button_allow_all_text'],
			'iframe_button_allow_one_text' => $translatedData['iframe_button_allow_one_text'],
			'iframe_button_load_one_text' => $translatedData['iframe_button_load_one_text'],
			'iframe_open_settings_text' => $translatedData['iframe_open_settings_text'],
			'banner_button_accept_text' => $translatedData['banner_button_accept_text'],
			'banner_button_settings_text' => $translatedData['banner_button_settings_text'],
			'banner_description' => $translatedData['banner_description'],
		];

		$jsonDataArray = [
			'cookieGroups' => $cookieGroups,
			'cssData' => $cssData,
			'footerLinks' => $footerLinks,
			'iFrameGroup' => $iFrameGroup,
			'settings' => $settings,
			'textEntries' => $textEntries,
		];

		$jsonDataArray['mustacheData'] = [
			'template' => [
				'template_html' => $translatedData['template_html'],
				'template_overwritten' => $translatedData['template_overwritten'],
				'template_selection' => $translatedData['template_selection'],
				'markup' => $this->getRenderedMustacheTemplate(
					$translatedData['template_overwritten'], $translatedData['template_html'],
					$translatedData['template_selection'], TemplateService::TYPE_TEMPLATE, $jsonDataArray
				),
			],
			'banner' => [
				'banner_html' => $translatedData['banner_html'],
				'banner_overwritten' => $translatedData['banner_overwritten'],
				'banner_selection' => $translatedData['banner_selection'],
				'markup' => $this->getRenderedMustacheTemplate(
					$translatedData['banner_overwritten'], $translatedData['banner_html'],
					$translatedData['banner_selection'], TemplateService::TYPE_BANNER, $jsonDataArray
				),
			],
			'iframe' => [
				'iframe_html' => $translatedData['iframe_html'],
				'iframe_overwritten' => $translatedData['iframe_overwritten'],
				'iframe_selection' => $translatedData['iframe_selection'],
				'markup' => $this->getRenderedMustacheTemplate(
					$translatedData['iframe_overwritten'], $translatedData['iframe_html'],
					$translatedData['iframe_selection'], TemplateService::TYPE_IFRAME, $jsonDataArray
				),
			],
			'iframeReplacement' => [
				'iframe_replacement_html' => $translatedData['iframe_replacement_html'],
				'iframe_replacement_overwritten' => $translatedData['iframe_replacement_overwritten'],
				'iframe_replacement_selection' => $translatedData['iframe_replacement_selection'],
				'markup' => $this->getRenderedMustacheTemplate(
					$translatedData['iframe_replacement_overwritten'], $translatedData['iframe_replacement_html'],
					$translatedData['iframe_replacement_selection'], TemplateService::TYPE_IFRAME_REPLACEMENT,
					$jsonDataArray
				),
			],
		];

		$sitePath = defined(PATH_site) ? PATH_site : \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/';
		$file = $sitePath . $folder . str_replace('#LANG#', $translatedData['sys_language_uid'], self::TEMPLATE_JSON_NAME);
		file_put_contents($file, json_encode($jsonDataArray, JSON_PRETTY_PRINT));
		GeneralUtility::fixPermissions($file);
	}

	/**
	 * Renders a mustache template by the given data and returns it.
	 *
	 * @param bool $overwritten
	 * @param string $overwrittenTemplate
	 * @param int $templateSelection
	 * @param int $type
	 * @param array $data
	 *
	 * @return string
	 */
	protected function getRenderedMustacheTemplate(
		$overwritten, $overwrittenTemplate, $templateSelection, $type, array $data
	) {
		$templateService = GeneralUtility::makeInstance(TemplateService::class);
		if ((boolean) $overwritten && $overwrittenTemplate) {
			$template = $overwrittenTemplate;
		} else {
			$template = $templateService->getMustacheContent((int) $type, (int) $templateSelection);
		}

		$mustacheTemplate = '';
		if ($template) {
			$mustacheTemplate = $templateService->renderTemplate($template, $data);
		}

		return $mustacheTemplate;
	}

	/**
	 * Returns an array with page data out of the given data string.
	 *
	 * @param string $navigationData
	 * @param int $languageUid
	 *
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
}
