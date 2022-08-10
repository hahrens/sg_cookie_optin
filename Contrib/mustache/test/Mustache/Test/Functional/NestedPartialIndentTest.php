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
 * @group functional
 * @group partials
 */
class Mustache_Test_Functional_NestedPartialIndentTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider partialsAndStuff
	 */
	public function testNestedPartialsAreIndentedProperly($src, array $partials, $expected) {
		$m = new Mustache_Engine([
			'partials' => $partials,
		]);
		$tpl = $m->loadTemplate($src);
		$this->assertEquals($expected, $tpl->render());
	}

	public function partialsAndStuff() {
		$partials = [
			'a' => ' {{> b }}',
			'b' => ' {{> d }}',
			'c' => ' {{> d }}{{> d }}',
			'd' => 'D!',
		];

		return [
			[' {{> a }}', $partials, '   D!'],
			[' {{> b }}', $partials, '  D!'],
			[' {{> c }}', $partials, '  D!D!'],
		];
	}
}
