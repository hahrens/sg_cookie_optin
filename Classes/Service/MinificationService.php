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

use MatthiasMullie\Minify;
use Patchwork\JSqueeze;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class SGalinski\SgCookieOptin\Service\MinificationService
 */
class MinificationService implements SingletonInterface {
	/**
	 * MinificationService constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$path = __DIR__ . '/../../Contrib/';
		require_once $path . 'minify/src/Minify.php';
		require_once $path . 'minify/src/CSS.php';
		require_once $path . 'minify/src/JS.php';
		require_once $path . 'minify/src/Exception.php';
		require_once $path . 'minify/src/Exceptions/BasicException.php';
		require_once $path . 'minify/src/Exceptions/FileImportException.php';
		require_once $path . 'minify/src/Exceptions/IOException.php';
		require_once $path . 'path-converter/src/ConverterInterface.php';
		require_once $path . 'path-converter/src/Converter.php';
		require_once $path . 'jsqueeze/src/JSqueeze.php';
	}

	/**
	 * Minifies the given Java Script file and returns the result.
	 *
	 * @param string $file
	 * @return boolean
	 */
	public function minifyJavaScriptFile($file) {
		if (!file_exists($file)) {
			return FALSE;
		}

		$javaScript = file_get_contents($file);
		if ($javaScript === '') {
			return FALSE;
		}

		$JSqueeze = new JSqueeze();
		$minifier = new Minify\JS();
		$minifier->add($JSqueeze->squeeze($javaScript));
		$content = $minifier->minify($file);
		return ($content !== '');
	}

	/**
	 * Minifies the given CSS file and returns the result.
	 *
	 * @param string $file
	 * @return boolean
	 */
	public function minifyCSSFile($file) {
		if (!file_exists($file)) {
			return FALSE;
		}

		$minifier = new Minify\CSS($file);
		$content = $minifier->minify($file);
		return ($content !== '');
	}
}
