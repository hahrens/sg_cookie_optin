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

use SGalinski\SgCookieOptin\Service\BackendService;
use SGalinski\SgCookieOptin\Service\JsonImportService;
use SGalinski\SgCookieOptin\Service\LanguageService;
use SGalinski\SgCookieOptin\Service\LicensingService;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\DocHeaderComponent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
		$typo3Version = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
		$keyState = LicensingService::checkKey();
		$isInDemoMode = LicensingService::isInDemoMode();
		if ($keyState !== LicensingService::STATE_LICENSE_VALID && $isInDemoMode) {
			// - 1 because the flash message would show 00:00:00 instead of 23:59:59
			$this->addFlashMessage(
				LocalizationUtility::translate(
					'backend.licenseKey.isInDemoMode.description', 'sg_cookie_optin', [
						date('H:i:s', mktime(0, 0, LicensingService::getRemainingTimeInDemoMode() - 1))
					]
				),
				LocalizationUtility::translate('backend.licenseKey.isInDemoMode.header', 'sg_cookie_optin'),
				AbstractMessage::INFO
			);
		} elseif ($keyState === LicensingService::STATE_LICENSE_INVALID) {
			LicensingService::removeAllCookieOptInFiles();

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
		} elseif ($keyState === LicensingService::STATE_LICENSE_NOT_SET) {
			LicensingService::removeAllCookieOptInFiles();

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

		$this->view->assign('pages', BackendService::getPages());
		$this->view->assign('typo3Version', $typo3Version);
		$this->view->assign('pageUid', $pageUid);
		$this->view->assign('invalidKey', $keyState !== LicensingService::STATE_LICENSE_VALID);
		$this->view->assign('showDemoButton', !$isInDemoMode && LicensingService::isDemoModeAcceptable());
	}

	/**
	 * Activates the demo mode for the given instance.
	 *
	 * @throws StopActionException
	 * @throws UnsupportedRequestTypeException
	 * @return void
	 */
	public function activateDemoModeAction() {
		if (LicensingService::isInDemoMode() || !LicensingService::isDemoModeAcceptable()) {
			$this->redirect('index');
		}

		LicensingService::activateDemoMode();
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
	 */
	public function importJsonAction() {
		$pid = (int) GeneralUtility::_GP('id');
		if (isset($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin'])) {
			try {
				$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
				$jsonImportService = $objectManager->get(JsonImportService::class);

				$languages = LanguageService::getLanguages($pid);
				// get and import the default language
				foreach ($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['name']['file'] as $key => $fileName) {
					if ($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['type']['file'][$key] !== 'application/json'
						|| $_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['error']['file'][$key] !== 0) {
						// TODO: what to do in case of an error
						continue;
					}
					$fileName = str_replace('.json', '', $fileName);
					$parts = explode('--', $fileName);
					$parts = array_reverse($parts);
					$languageId = (int) $parts[0];
					$locale = $parts[1];

					$defaultFound = FALSE;
					foreach ($languages as $language) {
						if ($language['uid'] === 0 && strpos($language['locale'], $locale) !== FALSE) {
							$defaultLanguageId = $languageId;
							$defaultFound = TRUE;
							break;
						}
					}

					if (!$defaultFound) {
						continue;
					}

					$defaultLanguageJsonData = json_decode(file_get_contents($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['tmp_name']['file'][$key]), TRUE);
					$defaultLanguageOptinId = $jsonImportService->importJsonData($defaultLanguageJsonData, $pid);
				}

				if (!$defaultFound) {
					throw new \Exception('Please upload the default language configuration file');
				}

				// import the other languages
				foreach ($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['name']['file'] as $key => $fileName) {
					if ($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['type']['file'][$key] !== 'application/json'
						|| $_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['error']['file'][$key] !== 0) {
						// TODO: what to do in case of an error
						continue;
					}

					$fileName = str_replace('.json', '', $fileName);
					$parts = explode('--', $fileName);
					$parts = array_reverse($parts);
					$languageId = (int) $parts[0];
					$locale = $parts[1];

					// we already imported that
					if ($languageId === $defaultLanguageId) {
						continue;
					}

					$jsonData = json_decode(file_get_contents($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin']['tmp_name']['file'][$key]), TRUE);
					$optInId = $jsonImportService->importJsonData($jsonData, $pid, $languageId, $defaultLanguageOptinId, $defaultLanguageJsonData);

					$this->redirectToEditAction($optInId);
				}
			} catch (Exception $exception) {
				// show error message
				throw $exception;
			}
		}
	}

	/**
	 * Redirects to the edit action
	 *
	 * @param $optinId
	 * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
	 */
	protected function redirectToEditAction($optinId) {
		$uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
		$params = [
			'edit' => ['tx_sgcookieoptin_domain_model_optin' => [$optInId => 'edit']],
			'returnUrl' => (string)$uriBuilder->buildUriFromRoute('web_SgCookieOptinOptin', ['id' => $pid])
		];
		$uri = (string)$uriBuilder->buildUriFromRoute('record_edit', $params);
		header('Location: ' . $uri);
	}

	/**
	 * Renders the upload JSON form
	 */
	public function uploadJsonAction() {
		// ToDo: do we need this check in every action? Move to trait?
		$typo3Version = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
		$keyState = LicensingService::checkKey();
		$isInDemoMode = LicensingService::isInDemoMode();
		if ($keyState !== LicensingService::STATE_LICENSE_VALID && $isInDemoMode) {
			// - 1 because the flash message would show 00:00:00 instead of 23:59:59
			$this->addFlashMessage(
				LocalizationUtility::translate(
					'backend.licenseKey.isInDemoMode.description', 'sg_cookie_optin', [
						date('H:i:s', mktime(0, 0, LicensingService::getRemainingTimeInDemoMode() - 1))
					]
				),
				LocalizationUtility::translate('backend.licenseKey.isInDemoMode.header', 'sg_cookie_optin'),
				AbstractMessage::INFO
			);
		} elseif ($keyState === LicensingService::STATE_LICENSE_INVALID) {
			LicensingService::removeAllCookieOptInFiles();

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
		} elseif ($keyState === LicensingService::STATE_LICENSE_NOT_SET) {
			LicensingService::removeAllCookieOptInFiles();

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

		$this->view->assign('pages', BackendService::getPages());
		$this->view->assign('typo3Version', $typo3Version);
		$this->view->assign('pageUid', $pageUid);
		$this->view->assign('invalidKey', $keyState !== LicensingService::STATE_LICENSE_VALID);
		$this->view->assign('showDemoButton', !$isInDemoMode && LicensingService::isDemoModeAcceptable());
	}

	/**
	 * Create an optin entry in the database and redirect to edit action
	 *
	 * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
	 * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
	 */
	public function createAction() {
		$pid = (int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id');
		// create with DataHandler
		$dataMapArray = array (
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
		);

		$data['tx_sgcookieoptin_domain_model_optin']['NEW9823be87'] = $dataMapArray;

		$newCookieKey = 'NEW' . md5(microtime());
		$data['tx_sgcookieoptin_domain_model_cookie'][$newCookieKey] = [
			'pid' => $pid,
			'name' => 'cookie_optin',
			'provider' => '',
			'purpose' => 'This cookie is used to store your cookie preferences for this website.',
			'lifetime' => '1 Year',
			'parent_optin' => 'NEW9823be87'
		];

		$dataHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
		$dataHandler->start($data, []);
		$dataHandler->process_datamap();

		$newOptinId = $dataHandler->substNEWwithIDs['NEW9823be87'];

		// Add cookie translations for each non-default language that is enabled for this site root
		$translatedCookiesData = [];
		$languages = LanguageService::getLanguages($pid);
		foreach ($languages as $language) {
			$languageUid = (int) $language['uid'];
			if ($languageUid <= 0) {
				continue;
			}

			// We are using the values from the old DataHandler call to avoid redundancy
			$thisLanguageKey = 'NEW' . $languageUid . md5(microtime());
			$translatedCookiesData['tx_sgcookieoptin_domain_model_cookie'][$thisLanguageKey]
				= $data['tx_sgcookieoptin_domain_model_cookie'][$newCookieKey];
			$translatedCookiesData['tx_sgcookieoptin_domain_model_cookie'][$thisLanguageKey]['l10n_parent']
				= $dataHandler->substNEWwithIDs[$newCookieKey];
			$translatedCookiesData['tx_sgcookieoptin_domain_model_cookie'][$thisLanguageKey]['sys_language_uid'] = $languageUid;
		}

		// Replace the $dataHandler object with a fresh one and add the cookies
		$dataHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
		$dataHandler->start($translatedCookiesData, []);
		$dataHandler->process_datamap();

		$this->redirectToEditAction($newOptinId);
	}
}
