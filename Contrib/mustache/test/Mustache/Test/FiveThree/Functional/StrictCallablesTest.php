<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2017 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @group lambdas
 * @group functional
 */
class Mustache_Test_FiveThree_Functional_StrictCallablesTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider callables
	 */
	public function testStrictCallables($strict, $name, $section, $expected) {
		$mustache = new Mustache_Engine(['strict_callables' => $strict]);
		$tpl = $mustache->loadTemplate('{{# section }}{{ name }}{{/ section }}');

		$data = new StdClass();
		$data->name = $name;
		$data->section = $section;

		$this->assertEquals($expected, $tpl->render($data));
	}

	public function callables() {
		$lambda = function ($tpl, $mustache) {
			return strtoupper($mustache->render($tpl));
		};

		return [
			// Interpolation lambdas
			[
				FALSE,
				[$this, 'instanceName'],
				$lambda,
				'YOSHI',
			],
			[
				FALSE,
				[__CLASS__, 'staticName'],
				$lambda,
				'YOSHI',
			],
			[
				FALSE,
				function () {
					return 'Yoshi';
				},
				$lambda,
				'YOSHI',
			],

			// Section lambdas
			[
				FALSE,
				'Yoshi',
				[$this, 'instanceCallable'],
				'YOSHI',
			],
			[
				FALSE,
				'Yoshi',
				[__CLASS__, 'staticCallable'],
				'YOSHI',
			],
			[
				FALSE,
				'Yoshi',
				$lambda,
				'YOSHI',
			],

			// Strict interpolation lambdas
			[
				TRUE,
				function () {
					return 'Yoshi';
				},
				$lambda,
				'YOSHI',
			],

			// Strict section lambdas
			[
				TRUE,
				'Yoshi',
				[$this, 'instanceCallable'],
				'YoshiYoshi',
			],
			[
				TRUE,
				'Yoshi',
				[__CLASS__, 'staticCallable'],
				'YoshiYoshi',
			],
			[
				TRUE,
				'Yoshi',
				function ($tpl, $mustache) {
					return strtoupper($mustache->render($tpl));
				},
				'YOSHI',
			],
		];
	}

	public function instanceCallable($tpl, $mustache) {
		return strtoupper($mustache->render($tpl));
	}

	public static function staticCallable($tpl, $mustache) {
		return strtoupper($mustache->render($tpl));
	}

	public function instanceName() {
		return 'Yoshi';
	}

	public static function staticName() {
		return 'Yoshi';
	}
}
