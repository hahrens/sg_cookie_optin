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

use SGalinski\SgMail\ViewHelpers\AbstractViewHelper;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class IconViewHelper
 **/
class IconViewHelper extends AbstractViewHelper {

	/**
	 * Register the ViewHelper arguments
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('table', 'string', 'The table for the icon', TRUE);
		$this->registerArgument('row', 'array', 'The row of the record', TRUE);
		$this->registerArgument('clickMenu', 'bool', 'Render a clickMenu around the icon', FALSE, TRUE);
	}

	/**
	 * Renders the icon for the specified record
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function render() {
		$row = $this->arguments['row'];
		$table = $this->arguments['table'];
		$clickMenu = $this->arguments['clickMenu'];
		/** @var IconFactory $iconFactory */
		$iconFactory = GeneralUtility::makeInstance(IconFactory::class);
		$toolTip = BackendUtility::getRecordToolTip($row, $table);
		$iconImg = '<span ' . $toolTip . '>'
			. $iconFactory->getIconForRecord($table, $row, Icon::SIZE_SMALL)->render()
			. '</span>';
		if ($clickMenu) {
			return BackendUtility::wrapClickMenuOnIcon($iconImg, $table, $row['uid']);
		}

		return $iconImg;
	}
}
