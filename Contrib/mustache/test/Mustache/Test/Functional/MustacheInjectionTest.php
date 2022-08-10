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
 * @group mustache_injection
 * @group functional
 */
class Mustache_Test_Functional_MustacheInjectionTest extends PHPUnit_Framework_TestCase {
	private $mustache;

	public function setUp() {
		$this->mustache = new Mustache_Engine();
	}

	/**
	 * @dataProvider injectionData
	 */
	public function testInjection($tpl, $data, $partials, $expect) {
		$this->mustache->setPartials($partials);
		$this->assertEquals($expect, $this->mustache->render($tpl, $data));
	}

	public function injectionData() {
		$interpolationData = [
			'a' => '{{ b }}',
			'b' => 'FAIL',
		];

		$sectionData = [
			'a' => TRUE,
			'b' => '{{ c }}',
			'c' => 'FAIL',
		];

		$lambdaInterpolationData = [
			'a' => [$this, 'lambdaInterpolationCallback'],
			'b' => '{{ c }}',
			'c' => 'FAIL',
		];

		$lambdaSectionData = [
			'a' => [$this, 'lambdaSectionCallback'],
			'b' => '{{ c }}',
			'c' => 'FAIL',
		];

		return [
			['{{ a }}',   $interpolationData, [], '{{ b }}'],
			['{{{ a }}}', $interpolationData, [], '{{ b }}'],

			['{{# a }}{{ b }}{{/ a }}',   $sectionData, [], '{{ c }}'],
			['{{# a }}{{{ b }}}{{/ a }}', $sectionData, [], '{{ c }}'],

			['{{> partial }}', $interpolationData, ['partial' => '{{ a }}'],   '{{ b }}'],
			['{{> partial }}', $interpolationData, ['partial' => '{{{ a }}}'], '{{ b }}'],

			['{{ a }}',           $lambdaInterpolationData, [], '{{ c }}'],
			['{{# a }}b{{/ a }}', $lambdaSectionData,       [], '{{ c }}'],
		];
	}

	public static function lambdaInterpolationCallback() {
		return '{{ b }}';
	}

	public static function lambdaSectionCallback($text) {
		return '{{ ' . $text . ' }}';
	}
}
