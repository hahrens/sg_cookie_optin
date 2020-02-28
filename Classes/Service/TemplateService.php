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

	const TEMPLATE_ID_DEFAULT = 0;
	const TEMPLATE_ID_NEW = 1;

	const BANNER_TEMPLATE_ID_DEFAULT = 0;

	const IFRAME_TEMPLATE_ID_DEFAULT = 0;

	const IFRAME_REPLACEMENT_TEMPLATE_ID_DEFAULT = 0;

	protected $templateIdMap = [
		self::TEMPLATE_ID_DEFAULT => 'Default',
		self::TEMPLATE_ID_NEW => 'New',
	];

	protected $bannerTemplateIdMap = [
		self::BANNER_TEMPLATE_ID_DEFAULT => 'Default',
	];

	protected $iframeTemplateIdMap = [
		self::IFRAME_TEMPLATE_ID_DEFAULT => 'Default',
	];

	protected $iframeReplacementTemplateIdMap = [
		self::IFRAME_REPLACEMENT_TEMPLATE_ID_DEFAULT => 'Default',
	];

	/**
	 * MinificationService constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$path = __DIR__ . '/../../Contrib/';
		require_once $path . 'mustache/src/Mustache/Autoloader.php';
		Mustache_Autoloader::register();
	}

	/**
	 * Returns a HTML markup out of the given template with the replaced markers by Mustache.
	 *
	 * @param string $template
	 * @param array $marker
	 *
	 * @return string
	 */
	public function renderTemplate($template, array $marker) {
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
	public function getContent($type, $templateId) {
		$content = '';
		switch ($type) {
			case self::TYPE_TEMPLATE:
				$content = $this->getTemplateContent($templateId);
				break;
			case self::TYPE_BANNER:
				$content = $this->getBannerContent($templateId);
				break;
			case self::TYPE_IFRAME:
				$content = $this->getIframeContent($templateId);
				break;
			case self::TYPE_IFRAME_REPLACEMENT:
				$content = $this->getIframeReplacementContent($templateId);
				break;
		}

		return $content;
	}

	/**
	 * Returns the content of one of the templates mapped by one of the constant id from this class.
	 *
	 * @param int $templateId
	 *
	 * @return string
	 */
	protected function getTemplateContent($templateId) {
		if (!isset($this->templateIdMap[$templateId])) {
			return '';
		}

		return $this->getFileContent($this->templateIdMap[$templateId], 'Template');
	}

	/**
	 * Returns the content of one of the templates mapped by one of the constant id from this class.
	 *
	 * @param int $bannerTemplateId
	 *
	 * @return string
	 */
	protected function getBannerContent($bannerTemplateId) {
		if (!isset($this->bannerTemplateIdMap[$bannerTemplateId])) {
			return '';
		}

		return $this->getFileContent($this->bannerTemplateIdMap[$bannerTemplateId], 'Banner');
	}

	/**
	 * Returns the content of one of the templates mapped by one of the constant id from this class.
	 *
	 * @param int $iframeTemplateId
	 *
	 * @return string
	 */
	protected function getIframeContent($iframeTemplateId) {
		if (!isset($this->iframeTemplateIdMap[$iframeTemplateId])) {
			return '';
		}

		return $this->getFileContent($this->iframeTemplateIdMap[$iframeTemplateId], 'Iframe');
	}

	/**
	 * Returns the content of one of the templates mapped by one of the constant id from this class.
	 *
	 * @param int $iframeReplacementId
	 *
	 * @return string
	 */
	protected function getIframeReplacementContent($iframeReplacementId) {
		if (!isset($this->iframeReplacementTemplateIdMap[$iframeReplacementId])) {
			return '';
		}

		return $this->getFileContent($this->iframeReplacementTemplateIdMap[$iframeReplacementId], 'IframeReplacement');
	}

	/**
	 * Returns the content of the searched template.
	 *
	 * @param string $name
	 * @param string $folder
	 *
	 * @return false|string
	 */
	protected function getFileContent($name, $folder) {
		$path = ExtensionManagementUtility::extPath('sg_cookie_optin') .
			'Resources/Private/Templates/Mustache/' . $folder . '/' . $name . '.html';
		if (!file_exists($path)) {
			return '';
		}

		return file_get_contents($path);
	}
}
