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
 * @group filters
 * @group functional
 */
class Mustache_Test_FiveThree_Functional_FiltersTest extends PHPUnit_Framework_TestCase {
	private $mustache;

	public function setUp() {
		$this->mustache = new Mustache_Engine();
	}

	/**
	 * @dataProvider singleFilterData
	 */
	public function testSingleFilter($tpl, $helpers, $data, $expect) {
		$this->mustache->setHelpers($helpers);
		$this->assertEquals($expect, $this->mustache->render($tpl, $data));
	}

	public function singleFilterData() {
		$helpers = [
			'longdate' => function (\DateTime $value) {
				return $value->format('Y-m-d h:m:s');
			},
			'echo' => function ($value) {
				return [$value, $value, $value];
			},
		];

		return [
			[
				'{{% FILTERS }}{{ date | longdate }}',
				$helpers,
				(object) ['date' => new DateTime('1/1/2000', new DateTimeZone('UTC'))],
				'2000-01-01 12:01:00',
			],

			[
				'{{% FILTERS }}{{# word | echo }}{{ . }}!{{/ word | echo }}',
				$helpers,
				['word' => 'bacon'],
				'bacon!bacon!bacon!',
			],
		];
	}

	public function testChainedFilters() {
		$tpl = $this->mustache->loadTemplate('{{% FILTERS }}{{ date | longdate | withbrackets }}');

		$this->mustache->addHelper('longdate', function (\DateTime $value) {
			return $value->format('Y-m-d h:m:s');
		});

		$this->mustache->addHelper('withbrackets', function ($value) {
			return sprintf('[[%s]]', $value);
		});

		$foo = new \StdClass();
		$foo->date = new DateTime('1/1/2000', new DateTimeZone('UTC'));

		$this->assertEquals('[[2000-01-01 12:01:00]]', $tpl->render($foo));
	}

	const CHAINED_SECTION_FILTERS_TPL = <<<'EOS'
{{% FILTERS }}
{{# word | echo | with_index }}
{{ key }}: {{ value }}
{{/ word | echo | with_index }}
EOS;

	public function testChainedSectionFilters() {
		$tpl = $this->mustache->loadTemplate(self::CHAINED_SECTION_FILTERS_TPL);

		$this->mustache->addHelper('echo', function ($value) {
			return [$value, $value, $value];
		});

		$this->mustache->addHelper('with_index', function ($value) {
			return array_map(function ($k, $v) {
				return [
					'key' => $k,
					'value' => $v,
				];
			}, array_keys($value), $value);
		});

		$this->assertEquals("0: bacon\n1: bacon\n2: bacon\n", $tpl->render(['word' => 'bacon']));
	}

	/**
	 * @dataProvider interpolateFirstData
	 */
	public function testInterpolateFirst($tpl, $data, $expect) {
		$this->assertEquals($expect, $this->mustache->render($tpl, $data));
	}

	public function interpolateFirstData() {
		$data = [
			'foo' => 'FOO',
			'bar' => function ($value) {
				return ($value === 'FOO') ? 'win!' : 'fail :(';
			},
		];

		return [
			['{{% FILTERS }}{{ foo | bar }}',                         $data, 'win!'],
			['{{% FILTERS }}{{# foo | bar }}{{ . }}{{/ foo | bar }}', $data, 'win!'],
		];
	}

	/**
	 * @expectedException Mustache_Exception_UnknownFilterException
	 * @dataProvider brokenPipeData
	 */
	public function testThrowsExceptionForBrokenPipes($tpl, $data) {
		$this->mustache->render($tpl, $data);
	}

	public function brokenPipeData() {
		return [
			['{{% FILTERS }}{{ foo | bar }}',       []],
			['{{% FILTERS }}{{ foo | bar }}',       ['foo' => 'FOO']],
			['{{% FILTERS }}{{ foo | bar }}',       ['foo' => 'FOO', 'bar' => 'BAR']],
			['{{% FILTERS }}{{ foo | bar }}',       ['foo' => 'FOO', 'bar' => [1, 2]]],
			['{{% FILTERS }}{{ foo | bar | baz }}', ['foo' => 'FOO', 'bar' => function () {
				return 'BAR';
			}]],
			['{{% FILTERS }}{{ foo | bar | baz }}', ['foo' => 'FOO', 'baz' => function () {
				return 'BAZ';
			}]],
			['{{% FILTERS }}{{ foo | bar | baz }}', ['bar' => function () {
				return 'BAR';
			}]],
			['{{% FILTERS }}{{ foo | bar | baz }}', ['baz' => function () {
				return 'BAZ';
			}]],
			['{{% FILTERS }}{{ foo | bar.baz }}',   ['foo' => 'FOO', 'bar' => function () {
				return 'BAR';
			}, 'baz' => function () {
				return 'BAZ';
			}]],

			['{{% FILTERS }}{{# foo | bar }}{{ . }}{{/ foo | bar }}',             []],
			['{{% FILTERS }}{{# foo | bar }}{{ . }}{{/ foo | bar }}',             ['foo' => 'FOO']],
			['{{% FILTERS }}{{# foo | bar }}{{ . }}{{/ foo | bar }}',             ['foo' => 'FOO', 'bar' => 'BAR']],
			['{{% FILTERS }}{{# foo | bar }}{{ . }}{{/ foo | bar }}',             ['foo' => 'FOO', 'bar' => [1, 2]]],
			['{{% FILTERS }}{{# foo | bar | baz }}{{ . }}{{/ foo | bar | baz }}', ['foo' => 'FOO', 'bar' => function () {
				return 'BAR';
			}]],
			['{{% FILTERS }}{{# foo | bar | baz }}{{ . }}{{/ foo | bar | baz }}', ['foo' => 'FOO', 'baz' => function () {
				return 'BAZ';
			}]],
			['{{% FILTERS }}{{# foo | bar | baz }}{{ . }}{{/ foo | bar | baz }}', ['bar' => function () {
				return 'BAR';
			}]],
			['{{% FILTERS }}{{# foo | bar | baz }}{{ . }}{{/ foo | bar | baz }}', ['baz' => function () {
				return 'BAZ';
			}]],
			['{{% FILTERS }}{{# foo | bar.baz }}{{ . }}{{/ foo | bar.baz }}',     ['foo' => 'FOO', 'bar' => function () {
				return 'BAR';
			}, 'baz' => function () {
				return 'BAZ';
			}]],
		];
	}
}
