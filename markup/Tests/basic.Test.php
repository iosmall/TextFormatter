<?php

namespace s9e\toolkit\markup;

include_once __DIR__ . '/../config_builder.php';
include_once __DIR__ . '/../parser.php';

class testBasic extends \PHPUnit_Framework_TestCase
{
	public function testPlainText()
	{
		$text     = 'This is some plain text.';
		$expected = '<pt>This is some plain text.</pt>';
		$actual   = $this->parser->parse($text);

		$this->assertSame($expected, $actual);
	}

	public function testPlainTextResultIsReversible()
	{
		$text   = 'This is some plain text.';
		$xml    = $this->parser->parse($text);

		$actual = html_entity_decode(strip_tags($xml));

		$this->assertSame($text, $actual);
	}

	public function testRichText()
	{
		$text     = 'This is some [b]bold[/b] text.';
		$expected = '<rt>This is some <B><st>[b]</st>bold<et>[/b]</et></B> text.</rt>';
		$actual   = $this->parser->parse($text);

		$this->assertSame($expected, $actual);
	}

	public function testRichTextResultIsReversible()
	{
		$text   = "This is some [b]bold[/b] text with special \"'& \xE2\x99\xA5<characters>\r\n"
		        . '...and line breaks too.';
		$xml    = $this->parser->parse($text);

		$actual = html_entity_decode(strip_tags($xml));

		$this->assertSame($text, $actual);
	}

	public function testNestingLimitIsRespected()
	{
		$text     = 'This is some [b][b]bold[/b] text.';
		$expected = '<rt>This is some <B><st>[b]</st>[b]bold<et>[/b]</et></B> text.</rt>';
		$actual   = $this->parser->parse($text);

		$this->assertSame($expected, $actual);
	}

	/**
	* @depends testNestingLimitIsRespected
	*/
	public function testBBCodeSuffix()
	{
		$text     = 'This is some [b:123][b]bold[/b][/b:123] text.';
		$expected = '<rt>This is some <B><st>[b:123]</st>[b]bold[/b]<et>[/b:123]</et></B> text.</rt>';
		$actual   = $this->parser->parse($text);

		$this->assertSame($expected, $actual);
	}

	public function testEmoticon()
	{
		$text     = 'test :) :)';
		$expected = '<rt>test <E code=":)">:)</E> <E code=":)">:)</E></rt>';
		$actual   = $this->parser->parse($text);

		$this->assertSame($expected, $actual);
	}

	public function testBBCodesFromTokenizersAreUppercasedIfNeeded()
	{
		$cb = new config_builder;
		$cb->addBBCode('b');

		$config = $cb->getParserConfig();

		$config['custom'] = array(
			'parser' => function()
			{
				return array(
					'tags' => array(
						array(
							'pos'  => 0,
							'len'  => 0,
							'type' => parser::TAG_OPEN,
							'name' => 'b'
						),
						array(
							'pos'  => 3,
							'len'  => 0,
							'type' => parser::TAG_CLOSE,
							'name' => 'B'
						)
					)
				);
			}
		);

		$parser = new parser($config);

		$expected = '<rt><B>foo</B></rt>';
		$actual   = $parser->parse('foo');

		$this->assertSame($expected, $actual);
	}

	public function testUnknownBBCodesAreIgnored()
	{
		$cb = new config_builder;
		$cb->addBBCode('b');
		$cb->addBBCode('i');

		/**
		* It is possible that an application would selectively disable BBCodes by altering the
		* config rather than regenerate a whole new one. We make sure stuff doesn't go haywire
		*/
		$config = $cb->getParserConfig();
		unset($config['bbcode']['aliases']['I']);
		unset($config['bbcode']['bbcodes']['I']);

		$parser = new parser($config);

		$text     = '[i]foo[/i]';
		$expected = '<pt>[i]foo[/i]</pt>';
		$actual   = $parser->parse($text);

		$this->assertSame($expected, $actual);
	}

	public function testUnknownBBCodesFromCustomPassesAreIgnored()
	{
		$cb = new config_builder;
		$cb->addBBCode('b');

		$config = $cb->getParserConfig();

		$config['custom'] = array(
			'parser' => function()
			{
				return array(
					'tags' => array(
						array(
							'pos'  => 0,
							'len'  => 0,
							'type' => parser::TAG_OPEN,
							'name' => 'Z'
						),
						array(
							'pos'  => 3,
							'len'  => 0,
							'type' => parser::TAG_CLOSE,
							'name' => 'Z'
						)
					)
				);
			}
		);

		$parser = new parser($config);

		$expected = '<pt>foo</pt>';
		$actual   = $parser->parse('foo');

		$this->assertSame($expected, $actual);
	}

	public function testAutolink()
	{
		$text     = 'Go to http://www.example.com for more';
		$expected = '<rt>Go to <A href="http://www.example.com">http://www.example.com</A> for more</rt>';
		$actual   = $this->parser->parse($text);

		$this->assertSame($expected, $actual);
	}

	public function setUp()
	{
		$cb = new config_builder;

		$cb->addBBCode('b', array('nesting_limit' => 1));
		$cb->addBBCode('e', array(
			'default_param'    => 'code',
			'content_as_param' => true
		));
		$cb->addBBCodeParam('e', 'code', 'text', true);

		$cb->setEmoticonOption('bbcode', 'e');
		$cb->setEmoticonOption('param', 'code');

		$cb->addEmoticon(':)');

		$cb->addBBCode('a');
		$cb->addBBCodeParam('a', 'href', 'url', true);
		$cb->setAutolinkOption('bbcode', 'a');
		$cb->setAutolinkOption('param', 'href');

		$cb->addBBCode('x');
		$cb->addBBCodeParam('x', 'foo', 'text', false);

		$this->parser = new parser($cb->getParserConfig());
	}
}