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

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class EditLink
 **/
class EditOnClickViewHelper extends \SgCookieAbstractViewHelper {
	/**
	 * Register the ViewHelper arguments
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('table', 'string', 'The table for the clickenlarge link', TRUE);
		$this->registerArgument('uid', 'int', 'The uid of the record to clickenlarge', TRUE);
		$this->registerArgument('new', 'bool', 'Open a new record in the popup', FALSE, FALSE);
	}

	/**
	 * Renders the onclick script for editing a record
	 *
	 * @return string
	 * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
	 */
	public function render() {
		$params = '&edit[' . $this->arguments['table'] . '][' . $this->arguments['uid'] . ']='
			. ($this->arguments['new'] ? 'new' : 'edit');
		if (version_compare(VersionNumberUtility::getNumericTypo3Version(), '10.0.0', '>=')) {
			$uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
			$onclickScript = 'window.location.href=\'' . $uriBuilder->buildUriFromRoute(
				'record_edit'
			) . $params . '&returnUrl=' . rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')) . '\'';
		} else {
			$onclickScript = BackendUtility::editOnClick($params, '', -1);
		}
		return $onclickScript;
	}
}
