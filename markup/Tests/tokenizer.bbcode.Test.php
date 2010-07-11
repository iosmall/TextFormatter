<?php

namespace s9e\toolkit\markup;

include_once __DIR__ . '/../config_builder.php';
include_once __DIR__ . '/../parser.php';

class testTokenizerBBCode extends \PHPUnit_Framework_TestCase
{
	public function testContentAsParam()
	{
		$ret = parser::getBBCodeTags('[url]http://www.example.com[/url]', $this->config['bbcode']);

		if (empty($ret['tags']))
		{
			$this->fail('No tags were parsed');
		}
		elseif (!isset($ret['tags'][0]['params']['url']))
		{
			$this->fail('The "url" param is missing');
		}
		else
		{
			$this->assertSame('http://www.example.com', $ret['tags'][0]['params']['url']);
		}
	}

	public function testContentAsParamWithBBCodeSuffix()
	{
		$ret = parser::getBBCodeTags('[url:1]http://www.example.com/?q[/url]=1[/url:1]', $this->config['bbcode']);

		if (empty($ret['tags']))
		{
			$this->fail('No tags were parsed');
		}
		elseif (!isset($ret['tags'][0]['params']['url']))
		{
			$this->fail('The "url" param is missing');
		}
		else
		{
			$this->assertSame('http://www.example.com/?q[/url]=1', $ret['tags'][0]['params']['url']);
		}
	}

	public function testDefaultParam()
	{
		$ret = parser::getBBCodeTags('[url=http://www.example.com]foo[/url]', $this->config['bbcode']);

		if (empty($ret['tags']))
		{
			$this->fail('No tags were parsed');
		}
		elseif (!isset($ret['tags'][0]['params']['url']))
		{
			$this->fail('The "url" param is missing');
		}
		else
		{
			$this->assertSame('http://www.example.com', $ret['tags'][0]['params']['url']);
		}
	}

	public function testTokenizerLimitIsRespected()
	{
		$text = str_repeat('[b]x[/b] ', 6);
		$ret  = parser::getBBCodeTags($text, $this->config['bbcode']);

		$this->assertSame(10, count($ret['tags']));
	}

	/**
	* @expectedException Exception
	*/
	public function testTokenizerLimitExceededWithActionAbortThrowsAnException()
	{
		$config = $this->config['bbcode'];
		$config['limit_action'] = 'abort';

		$text = str_repeat('[b]x[/b] ', 6);
		$ret  = parser::getBBCodeTags($text, $config);
	}

	public function testParamInDoubleQuotesIsParsedCorrectly()
	{
		$text = '[x foo="bar"]xxx[/x]';
		$ret  = parser::getBBCodeTags($text, $this->config['bbcode']);

		if (!isset($ret['tags'][0]['params']['foo']))
		{
			$this->fail('No param');
		}

		$this->assertSame('bar', $ret['tags'][0]['params']['foo']);
	}

	public function testParamInSingleQuotesIsParsedCorrectly()
	{
		$text = "[x foo='bar']xxx[/x]";
		$ret  = parser::getBBCodeTags($text, $this->config['bbcode']);

		if (!isset($ret['tags'][0]['params']['foo']))
		{
			$this->fail('No param');
		}

		$this->assertSame('bar', $ret['tags'][0]['params']['foo']);
	}

	public function testParamWithoutQuotesIsParsedCorrectly()
	{
		$text = '[x foo=bar]xxx[/x]';
		$ret  = parser::getBBCodeTags($text, $this->config['bbcode']);

		if (!isset($ret['tags'][0]['params']['foo']))
		{
			$this->fail('No param');
		}

		$this->assertSame('bar', $ret['tags'][0]['params']['foo']);
	}

	public function testEscapedQuotesAreParsedCorrectly()
	{
		$text = '[x foo="\"b\"ar\""]xxx[/x]';
		$ret  = parser::getBBCodeTags($text, $this->config['bbcode']);

		if (!isset($ret['tags'][0]['params']['foo']))
		{
			$this->fail('No param');
		}

		$this->assertSame('"b"ar"', $ret['tags'][0]['params']['foo']);
	}

	public function testSelfClosingTagsAreParsedCorrectly()
	{
		$text = '[x/] [x /]';
		$ret  = parser::getBBCodeTags($text, $this->config['bbcode']);

		$this->assertSame(2, count($ret['tags']));
		$this->assertSame('X', $ret['tags'][0]['name']);
		$this->assertSame('X', $ret['tags'][1]['name']);
		$this->assertSame(parser::TAG_SELF, $ret['tags'][0]['type']);
		$this->assertSame(parser::TAG_SELF, $ret['tags'][1]['type']);
		$this->assertSame(4, $ret['tags'][0]['len']);
		$this->assertSame(5, $ret['tags'][1]['len']);
	}

	/**
	* @depends testSelfClosingTagsAreParsedCorrectly
	*/
	public function testSelfClosingTagsCanHaveParams()
	{
		$text     = '[x foo="bar" /]';
		$actual   = parser::getBBCodeTags($text, $this->config['bbcode']);
		$expected = array(
			'tags' => array(
				array(
					'name'   => 'X',
					'pos'    => 0,
					'len'    => 15,
					'params' => array('foo' => 'bar')
				)
			)
		);

		$this->assertKindaEquals($expected, $actual);
	}

	/**
	* @depends testSelfClosingTagsCanHaveParams
	*/
	public function testQuotesCanBeEscapedInsideParamValues()
	{
		$text     = '[x foo="ba\\"r" /]';
		$actual   = parser::getBBCodeTags($text, $this->config['bbcode']);
		$expected = array(
			'tags' => array(
				array(
					'name'   => 'X',
					'pos'    => 0,
					'len'    => 17,
					'params' => array('foo' => 'ba"r')
				)
			)
		);

		$this->assertKindaEquals($expected, $actual);
	}

	/**
	* @depends testSelfClosingTagsCanHaveParams
	*/
	public function testBackslashesAndQuotesCanBeEscapedInsideParamValues()
	{
		// foo="ba\\\"r" -- that's one escaped backslash followed by one escaped quote
		$text     = '[x foo="ba\\\\\\"r" /]';
		$actual   = parser::getBBCodeTags($text, $this->config['bbcode']);
		$expected = array(
			'tags' => array(
				array(
					'name'   => 'X',
					'pos'    => 0,
					'len'    => 19,
					'params' => array('foo' => 'ba\\"r')
				)
			)
		);

		$this->assertKindaEquals($expected, $actual);
	}

	/**
	* @depends testSelfClosingTagsCanHaveParams
	*/
	public function testParamValuesCanEndWithAnEscapedBackslash()
	{
		// foo="ba\\\"r" -- that's one escaped backslash followed by one escaped quote
		$text     = '[x foo="bar\\\\" /]';
		$actual   = parser::getBBCodeTags($text, $this->config['bbcode']);
		$expected = array(
			'tags' => array(
				array(
					'name'   => 'X',
					'pos'    => 0,
					'len'    => 17,
					'params' => array('foo' => 'bar\\')
				)
			)
		);

		$this->assertKindaEquals($expected, $actual);
	}

	public function testUnknownBBCodesAreIgnored()
	{
		$config = $this->config['bbcode'];
		unset($config['aliases']['X']);

		$text     = '[x][/x]';
		$actual   = parser::getBBCodeTags($text, $config);
		$expected = array(
			'tags' => array()
		);

		$this->assertKindaEquals($expected, $actual);
	}

	/**
	* @dataProvider getInvalidStuff
	*/
	public function testInvalidStuff($text, $expected)
	{
		$actual = parser::getBBCodeTags($text, $this->config['bbcode']);
		$this->assertKindaEquals($expected, $actual);
	}

	public function getInvalidStuff()
	{
		return array(

			array(
				'[x foo=" /]',
				array(
					'tags' => array(),
					'msgs' => array(
						'error' => array(
							array('pos' => 7)
						)
					)
				)
			),
			array(
				'[z][/z]',
				array(
					'tags' => array(),
					'msgs' => array(
						'warning' => array(
							array(
								'pos'    => 0,
								'msg'    => 'BBCode %s is for internal use only',
								'params' => array('Z')
							)
						)
					)
				)
			),
			array(
				'[x]x[/x=123]',
				array(
					'tags' => array(
						array(
							'name' => 'X',
							'pos'  => 0,
							'len'  => 3
						)
					),
					'msgs' => array(
						'warning' => array(
							array(
								'pos'    => 7,
								'msg'    => 'Unexpected character %s',
								'params' => array('=')
							)
						)
					)
				)
			),
			array(
				'[x foo=]',
				array(
					'tags' => array(),
					'msgs' => array(
						'warning' => array(
							array(
								'pos'    => 7,
								'msg'    => 'Unexpected character %s',
								'params' => array(']')
							)
						)
					)
				)
			),
			array(
				'[x foo=/',
				array(
					'tags' => array(),
					'msgs' => array(
						'warning' => array(
							array(
								'pos'    => 7,
								'msg'    => 'Unexpected character %s',
								'params' => array('/')
							)
						)
					)
				)
			),
			array(
				'[x/',
				array(
					'tags' => array(),
					'msgs' => array()
				)
			),
			array(
				'[x//]',
				array(
					'tags' => array(),
					'msgs' => array(
						'warning' => array(
							array(
								'pos'    => 3,
								'msg'    => 'Unexpected character: expected ] found %s',
								'params' => array('/')
							)
						)
					)
				)
			),
			array(
				'[x !]',
				array(
					'tags' => array(),
					'msgs' => array(
						'warning' => array(
							array(
								'pos'    => 3,
								'msg'    => 'Unexpected character %s',
								'params' => array('!')
							)
						)
					)
				)
			),
			array(
				'[x param',
				array(
					'tags' => array(),
					'msgs' => array(
						'debug' => array(
							array(
								'pos'    => 3,
								'msg'    => 'Param name seems to extend till the end of $text'
							)
						)
					)
				)
			),
			array(
				'[x param]',
				array(
					'tags' => array(),
					'msgs' => array(
						'warning' => array(
							array(
								'pos'    => 8,
								'msg'    => 'Unexpected character %s',
								'params' => array(']')
							)
						)
					)
				)
			),
		);
	}

	public function setUp()
	{
		$cb = new config_builder;

		$cb->setBBCodeOption('limit', 10);
		$cb->setBBCodeOption('limit_action', 'ignore');

		$cb->addBBCode('b');
		$cb->addBBCode('url', array(
			'default_param'    => 'url',
			'content_as_param' => true
		));
		$cb->addBBCode('x');
		$cb->addBBCode('y');
		$cb->addBBCode('z');

		$cb->addBBCodeParam('x', 'foo', 'text', false);
		$cb->addBBCodeParam('y', 'foo', 'text', true);

		$cb->addBBCodeParam('url', 'url', 'url', true);

		$this->config = $cb->getParserConfig();

		// we temper with the config to let us explore all code paths
		$this->config['bbcode']['bbcodes']['Z']['internal_use'] = true;
	}

	protected function assertKindaEquals($expected, $actual)
	{
		foreach ($expected as $type => $content)
		{
			$this->assertArrayHasKey($type, $actual);

			if (count($expected[$type]) !== count($actual[$type]))
			{
				$this->assertEquals($expected[$type], $actual[$type]);
			}

			switch ($type)
			{
				case 'msgs':
					$this->assertKindaEquals($expected['msgs'], $actual['msgs']);
					break;

				case 'tags':
				case 'error':
				case 'warning':
				case 'debug':
					foreach ($content as $k => $v)
					{
						$this->assertEquals(
							$v,
							array_intersect_key($actual[$type][$k], $v)
						);
					}
					break;

				default:
					$this->fail('Unknown key');
			}
		}
	}
}