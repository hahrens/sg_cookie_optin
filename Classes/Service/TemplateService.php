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

/**
 * Class SGalinski\SgCookieOptin\Service\TemplateService
 */
class TemplateService implements SingletonInterface {
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
}
