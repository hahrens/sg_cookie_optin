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
class Mustache_Test_ParserTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider getTokenSets
	 */
	public function testParse($tokens, $expected) {
		$parser = new Mustache_Parser();
		$this->assertEquals($expected, $parser->parse($tokens));
	}

	public function getTokenSets() {
		return [
			[
				[],
				[],
			],

			[
				[[
					Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
					Mustache_Tokenizer::LINE => 0,
					Mustache_Tokenizer::VALUE => 'text',
				]],
				[[
					Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
					Mustache_Tokenizer::LINE => 0,
					Mustache_Tokenizer::VALUE => 'text',
				]],
			],

			[
				[[
					Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_ESCAPED,
					Mustache_Tokenizer::LINE => 0,
					Mustache_Tokenizer::NAME => 'name',
				]],
				[[
					Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_ESCAPED,
					Mustache_Tokenizer::LINE => 0,
					Mustache_Tokenizer::NAME => 'name',
				]],
			],

			[
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::VALUE => 'foo',
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_INVERTED,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 123,
						Mustache_Tokenizer::NAME => 'parent',
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_ESCAPED,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::NAME => 'name',
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_END_SECTION,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 456,
						Mustache_Tokenizer::NAME => 'parent',
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::VALUE => 'bar',
					],
				],

				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::VALUE => 'foo',
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_INVERTED,
						Mustache_Tokenizer::NAME => 'parent',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 123,
						Mustache_Tokenizer::END => 456,
						Mustache_Tokenizer::NODES => [
							[
								Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_ESCAPED,
								Mustache_Tokenizer::LINE => 0,
								Mustache_Tokenizer::NAME => 'name',
							],
						],
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::VALUE => 'bar',
					],
				],
			],

			// This *would* be an invalid inheritance parse tree, but that pragma
			// isn't enabled so it'll thunk it back into an "escaped" token:
			[
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_BLOCK_VAR,
						Mustache_Tokenizer::NAME => 'foo',
						Mustache_Tokenizer::OTAG => '{{',
						Mustache_Tokenizer::CTAG => '}}',
						Mustache_Tokenizer::LINE => 0,
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::VALUE => 'bar',
					],
				],
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_ESCAPED,
						Mustache_Tokenizer::NAME => '$foo',
						Mustache_Tokenizer::OTAG => '{{',
						Mustache_Tokenizer::CTAG => '}}',
						Mustache_Tokenizer::LINE => 0,
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::VALUE => 'bar',
					],
				],
			],

			[
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::VALUE => '  ',
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_DELIM_CHANGE,
						Mustache_Tokenizer::LINE => 0,
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::VALUE => "  \n",
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_ESCAPED,
						Mustache_Tokenizer::NAME => 'foo',
						Mustache_Tokenizer::OTAG => '[[',
						Mustache_Tokenizer::CTAG => ']]',
						Mustache_Tokenizer::LINE => 1,
					],
				],
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_ESCAPED,
						Mustache_Tokenizer::NAME => 'foo',
						Mustache_Tokenizer::OTAG => '[[',
						Mustache_Tokenizer::CTAG => ']]',
						Mustache_Tokenizer::LINE => 1,
					],
				],
			],

		];
	}

	/**
	 * @dataProvider getInheritanceTokenSets
	 */
	public function testParseWithInheritance($tokens, $expected) {
		$parser = new Mustache_Parser();
		$parser->setPragmas([Mustache_Engine::PRAGMA_BLOCKS]);
		$this->assertEquals($expected, $parser->parse($tokens));
	}

	public function getInheritanceTokenSets() {
		return [
			[
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_PARENT,
						Mustache_Tokenizer::NAME => 'foo',
						Mustache_Tokenizer::OTAG => '{{',
						Mustache_Tokenizer::CTAG => '}}',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 8,
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_BLOCK_VAR,
						Mustache_Tokenizer::NAME => 'bar',
						Mustache_Tokenizer::OTAG => '{{',
						Mustache_Tokenizer::CTAG => '}}',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 16,
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::VALUE => 'baz',
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_END_SECTION,
						Mustache_Tokenizer::NAME => 'bar',
						Mustache_Tokenizer::OTAG => '{{',
						Mustache_Tokenizer::CTAG => '}}',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 19,
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_END_SECTION,
						Mustache_Tokenizer::NAME => 'foo',
						Mustache_Tokenizer::OTAG => '{{',
						Mustache_Tokenizer::CTAG => '}}',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 27,
					],
				],
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_PARENT,
						Mustache_Tokenizer::NAME => 'foo',
						Mustache_Tokenizer::OTAG => '{{',
						Mustache_Tokenizer::CTAG => '}}',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 8,
						Mustache_Tokenizer::END => 27,
						Mustache_Tokenizer::NODES => [
							[
								Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_BLOCK_ARG,
								Mustache_Tokenizer::NAME => 'bar',
								Mustache_Tokenizer::OTAG => '{{',
								Mustache_Tokenizer::CTAG => '}}',
								Mustache_Tokenizer::LINE => 0,
								Mustache_Tokenizer::INDEX => 16,
								Mustache_Tokenizer::END => 19,
								Mustache_Tokenizer::NODES => [
									[
										Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
										Mustache_Tokenizer::LINE => 0,
										Mustache_Tokenizer::VALUE => 'baz',
									],
								],
							],
						],
					],
				],
			],

			[
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_BLOCK_VAR,
						Mustache_Tokenizer::NAME => 'foo',
						Mustache_Tokenizer::OTAG => '{{',
						Mustache_Tokenizer::CTAG => '}}',
						Mustache_Tokenizer::LINE => 0,
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::VALUE => 'bar',
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_END_SECTION,
						Mustache_Tokenizer::NAME => 'foo',
						Mustache_Tokenizer::OTAG => '{{',
						Mustache_Tokenizer::CTAG => '}}',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 11,
					],
				],
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_BLOCK_VAR,
						Mustache_Tokenizer::NAME => 'foo',
						Mustache_Tokenizer::OTAG => '{{',
						Mustache_Tokenizer::CTAG => '}}',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::END => 11,
						Mustache_Tokenizer::NODES => [
							[
								Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
								Mustache_Tokenizer::LINE => 0,
								Mustache_Tokenizer::VALUE => 'bar',
							],
						],
					],
				],
			],
		];
	}

	/**
	 * @dataProvider getBadParseTrees
	 * @expectedException Mustache_Exception_SyntaxException
	 */
	public function testParserThrowsExceptions($tokens) {
		$parser = new Mustache_Parser();
		$parser->parse($tokens);
	}

	public function getBadParseTrees() {
		return [
			// no close
			[
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_SECTION,
						Mustache_Tokenizer::NAME => 'parent',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 123,
					],
				],
			],

			// no close inverted
			[
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_INVERTED,
						Mustache_Tokenizer::NAME => 'parent',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 123,
					],
				],
			],

			// no opening inverted
			[
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_END_SECTION,
						Mustache_Tokenizer::NAME => 'parent',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 123,
					],
				],
			],

			// weird nesting
			[
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_SECTION,
						Mustache_Tokenizer::NAME => 'parent',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 123,
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_SECTION,
						Mustache_Tokenizer::NAME => 'child',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 123,
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_END_SECTION,
						Mustache_Tokenizer::NAME => 'parent',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 123,
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_END_SECTION,
						Mustache_Tokenizer::NAME => 'child',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 123,
					],
				],
			],

			// This *would* be a valid inheritance parse tree, but that pragma
			// isn't enabled here so it's going to fail :)
			[
				[
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_BLOCK_VAR,
						Mustache_Tokenizer::NAME => 'foo',
						Mustache_Tokenizer::OTAG => '{{',
						Mustache_Tokenizer::CTAG => '}}',
						Mustache_Tokenizer::LINE => 0,
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_TEXT,
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::VALUE => 'bar',
					],
					[
						Mustache_Tokenizer::TYPE => Mustache_Tokenizer::T_END_SECTION,
						Mustache_Tokenizer::NAME => 'foo',
						Mustache_Tokenizer::OTAG => '{{',
						Mustache_Tokenizer::CTAG => '}}',
						Mustache_Tokenizer::LINE => 0,
						Mustache_Tokenizer::INDEX => 11,
					],
				],
			],
		];
	}
}
