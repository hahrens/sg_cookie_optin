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
use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use SGalinski\SgCookieOptin\Traits\InitControllerComponents;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\DocHeaderComponent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
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
 * Consent Controller
 */
class StatisticsController extends ActionController {

	use InitControllerComponents;

	/**
	 * DocHeaderComponent
	 *
	 * @var DocHeaderComponent
	 */
	protected $docHeaderComponent;

	/**
	 * Displays the user preference statistics
	 */
	public function indexAction() {
		$this->initComponents();
	}
}
