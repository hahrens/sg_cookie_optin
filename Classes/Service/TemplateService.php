<?php

namespace SGalinski\SgCookieOptin\Service;

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

use Mustache_Autoloader;
use Mustache_Engine;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class SGalinski\SgCookieOptin\Service\TemplateService
 */
class TemplateService implements SingletonInterface {
	const TYPE_TEMPLATE = 0;
	const TYPE_BANNER = 1;
	const TYPE_IFRAME = 2;
	const TYPE_IFRAME_REPLACEMENT = 3;
	const TYPE_IFRAME_WHITELIST = 4;

	const TEMPLATE_ID_DEFAULT = 0;
	const TEMPLATE_ID_NEW = 1;

	const BANNER_TEMPLATE_ID_DEFAULT = 0;
	const IFRAME_TEMPLATE_ID_DEFAULT = 0;
	const IFRAME_REPLACEMENT_TEMPLATE_ID_DEFAULT = 0;
	const IFRAME_WHITELIST_TEMPLATE_ID_DEFAULT = 0;

	protected static $templateIdToNameMap = [
		self::TYPE_TEMPLATE => [
			self::TEMPLATE_ID_DEFAULT => 'Default',
			self::TEMPLATE_ID_NEW => 'Full',
		],
		self::TYPE_BANNER => [
			self::BANNER_TEMPLATE_ID_DEFAULT => 'Default',
		],
		self::TYPE_IFRAME => [
			self::IFRAME_TEMPLATE_ID_DEFAULT => 'Default',
		],
		self::TYPE_IFRAME_REPLACEMENT => [
			self::IFRAME_REPLACEMENT_TEMPLATE_ID_DEFAULT => 'Default',
		],
		self::TYPE_IFRAME_WHITELIST => [
			self::IFRAME_WHITELIST_TEMPLATE_ID_DEFAULT => 'Default',
		],
	];

	protected static $templateIdToFolderMap = [
		self::TYPE_TEMPLATE => 'Template',
		self::TYPE_BANNER => 'Banner',
		self::TYPE_IFRAME => 'Iframe',
		self::TYPE_IFRAME_REPLACEMENT => 'IframeReplacement',
		self::TYPE_IFRAME_WHITELIST => 'IframeWhitelist',
	];

	/**
	 * Returns a HTML markup out of the given template with the replaced markers by Mustache.
	 *
	 * @param string $template
	 * @param array $marker
	 *
	 * @return string
	 */
	public function renderTemplate($template, array $marker) {
		if (!class_exists(Mustache_Engine::class)) {
			$path = __DIR__ . '/../../Contrib/';
			require_once $path . 'mustache/src/Mustache/Autoloader.php';
			Mustache_Autoloader::register();
		}

		if ($template === '') {
			return '';
		}

		$mustacheEngine = new Mustache_Engine;
		return $mustacheEngine->render($template, $marker);
	}

	/**
	 * Returns the content of one of the templates mapped by one of the constant id from this class.
	 *
	 * @param int $type
	 * @param int $templateId
	 *
	 * @return string
	 */
	public function getMustacheContent($type, $templateId) {
		if (
		!isset(self::$templateIdToFolderMap[$type], self::$templateIdToNameMap[$type][$templateId])
		) {
			return '';
		}

		return $this->getHTMLFileContent(
			self::$templateIdToNameMap[$type][$templateId], self::$templateIdToFolderMap[$type]
		);
	}

	/**
	 * Returns the content of the searched template.
	 *
	 * @param string $name
	 * @param string $folder
	 *
	 * @return false|string
	 */
	protected function getHTMLFileContent($name, $folder) {
		$path = ExtensionManagementUtility::extPath('sg_cookie_optin') .
			'Resources/Private/Templates/Mustache/' . $folder . '/' . $name . '.html';
		if (!file_exists($path)) {
			return '';
		}

		return file_get_contents($path);
	}

	/**
	 * Returns the content of one of the templates mapped by one of the constant id from this class.
	 *
	 * @param int $type
	 * @param int $templateId
	 *
	 * @return string
	 */
	public function getCSSContent($type, $templateId) {
		if (!isset(self::$templateIdToFolderMap[$type])) {
			return '';
		}

		$content = '/* File: ' . self::$templateIdToNameMap[$type][0] . " */\n\n" .
			$this->getCSSFileContent(
				self::$templateIdToNameMap[$type][0], self::$templateIdToFolderMap[$type]
			);
		if ($templateId > 0) {
			if (!isset(self::$templateIdToNameMap[$type][$templateId])) {
				return $content;
			}

			$content .= '/* File: ' . self::$templateIdToNameMap[$type][$templateId] . " */\n\n" .
				$this->getCSSFileContent(
					self::$templateIdToNameMap[$type][$templateId], self::$templateIdToFolderMap[$type]
				);
		}

		return $content;
	}

	/**
	 * Returns the content of the searched template.
	 *
	 * @param string $name
	 * @param string $folder
	 *
	 * @return false|string
	 */
	protected function getCSSFileContent($name, $folder) {
		$path = ExtensionManagementUtility::extPath('sg_cookie_optin') .
			'Resources/Public/StyleSheets/Mustache/' . $folder . '/' . $name . '.css';
		if (!file_exists($path)) {
			return '';
		}

		return file_get_contents($path);
	}
}
