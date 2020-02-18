<?php

namespace SGalinski\SgCookieOptin\UserFunction;

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

use TYPO3\CMS\Backend\Form\Element\UserElement;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Adds the Cookie Optin JavaScript if it's generated for the current page.
 */
class RenderHTMLTemplate implements SingletonInterface {
	/**
	 * Renders the Mustache HTML editor of the given form.
	 *
	 * @param array $form
	 * @param UserElement $userElement
	 *
	 * @return string
	 */
	public function render(array $form, UserElement $userElement) {
		$formField = '<textarea name="' . $form['itemFormElName'] . '"';
		$formField .= ' id="' . $form['itemFormElID'] . '"';
		$formField .= ' class="text-monospace enable-tab t3editor" rows="10" wrap="off"';
		$formField .= ' style="width: 100%; display: none;"';
		$formField .= ' value="' . htmlspecialchars($form['itemFormElValue']) . '"';
		$formField .= ' onchange="' . htmlspecialchars(implode('', $form['fieldChangeFunc'])) . '"';
		$formField .= $this->getCodemirrorConfig();

		if ((bool) $form['row']['template_overwritten'] === FALSE) {
			$formField .= ' readonly ';
		}

		$formField .= ' >' . htmlspecialchars($form['itemFormElValue']) . '</textarea';
		return '<div class="form-control-wrap">
			<div class="form-wizards-wrap">
				<div class="form-wizards-element">
					<div class="t3editor-wrapper">' . $formField . '</div>
				</div>
			</div>
		</div>';
	}

	/**
	 * Returns the codemirror data attribute for a textarea.
	 *
	 * @return string
	 */
	protected function getCodemirrorConfig() {
		return ' data-codemirror-config="{
			\"mode\":\"cm/mode/htmlmixed/htmlmixed\",
			\"addons\":\"[
				\\\"cm/addon/dialog/dialog\\\",
				\\\"cm/addon/display/fullscreen\\\",
				\\\"cm/addon/display/autorefresh\\\",
				\\\"cm/addon/display/panel\\\",
				\\\"cm/addon/fold/xml-fold\\\",
				\\\"cm/addon/scroll/simplescrollbars\\\",
				\\\"cm/addon/scroll/annotatescrollbar\\\",
				\\\"cm/addon/search/searchcursor\\\",
				\\\"cm/addon/search/search\\\",
				\\\"cm/addon/search/jump-to-line\\\",
				\\\"cm/addon/search/matchesonscrollbar\\\",
				\\\"cm/addon/edit/matchbrackets\\\",
				\\\"cm/addon/edit/closebrackets\\\",
				\\\"cm/addon/selection/active-line\\\",
				\\\"cm/addon/edit/matchtags\\\",
				\\\"cm/addon/edit/closetag\\\",
				\\\"cm/addon/hint/show-hint\\\"
			]\",
			\"options\":\"{
				\\\"scrollbarStyle\\\":\\\"simple\\\",
				\\\"matchBrackets\\\":true,
				\\\"autoCloseBrackets\\\":true,
				\\\"styleActiveLine\\\":true,
				\\\"matchTags\\\":true,
				\\\"autoCloseTags\\\":true,
				\\\"hintOptions\\\":{
					\\\\\"completeSingle\\\\\":false
				}
			}\"
		}"';
	}
}
