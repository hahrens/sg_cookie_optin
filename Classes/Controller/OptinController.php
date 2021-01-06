<?php

namespace SGalinski\SgCookieOptin\Controller;

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

use DirectoryIterator;
use Exception;
use SGalinski\SgCookieOptin\Exception\JsonImportException;
use SGalinski\SgCookieOptin\Service\BackendService;
use SGalinski\SgCookieOptin\Service\DemoModeService;
use SGalinski\SgCookieOptin\Service\ExtensionSettingsService;
use SGalinski\SgCookieOptin\Service\JsonImportService;
use SGalinski\SgCookieOptin\Service\LanguageService;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\DocHeaderComponent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Optin Controller
 */
class OptinController extends ActionController {
	/**
	 * DocHeaderComponent
	 *
	 * @var DocHeaderComponent
	 */
	protected $docHeaderComponent;

	/**
	 * Starts the module, even opens up a TCEForm, or shows where the domain root is.
	 *
	 * @param array $parameters
	 */
	public function indexAction(array $parameters = []) {
		$this->initComponents();
		$pageUid = (int) GeneralUtility::_GP('id');
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
		if ($pageInfo && (int) $pageInfo['is_siteroot'] === 1) {
			$optIns = BackendService::getOptins($pageUid);

			if (count($optIns) > 1) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.tooManyRecorsException.description', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.tooManyRecorsException.header', 'sg_cookie_optin'),
					AbstractMessage::ERROR
				);
			}

			$this->view->assign('isSiteRoot', TRUE);
			$this->view->assign('optins', $optIns);
		}

		$this->view->assign('pages', BackendService::getPages());
	}

	/**
	 * Activates the demo mode for the given instance.
	 *
	 * @return void
	 * @throws UnsupportedRequestTypeException
	 * @throws StopActionException
	 */
	public function activateDemoModeAction() {
		if (DemoModeService::isInDemoMode() || !DemoModeService::isDemoModeAcceptable()) {
			$this->redirect('index');
		}

		DemoModeService::activateDemoMode();
		$this->redirect('index');
	}

	/**
	 * Renders the cookie opt in.
	 *
	 * @return void
	 */
	public function showAction() {

	}

	/**
	 * Imports JSON configuration
	 *
	 * @throws StopActionException
	 */
	public function importJsonAction() {
		session_start();
		$pid = (int) GeneralUtility::_GP('id');
		try {
			if (!isset($_SESSION['tx_sgcookieoptin']['importJsonData']['defaultLanguageId'])) {
				throw new JsonImportException(
					LocalizationUtility::translate(
						'jsonImport.error.theStoredImportedDataIsCorrupt',
						'sg_cookie_optin'
					), 101
				);
			}

			$defaultLanguageId = $_SESSION['tx_sgcookieoptin']['importJsonData']['defaultLanguageId'];
			$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
			$jsonImportService = $objectManager->get(JsonImportService::class);

			$defaultLanguageJsonData = $_SESSION['tx_sgcookieoptin']['importJsonData']['languageData'][$defaultLanguageId];
			$defaultLanguageOptinId = $jsonImportService->importJsonData($defaultLanguageJsonData, $pid);

			foreach ($_SESSION['tx_sgcookieoptin']['importJsonData']['languageData'] as $languageId => $jsonData) {
				if ($languageId !== $defaultLanguageId) {
					$optInId = $jsonImportService->importJsonData(
						$jsonData, $pid, $languageId, $defaultLanguageOptinId
					);
				}
			}

			unset($_SESSION['tx_sgcookieoptin']['importJsonData']);
			$this->redirectToTCAEdit($defaultLanguageOptinId);
		} catch (Exception $exception) {
			$this->addFlashMessage(
				$exception->getMessage(),
				'',
				AbstractMessage::ERROR
			);
			$this->redirect('previewImport', 'Optin', 'sg_cookie_optin');
		}
	}

	/**
	 * Redirects to the edit action
	 *
	 * @param int $optInId
	 * @throws RouteNotFoundException
	 */
	protected function redirectToTCAEdit($optInId) {
		$pid = (int) GeneralUtility::_GP('id');
		$uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
		$params = [
			'edit' => ['tx_sgcookieoptin_domain_model_optin' => [$optInId => 'edit']],
			'returnUrl' => (string) $uriBuilder->buildUriFromRoute('web_SgCookieOptinOptin', ['id' => $pid])
		];
		$uri = (string) $uriBuilder->buildUriFromRoute('record_edit', $params);
		header('Location: ' . $uri);
	}

	/**
	 * Displays statistics about the imported data for a  preview
	 *
	 * @throws StopActionException
	 * @throws \TYPO3\CMS\Extbase\Object\Exception
	 */
	public function previewImportAction() {
		session_start();
		$this->initComponents();
		$pageUid = (int) GeneralUtility::_GP('id');
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
		if ($pageInfo && (int) $pageInfo['is_siteroot'] === 1) {
			$optIns = BackendService::getOptins($pageUid);

			if (count($optIns) > 0) {
				$this->addFlashMessage(
					LocalizationUtility::translate(
						'backend.jsonImport.tooManyRecorsException.description', 'sg_cookie_optin'
					),
					LocalizationUtility::translate(
						'backend.jsonImport.tooManyRecorsException.header', 'sg_cookie_optin'
					),
					AbstractMessage::INFO
				);
			}

			$this->view->assign('isSiteRoot', TRUE);
			$this->view->assign('optins', $optIns);
		}
		try {
			$languages = LanguageService::getLanguages($pageUid);
		} catch (SiteNotFoundException $e) {
			$languages = [];
		}
		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$jsonImportService = $objectManager->get(JsonImportService::class);
		try {
			if (!isset($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin'])) {
				throw new JsonImportException(
					LocalizationUtility::translate('frontend.error.noFileUploaded', 'sg_cookie_optin'), 104
				);
			}

			$jsonImportService->parseAndStoreImportedData($languages);

			// check if all local languages are translated
			$untranslatedLanguages = [];
			foreach ($languages as $language) {
				if (!isset($_SESSION['tx_sgcookieoptin']['importJsonData']['languageData'][$language['uid']])) {
					$this->addFlashMessage(
						LocalizationUtility::translate(
							'backend.jsonImport.warnings.language.missing', 'sg_cookie_optin',
							['lang' => $language['title'] . ' (' . $language['locale'] . ')']
						),
						LocalizationUtility::translate(
							'backend.jsonImport.warnings.language.header', 'sg_cookie_optin'
						),
						AbstractMessage::WARNING
					);
				}
			}

			// check groups, cookies and scripts count
			$groupsCounts = [];
			$cookiesCounts = [];
			$scriptsCounts = [];
			$warningCookies = FALSE;
			$warningGroups = FALSE;
			$warningScripts = FALSE;
			$dataSummary = [];
			foreach ($_SESSION['tx_sgcookieoptin']['importJsonData']['languageData'] as $languageId => $languageData) {
				$groupsCounts[$languageId] = count($languageData['cookieGroups']);
				foreach ($languageData['cookieGroups'] as $group) {
					$cookiesCounts[$languageId] += (isset($group['cookieData'])) ? count($group['cookieData']) : 0;
					$scriptsCounts[$languageId] += (isset($group['scriptData'])) ? count($group['scriptData']) : 0;
				}
			}

			if (count(array_unique($groupsCounts)) > 1) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.jsonImport.warnings.groupsCount', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.jsonImport.warnings.header', 'sg_cookie_optin'),
					AbstractMessage::WARNING
				);
				$warningGroups = TRUE;
			}

			if (count(array_unique($cookiesCounts)) > 1) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.jsonImport.warnings.cookiesCount', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.jsonImport.warnings.header', 'sg_cookie_optin'),
					AbstractMessage::WARNING
				);
				$warningCookies = TRUE;
			}

			if (count(array_unique($scriptsCounts)) > 1) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.jsonImport.warnings.scriptsCount', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.jsonImport.warnings.header', 'sg_cookie_optin'),
					AbstractMessage::WARNING
				);
				$warningScripts = TRUE;
			}

			foreach ($languages as $language) {
				$dataSummary[$language['uid']] = [
					'translated' => array_key_exists($language['uid'], $groupsCounts),
					'groups' => $groupsCounts[$language['uid']],
					'cookies' => $cookiesCounts[$language['uid']],
					'scripts' => $scriptsCounts[$language['uid']],
					'title' => $language['title'],
					'locale' => $language['locale'],
					'flagIdentifier' => $language['flagIdentifier']
				];
			}
			$this->view->assign('dataSummary', $dataSummary);
			$this->view->assign('warningGroups', $warningGroups);
			$this->view->assign('warningScripts', $warningScripts);
			$this->view->assign('warningCookies', $warningCookies);

		} catch (Exception $exception) {
			$this->addFlashMessage(
				$exception->getMessage(),
				'',
				AbstractMessage::ERROR
			);
			$this->redirect('uploadJson', 'Optin', 'sg_cookie_optin');
		}
	}

	/**
	 * Downloads a JSON file containing all the configuration for each language
	 *
	 * @return string
	 */
	public function exportJsonAction() {
		try {
			$pid = (int) GeneralUtility::_GP('id');
			$folder = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_FOLDER);
			$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . DIRECTORY_SEPARATOR;
			$filesPath = $sitePath . $folder . 'siteroot-' . $pid . DIRECTORY_SEPARATOR;
			$jsonData = [];
			foreach (new DirectoryIterator($filesPath) as $file) {
				if (strpos($file->getFilename(), 'cookieOptinData') !== 0) {
					continue;
				}

				$contents = file_get_contents($filesPath . $file->getFilename());
				$locale = LanguageService::getLocaleByFileName(
					str_replace('.json', '', $file->getFilename())
				);
				$jsonData[$locale] = json_decode($contents, TRUE);
			}

			header('Content-disposition: attachment; filename=sg_cookie_optin.json');
			header('Content-type: application/json');
			echo json_encode($jsonData, TRUE);
			die();
		} catch (Exception $exception) {
			return 'Could not export the configuration because of the following error: ' . $exception->getMessage();
		}
	}

	/**
	 * Initialize the demo mode check and the doc header components
	 */
	protected function initComponents() {
		$typo3Version = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
		$keyState = DemoModeService::checkKey();
		$isInDemoMode = DemoModeService::isInDemoMode();
		if ($keyState !== DemoModeService::STATE_LICENSE_VALID && $isInDemoMode) {
			// - 1 because the flash message would show 00:00:00 instead of 23:59:59
			$this->addFlashMessage(
				LocalizationUtility::translate(
					'backend.licenseKey.isInDemoMode.description', 'sg_cookie_optin', [
						date('H:i:s', mktime(0, 0, DemoModeService::getRemainingTimeInDemoMode() - 1))
					]
				),
				LocalizationUtility::translate('backend.licenseKey.isInDemoMode.header', 'sg_cookie_optin'),
				AbstractMessage::INFO
			);
		} elseif ($keyState === DemoModeService::STATE_LICENSE_INVALID) {
			DemoModeService::removeAllCookieOptInFiles();

			if ($typo3Version < 9000000) {
				$description = LocalizationUtility::translate(
					'backend.licenseKey.invalid.description', 'sg_cookie_optin'
				);
			} else {
				$description = LocalizationUtility::translate(
					'backend.licenseKey.invalid.descriptionTYPO3-9', 'sg_cookie_optin'
				);
			}

			$this->addFlashMessage(
				$description,
				LocalizationUtility::translate('backend.licenseKey.invalid.header', 'sg_cookie_optin'),
				AbstractMessage::ERROR
			);
		} elseif ($keyState === DemoModeService::STATE_LICENSE_NOT_SET) {
			DemoModeService::removeAllCookieOptInFiles();

			if ($typo3Version < 9000000) {
				$description = LocalizationUtility::translate(
					'backend.licenseKey.notSet.description', 'sg_cookie_optin'
				);
			} else {
				$description = LocalizationUtility::translate(
					'backend.licenseKey.notSet.descriptionTYPO3-9', 'sg_cookie_optin'
				);
			}

			$this->addFlashMessage(
				$description,
				LocalizationUtility::translate('backend.licenseKey.notSet.header', 'sg_cookie_optin'),
				AbstractMessage::WARNING
			);
		}

		// create doc header component
		$pageUid = (int) GeneralUtility::_GP('id');
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));

		// the docHeaderComponent do not exist below version 7
		if ($typo3Version > 7000000) {
			$this->docHeaderComponent = GeneralUtility::makeInstance(DocHeaderComponent::class);
			if ($pageInfo === FALSE) {
				$pageInfo = ['uid' => $pageUid];
			}
			$this->docHeaderComponent->setMetaInformation($pageInfo);
			BackendService::makeButtons($this->docHeaderComponent, $this->request);
			$this->view->assign('docHeader', $this->docHeaderComponent->docHeaderContent());
		}

		$this->view->assign('typo3Version', $typo3Version);
		$this->view->assign('pageUid', $pageUid);
		$this->view->assign('invalidKey', $keyState !== DemoModeService::STATE_LICENSE_VALID);
		$this->view->assign('showDemoButton', !$isInDemoMode && DemoModeService::isDemoModeAcceptable());
	}

	/**
	 * Renders the upload JSON form
	 */
	public function uploadJsonAction() {
		$this->initComponents();

		$this->view->assign('pages', BackendService::getPages());

	}

	/**
	 * Create an optin entry in the database and redirect to edit action
	 *
	 * @throws RouteNotFoundException
	 * @throws SiteNotFoundException
	 */
	public function createAction() {
		$pid = (int) GeneralUtility::_GP('id');
		// create with DataHandler
		// adding default values for the german language. The values are hardcoded because they must not change since we don't know
		// the language keys or whatsoever in the target system
		$dataMapArray = [
			'description' => 'Auf unserer Webseite werden Cookies verwendet. Einige davon werden zwingend benötigt, während es uns andere ermöglichen, Ihre Nutzererfahrung auf unserer Webseite zu verbessern.',
			'template_html' => '',
			'show_button_close' => '0',
			'iframe_description' => 'Wir verwenden auf unserer Website externe Inhalte, um Ihnen zusätzliche Informationen anzubieten.',
			'iframe_html' => '',
			'iframe_replacement_html' => '',
			'iframe_whitelist_regex' => '',
			'banner_description' => 'Auf unserer Webseite werden Cookies verwendet. Einige davon werden zwingend benötigt, während es uns andere ermöglichen, Ihre Nutzererfahrung auf unserer Webseite zu verbessern.',
			'banner_html' => '',
			'essential_description' => 'Essentielle Cookies werden für grundlegende Funktionen der Webseite benötigt. Dadurch ist gewährleistet, dass die Webseite einwandfrei funktioniert.',
			'groups' => '',
			'set_cookie_for_domain' => '',
			'pid' => $pid,
		];

		$newOptinKey = StringUtility::getUniqueId('NewOptin');
		$data['tx_sgcookieoptin_domain_model_optin'][$newOptinKey] = $dataMapArray;

		$newCookieKey = StringUtility::getUniqueId('NewCookie');
		$data['tx_sgcookieoptin_domain_model_cookie'][$newCookieKey] = [
			'pid' => $pid,
			'name' => 'cookie_optin',
			'provider' => '',
			'purpose' => 'This cookie is used to store your cookie preferences for this website.',
			'lifetime' => '1 Year',
			'parent_optin' => $newOptinKey
		];

		$dataHandler = GeneralUtility::makeInstance(DataHandler::class);
		$dataHandler->start($data, []);
		$dataHandler->process_datamap();

		$newOptinId = $dataHandler->substNEWwithIDs[$newOptinKey];

		// Add cookie translations for each non-default language that is enabled for this site root
		$translatedCookiesData = [];
		$languages = LanguageService::getLanguages($pid);
		foreach ($languages as $language) {
			$languageUid = (int) $language['uid'];
			if ($languageUid <= 0) {
				continue;
			}

			// We are using the values from the old DataHandler call to avoid redundancy
			$thisLanguageKey = StringUtility::getUniqueId('New' . $languageUid);
			$translatedCookiesData['tx_sgcookieoptin_domain_model_cookie'][$thisLanguageKey]
				= $data['tx_sgcookieoptin_domain_model_cookie'][$newCookieKey];
			$translatedCookiesData['tx_sgcookieoptin_domain_model_cookie'][$thisLanguageKey]['l10n_parent']
				= $dataHandler->substNEWwithIDs[$newCookieKey];
			$translatedCookiesData['tx_sgcookieoptin_domain_model_cookie'][$thisLanguageKey]['sys_language_uid'] = $languageUid;
		}

		// Replace the $dataHandler object with a fresh one and add the cookies
		$dataHandler = GeneralUtility::makeInstance(DataHandler::class);
		$dataHandler->start($translatedCookiesData, []);
		$dataHandler->process_datamap();

		$this->redirectToTCAEdit($newOptinId);
	}
}
