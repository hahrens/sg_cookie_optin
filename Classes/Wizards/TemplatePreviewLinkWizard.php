<?php

namespace SGalinski\SgCookieOptin\Wizards;

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

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Adds a button, which shows the preview link of the given template.
 */
class TemplatePreviewLinkWizard extends AbstractNode {
	/**
	 * Renders the preview link button.
	 *
	 * @return array
	 */
	public function render() {
		$result = [];
		$label = LocalizationUtility::translate('backend.wizard.templatePreviewLink', 'sg_cookie_optin');
		$result['html'] = '<div>
			<button type="button"
					class="btn btn-default"
					style="margin: 10px 0;"
					onclick="window.open(window.location.origin + \'?showOptIn=1\', \'_blank\')">' . $label .
			'</button>
		</div>';
		return $result;
	}
}
