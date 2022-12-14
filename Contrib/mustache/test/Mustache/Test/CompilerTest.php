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
 * @group unit
 */
class Mustache_Test_CompilerTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider getCompileValues
	 */
	public function testCompile($source, array $tree, $name, $customEscaper, $entityFlags, $charset, $expected) {
		$compiler = new Mustache_Compiler();

		$compiled = $compiler->compile($source, $tree, $name, $customEscaper, $charset, FALSE, $entityFlags);
		foreach ($expected as $contains) {
			$this->assertContains($contains, $compiled);
		}
	}

	public function getCompileValues() {
		return [
			['', [], 'Banana', FALSE, ENT_COMPAT, 'ISO-8859-1', [
				"\nclass Banana extends Mustache_Template",
				'return $buffer;',
			]],

			['', [$this->createTextToken('TEXT')], 'Monkey', FALSE, ENT_COMPAT, 'UTF-8', [
				"\nclass Monkey extends Mustache_Template",
				'$buffer .= $indent . \'TEXT\';',
				'return $buffer;',
			]],

			[
				'',
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_ESCAPED,
						Mustache_Tokenizer::NAME => 'name',
					],
				],
				'Monkey',
				TRUE,
				ENT_COMPAT,
				'ISO-8859-1',
				[
					"\nclass Monkey extends Mustache_Template",
					'$value = $this->resolveValue($context->find(\'name\'), $context);',
					'$buffer .= $indent . call_user_func($this->mustache->getEscape(), $value);',
					'return $buffer;',
				],
			],

			[
				'',
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_ESCAPED,
						Mustache_Tokenizer::NAME => 'name',
					],
				],
				'Monkey',
				FALSE,
				ENT_COMPAT,
				'ISO-8859-1',
				[
					"\nclass Monkey extends Mustache_Template",
					'$value = $this->resolveValue($context->find(\'name\'), $context);',
					'$buffer .= $indent . htmlspecialchars($value, ' . ENT_COMPAT . ', \'ISO-8859-1\');',
					'return $buffer;',
				],
			],

			[
				'',
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_ESCAPED,
						Mustache_Tokenizer::NAME => 'name',
					],
				],
				'Monkey',
				FALSE,
				ENT_QUOTES,
				'ISO-8859-1',
				[
					"\nclass Monkey extends Mustache_Template",
					'$value = $this->resolveValue($context->find(\'name\'), $context);',
					'$buffer .= $indent . htmlspecialchars($value, ' . ENT_QUOTES . ', \'ISO-8859-1\');',
					'return $buffer;',
				],
			],

			[
				'',
				[
					$this->createTextToken("foo\n"),
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_ESCAPED,
						Mustache_Tokenizer::NAME => 'name',
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_ESCAPED,
						Mustache_Tokenizer::NAME => '.',
					],
					$this->createTextToken("'bar'"),
				],
				'Monkey',
				FALSE,
				ENT_COMPAT,
				'UTF-8',
				[
					"\nclass Monkey extends Mustache_Template",
					"\$buffer .= \$indent . 'foo\n';",
					'$value = $this->resolveValue($context->find(\'name\'), $context);',
					'$buffer .= htmlspecialchars($value, ' . ENT_COMPAT . ', \'UTF-8\');',
					'$value = $this->resolveValue($context->last(), $context);',
					'$buffer .= \'\\\'bar\\\'\';',
					'return $buffer;',
				],
			],
		];
	}

	/**
	 * @expectedException Mustache_Exception_SyntaxException
	 */
	public function testCompilerThrowsSyntaxException() {
		$compiler = new Mustache_Compiler();
		$compiler->compile('', [[Mustache_Tokenizer::TYPE => 'invalid']], 'SomeClass');
	}

	/**
	 * @param string $value
	 */
	private function createTextToken($value) {
		return [
			Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
			Mustache_Tokenizer::VALUE => $value,
		];
	}
}
