<?php

namespace SGalinski\SgCookieOptin\ViewHelpers\Backend;

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
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Type\BitSet;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

/**
 * Class ControlViewHelper
 **/
class ControlViewHelper extends \SgCookieAbstractViewHelper {

	/**
	 * Initialize the ViewHelper arguments
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('table', 'string', 'The table to control', TRUE);
		$this->registerArgument('row', 'array', 'The row of the record', TRUE);
	}

	/**
	 * Renders the control buttons for the specified record
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 * @throws \UnexpectedValueException
	 */
	public function render() {
		$table = $this->arguments['table'];
		$row = $this->arguments['row'];

		$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		$pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/AjaxDataHandler');
		$pageRenderer->addInlineLanguageLabelFile('EXT:backend/Resources/Private/Language/locallang_alt_doc.xlf');

		$currentTypo3Version = VersionNumberUtility::getCurrentTypo3Version();
		if (version_compare($currentTypo3Version, '9.0.0', '<')) {
			$languageService = GeneralUtility::makeInstance(\TYPO3\CMS\Lang\LanguageService::class);
		} else {
			$languageService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Localization\LanguageService::class);
		}
		$languageService->includeLLFile('EXT:backend/Resources/Private/Language/locallang_alt_doc.xlf');

		/** @var DatabaseRecordList $databaseRecordList */
		$databaseRecordList = GeneralUtility::makeInstance(DatabaseRecordList::class);
		$pageInfo = BackendUtility::readPageAccess($row['pid'], $GLOBALS['BE_USER']->getPagePermsClause(1));
        if (version_compare($currentTypo3Version, '11.0.0', '<')) {
            $databaseRecordList->calcPerms = $GLOBALS['BE_USER']->calcPerms($pageInfo);
        } else {
            $permission = new Permission();
            $permission->set($GLOBALS['BE_USER']->calcPerms($pageInfo));
            $databaseRecordList->calcPerms = $permission;
        }

		if (version_compare($currentTypo3Version, '7.0.0', '<')
			&& ExtensionManagementUtility::isLoaded('gridelements')) {
			// in old versions of gridelements the "makeControl" function
			// was xclassed with a 3rd (mandatory) parameter "$level"
			// @see gridelements/Classes/Xclass/DatabaseRecordList.php
			return $databaseRecordList->makeControl($table, $row, 0);
		} else {
			return $databaseRecordList->makeControl($table, $row);
		}
	}
}
