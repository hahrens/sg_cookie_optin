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
use TYPO3\CMS\Backend\Controller\EditDocumentController;
use TYPO3\CMS\Backend\Template\Components\DocHeaderComponent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
		// create doc header component
		$pageUid = (int) GeneralUtility::_GP('id');
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
		if ($pageInfo && (int) $pageInfo['is_siteroot'] === 1) {
			$this->view->assign('isSiteRoot', TRUE);
			$this->view->assign('optins', BackendService::getOptins($pageUid));
		}

		$this->docHeaderComponent = GeneralUtility::makeInstance(DocHeaderComponent::class);
		if ($pageInfo === FALSE) {
			$pageInfo = ['uid' => $pageUid];
		}
		$this->docHeaderComponent->setMetaInformation($pageInfo);
		BackendService::makeButtons($this->docHeaderComponent, $this->request);

		$this->view->assign('pages', BackendService::getPages());
		$this->view->assign('docHeader', $this->docHeaderComponent->docHeaderContent());
		$this->view->assign('typo3Version', VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version));
		$this->view->assign('pageUid', $pageUid);
	}
}
