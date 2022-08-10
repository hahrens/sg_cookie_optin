<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2017 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Mustache_Test_HelperCollectionTest extends PHPUnit_Framework_TestCase {
	public function testConstructor() {
		$foo = [$this, 'getFoo'];
		$bar = 'BAR';

		$helpers = new Mustache_HelperCollection([
			'foo' => $foo,
			'bar' => $bar,
		]);

		$this->assertSame($foo, $helpers->get('foo'));
		$this->assertSame($bar, $helpers->get('bar'));
	}

	public static function getFoo() {
		echo 'foo';
	}

	public function testAccessorsAndMutators() {
		$foo = [$this, 'getFoo'];
		$bar = 'BAR';

		$helpers = new Mustache_HelperCollection();
		$this->assertTrue($helpers->isEmpty());
		$this->assertFalse($helpers->has('foo'));
		$this->assertFalse($helpers->has('bar'));

		$helpers->add('foo', $foo);
		$this->assertFalse($helpers->isEmpty());
		$this->assertTrue($helpers->has('foo'));
		$this->assertFalse($helpers->has('bar'));

		$helpers->add('bar', $bar);
		$this->assertFalse($helpers->isEmpty());
		$this->assertTrue($helpers->has('foo'));
		$this->assertTrue($helpers->has('bar'));

		$helpers->remove('foo');
		$this->assertFalse($helpers->isEmpty());
		$this->assertFalse($helpers->has('foo'));
		$this->assertTrue($helpers->has('bar'));
	}

	public function testMagicMethods() {
		$foo = [$this, 'getFoo'];
		$bar = 'BAR';

		$helpers = new Mustache_HelperCollection();
		$this->assertTrue($helpers->isEmpty());
		$this->assertFalse($helpers->has('foo'));
		$this->assertFalse($helpers->has('bar'));
		$this->assertFalse(isset($helpers->foo));
		$this->assertFalse(isset($helpers->bar));

		$helpers->foo = $foo;
		$this->assertFalse($helpers->isEmpty());
		$this->assertTrue($helpers->has('foo'));
		$this->assertFalse($helpers->has('bar'));
		$this->assertTrue(isset($helpers->foo));
		$this->assertFalse(isset($helpers->bar));

		$helpers->bar = $bar;
		$this->assertFalse($helpers->isEmpty());
		$this->assertTrue($helpers->has('foo'));
		$this->assertTrue($helpers->has('bar'));
		$this->assertTrue(isset($helpers->foo));
		$this->assertTrue(isset($helpers->bar));

		unset($helpers->foo);
		$this->assertFalse($helpers->isEmpty());
		$this->assertFalse($helpers->has('foo'));
		$this->assertTrue($helpers->has('bar'));
		$this->assertFalse(isset($helpers->foo));
		$this->assertTrue(isset($helpers->bar));
	}

	/**
	 * @dataProvider getInvalidHelperArguments
	 */
	public function testHelperCollectionIsntAfraidToThrowExceptions($helpers = [], $actions = [], $exception = NULL) {
		if ($exception) {
			$this->setExpectedException($exception);
		}

		$helpers = new Mustache_HelperCollection($helpers);

		foreach ($actions as $method => $args) {
			call_user_func_array([$helpers, $method], $args);
		}
	}

	public function getInvalidHelperArguments() {
		return [
			[
				'not helpers',
				[],
				'InvalidArgumentException',
			],
			[
				[],
				['get' => ['foo']],
				'InvalidArgumentException',
			],
			[
				['foo' => 'FOO'],
				['get' => ['foo']],
				NULL,
			],
			[
				['foo' => 'FOO'],
				['get' => ['bar']],
				'InvalidArgumentException',
			],
			[
				['foo' => 'FOO'],
				[
					'add' => ['bar', 'BAR'],
					'get' => ['bar'],
				],
				NULL,
			],
			[
				['foo' => 'FOO'],
				[
					'get' => ['foo'],
					'remove' => ['foo'],
				],
				NULL,
			],
			[
				['foo' => 'FOO'],
				[
					'remove' => ['foo'],
					'get' => ['foo'],
				],
				'InvalidArgumentException',
			],
			[
				[],
				['remove' => ['foo']],
				'InvalidArgumentException',
			],
		];
	}
}
