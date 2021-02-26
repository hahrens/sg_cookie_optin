<?php

namespace SGalinski\SgCookieOptin\ViewHelpers\Be\Menus;

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

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Class ActionMenuItemViewHelper
 *
 * This is just a re-implemntation of the core view helper with the same name
 * only difference so far is the selected option is not automatically set if you cann the same action&controller
 *
 * @package SGalinski\SgCookieOptin\ViewHelpers\Be\Menus
 */
class ActionMenuItemViewHelper extends AbstractTagBasedViewHelper {
	/**
	 * @var string
	 */
	protected $tagName = 'option';

	/**
	 * Register the ViewHelper arguments
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('label', 'string', 'The label of the option tag', TRUE);
		$this->registerArgument(
			'controller', 'string', 'The controller to be associated with this ActionMenuItem', TRUE
		);
		$this->registerArgument('action', 'string', 'The action to be associated with this ActionMenuItem', TRUE);
		$this->registerArgument(
			'arguments', 'array',
			'Additional controller arguments to be passed to the action when this ActionMenuItem is selected', FALSE, []
		);
		$this->registerArgument('selected', 'bool', 'True if the option item should be selected', FALSE, FALSE);
		$this->registerArgument('simpleActionItem', 'bool', 'True if the option is a simple action item', FALSE, FALSE);
		$this->registerArgument('section', 'string', 'The section where this is rendered', FALSE, FALSE);
	}

	/**
	 * Renders an ActionMenu option tag
	 *
	 * @return string the rendered option tag
	 * @see \TYPO3\CMS\Fluid\ViewHelpers\Be\Menus\ActionMenuViewHelper
	 */
	public function render() {
		$label = $this->arguments['label'];
		$controller = $this->arguments['controller'];
		$action = $this->arguments['action'];
		$arguments = $this->arguments['arguments'];
		$uriBuilder = $this->renderingContext->getControllerContext()->getUriBuilder();
		$uri = $uriBuilder->reset()->uriFor($action, $arguments, $controller);
		$this->tag->addAttribute('value', $uri);
		$currentRequest = $this->renderingContext->getControllerContext()->getRequest();
		$requestArguments = $currentRequest->getArguments();
		unset($requestArguments['filters']);
		$requestArguments = ArrayUtility::flatten(
			array_merge(
				[
					'controller' => $currentRequest->getControllerName(),
					'action' => $currentRequest->getControllerActionName()
				], $requestArguments
			)
		);

		if (!$arguments) {
			$arguments = [];
		}

		$viewHelperArguments = ArrayUtility::flatten(
			array_merge(['controller' => $controller, 'action' => $action], $arguments)
		);
		$selected = FALSE;
		if ($this->arguments['section'] === 'actionMenu') {
			$selected = $this->arguments['selected'] ||
				(
					$this->arguments['simpleActionItem'] &&
					array_diff(
						['controller' => $requestArguments['controller'], 'action' => $requestArguments['action']],
						['controller' => $controller, 'action' => $action]
					) === []
				);
		} else {
			$selected = $this->arguments['selected'] ||
				(
					$this->arguments['simpleActionItem'] &&
					array_diff($requestArguments, $viewHelperArguments) === []
				);
		}
		if ($selected) {
			$this->tag->addAttribute('selected', 'selected');
		} else {
			$this->tag->removeAttribute('selected');
		}
		$this->tag->setContent($label);

		return $this->tag->render();
	}
}
