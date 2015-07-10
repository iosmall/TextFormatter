<?php

namespace s9e\TextFormatter\Tests\Plugins\Preg;

use Exception;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
use s9e\TextFormatter\Configurator\Items\AttributeFilters\RegexpFilter;
use s9e\TextFormatter\Configurator\Items\AttributeFilters\UrlFilter;
use s9e\TextFormatter\Configurator\JavaScript\RegExp;
use s9e\TextFormatter\Plugins\Preg\Configurator;
use s9e\TextFormatter\Tests\Test;

include_once __DIR__ . '/../../bootstrap.php';

/**
* @covers s9e\TextFormatter\Plugins\Preg\Configurator
*/
class ConfiguratorTest extends Test
{
	/**
	* @testdox replace() generates a tag name automatically
	*/
	public function testAddDefaultTagName()
	{
		$this->configurator->Preg->replace('/(?<foo>[0-9]+)/', '');

		$this->assertTrue(isset($this->configurator->tags['PREG_C53BB427']));
	}

	/**
	* @testdox The name of the tag can be specified
	*/
	public function testAddCustomTagName()
	{
		$this->configurator->Preg->replace('/(?<foo>[0-9]+)/', '', 'FOO');

		$this->assertTrue(isset($this->configurator->tags['FOO']));
	}

	/**
	* @testdox replace() returns the tag it creates
	*/
	public function testAddReturn()
	{
		$tag = $this->configurator->Preg->replace('/(?<foo>[0-9]+)/', '');

		$this->assertTrue(isset($this->configurator->tags['PREG_C53BB427']));
		$this->assertSame($this->configurator->tags['PREG_C53BB427'], $tag);
	}

	/**
	* @testdox replace() throws an exception if the regexp is invalid
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid regexp
	*/
	public function testInvalidRegexp()
	{
		$plugin = $this->configurator->plugins->load('Preg');
		$plugin->replace('invalid', '');
	}

	/**
	* @testdox replace() accepts duplicate named subpatterns
	*/
	public function testDuplicateSubpatterns()
	{
		$tag = $this->configurator->Preg->replace('#(?J)(?<foo>xxx)(?<foo>zzz)#', '');
		$this->assertSame(
			'#^(?:xxx|zzz)$#D',
			$tag->attributes['foo']->filterChain[0]->getRegexp()
		);
	}

	/**
	* @testdox replace() creates an attribute for each named subpattern
	*/
	public function testCreatesAttributes()
	{
		$tag = $this->configurator->Preg->replace('/(?<w>[0-9]+),(?<h>[0-9]+)/', '');

		$this->assertTrue($tag->attributes->exists('w'), "Attribute 'w' does not exist");
		$this->assertTrue($tag->attributes->exists('h'), "Attribute 'h' does not exist");
	}

	/**
	* @testdox replace() creates an attribute for each numeric subpattern in use
	*/
	public function testCreatesAttributesForSubpatternsInUse()
	{
		$tag = $this->configurator->Preg->replace('/([0-9]+),([0-9]+)/', '$1,$2');

		$this->assertTrue($tag->attributes->exists('_1'), "Attribute '_1' does not exist");
		$this->assertTrue($tag->attributes->exists('_2'), "Attribute '_2' does not exist");
	}

	/**
	* @testdox replace() creates a #regexp filter for each attribute created
	*/
	public function testCreatesAttributesWithFilter()
	{
		$tag = $this->configurator->Preg->replace('/(?<w>[0-9]+),(?<h>[0-9]+)/', '');

		$this->assertTrue(
			$tag->attributes->get('w')->filterChain->contains(
				new RegexpFilter('/^[0-9]+$/D')
			)
		);

		$this->assertTrue(
			$tag->attributes->get('h')->filterChain->contains(
				new RegexpFilter('/^[0-9]+$/D')
			)
		);
	}

	/**
	* @testdox replace() appends a #url filter to attributes that are used as a URL
	*/
	public function testCreatesAttributesWithUrlFilter()
	{
		$tag = $this->configurator->Preg->replace('/<([^>]+)>/', '<a href="$1">$1</a>');

		$this->assertTrue($tag->attributes['_1']->filterChain->contains(new UrlFilter));
	}

	/**
	* @testdox replace() replaces numeric references in the template with the corresponding attribute value
	*/
	public function testNumericReferencesTemplate()
	{
		$tag = $this->configurator->Preg->replace('/([0-9]+),([0-9]+)/', '$1,$2');

		$this->assertEquals(
			'<xsl:value-of select="@_1"/>,<xsl:value-of select="@_2"/>',
			$tag->template
		);
	}

	/**
	* @testdox replace() replaces numeric references pointing to named subpatterns in the template with the corresponding attribute value
	*/
	public function testNumericReferencesFromNamedSubpatternsTemplate()
	{
		$tag = $this->configurator->Preg->replace('/(?<w>[0-9]+),(?<h>[0-9]+)/', '$1,$2');

		$this->assertEquals(
			'<xsl:value-of select="@w"/>,<xsl:value-of select="@h"/>',
			$tag->template
		);
	}

	/**
	* @testdox replace() alters the regexp to give a name to unnamed subpatterns used in the template
	*/
	public function testAlterRegexpToNameSubpatterns()
	{
		$tag = $this->configurator->Preg->replace('/([0-9]+),([0-9]+)/', '$1,$2');

		$config   = $this->configurator->Preg->asConfig();
		$generics = $config['generics']->get();

		$this->assertSame(
			"/(?'_1'[0-9]+),(?'_2'[0-9]+)/",
			$generics[0][1]
		);
	}

	/**
	* @testdox replace() does not give a name to unnamed subpatterns that are not used in the template
	*/
	public function testDoesNotNameUnusedSubpatterns()
	{
		$tag = $this->configurator->Preg->replace('/([0-9]+),([0-9]+)/', '$2');

		$config   = $this->configurator->Preg->asConfig();
		$generics = $config['generics']->get();

		$this->assertSame(
			"/([0-9]+),(?'_2'[0-9]+)/",
			$generics[0][1]
		);
	}

	/**
	* @testdox replace() identifies $1 as a numeric reference in the template
	*/
	public function testNumericReferenceDollar()
	{
		$tag = $this->configurator->Preg->replace('/([0-9]+),([0-9]+)/', '$1,$2');

		$this->assertEquals(
			'<xsl:value-of select="@_1"/>,<xsl:value-of select="@_2"/>',
			$tag->template
		);
	}

	/**
	* @testdox replace() identifies \1 as a numeric reference in the template
	*/
	public function testNumericReferenceBackslash()
	{
		$tag = $this->configurator->Preg->replace('/([0-9]+),([0-9]+)/', '\\1,\\2');

		$this->assertEquals(
			'<xsl:value-of select="@_1"/>,<xsl:value-of select="@_2"/>',
			$tag->template
		);
	}

	/**
	* @testdox replace() identifies ${1} as a numeric reference in the template
	*/
	public function testNumericReferenceBraces()
	{
		$tag = $this->configurator->Preg->replace('/([0-9]+),([0-9]+)/', '${1},${2}');

		$this->assertEquals(
			'<xsl:value-of select="@_1"/>,<xsl:value-of select="@_2"/>',
			$tag->template
		);
	}

	/**
	* @testdox replace() interprets \\1 in the template as a literal \1
	*/
	public function testEscapedNumericReference()
	{
		if (PCRE_VERSION < 7.2)
		{
			$this->markTestSkipped('Requires PCRE 7.2');
		}

		$tag = $this->configurator->Preg->replace('/([0-9]+),([0-9]+)/', '\\1,\\\\1');

		$this->assertEquals(
			'<xsl:value-of select="@_1"/>,\\1',
			$tag->template
		);
	}

	/**
	* @testdox replace() interprets \\\1 in the template as a literal backslashes followed by a numeric reference
	*/
	public function testEscapedBackslashFollowedByNumericReference()
	{
		if (PCRE_VERSION < 7.2)
		{
			$this->markTestSkipped('Requires PCRE 7.2');
		}

		$tag = $this->configurator->Preg->replace('/([0-9]+),([0-9]+)/', '\\\\\\1');

		$this->assertEquals(
			'\\<xsl:value-of select="@_1"/>',
			$tag->template
		);
	}

	/**
	* @testdox replace() interprets \$1 in the template as a literal $1
	*/
	public function testEscapedDollar()
	{
		if (PCRE_VERSION < 7.2)
		{
			$this->markTestSkipped('Requires PCRE 7.2');
		}

		$tag = $this->configurator->Preg->replace('/([0-9]+),([0-9]+)/', '$1,\\$1');

		$this->assertEquals(
			'<xsl:value-of select="@_1"/>,$1',
			$tag->template
		);
	}

	/**
	* @testdox replace() replaces $0 with the whole match
	*/
	public function testNumericReferenceZeroWholeMatch()
	{
		$tag = $this->configurator->Preg->replace('/@(\\w+)/', '<a href="https://twitter.com/$1">$0</a>');

		$this->assertEquals(
			'<a href="https://twitter.com/{@_1}"><xsl:value-of select="."/></a>',
			$tag->template
		);
	}

	/**
	* @testdox replace() interprets a (.*?) capture used in template's text as a passthrough
	*/
	public function testPassthrough()
	{
		$tag = $this->configurator->Preg->replace(
			'/\\*(.*?)\\*/i',
			'<em>$1</em>'
		);

		$this->assertEquals(
			'<em><xsl:apply-templates/></em>',
			$tag->template
		);
	}

	/**
	* @testdox replace() interprets a (.*?) capture used in template's text as a passthrough
	*/
	public function testPassthrough2()
	{
		$tag = $this->configurator->Preg->replace(
			'#\\[(.*?)\\]\\((https?://.*?)\\)#i',
			'<a href="$2">$1</a>'
		);

		$this->assertEquals(
			'<a href="{@_2}"><xsl:apply-templates/></a>',
			$tag->template
		);
	}

	/**
	* @testdox A capture used as a URL can also be used as a passthrough, in which case it will used the filtered attribute when used in an attribute, and the normal passthrough when used in text
	* depends testCreatesAttributesWithUrlFilter
	*/
	public function testUrlCapturePassthrough()
	{
		$tag = $this->configurator->Preg->replace('/<(.*?)>/', '<a href="$1">$1</a>');

		$this->assertEquals('<a href="{@_1}"><xsl:apply-templates/></a>', $tag->template);
	}

	/**
	* @testdox Captures from non-existent subpattern are removed from the template
	*/
	public function testNonExistentCaptures()
	{
		$tag = $this->configurator->Preg->replace(
			'#[0-9]+#i',
			'x$1y'
		);

		$this->assertEquals(
			'xy',
			$tag->template
		);
	}

	/**
	* @testdox replace() normalizes the tag's template
	*/
	public function testNormalize()
	{
		$tag = $this->configurator->Preg->replace('#^---+$#m', '<xsl:element name="hr"/>');

		$this->assertEquals('<hr/>', $tag->template);
	}

	/**
	* @testdox replace() checks the safeness of the tag
	* @expectedException s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException
	*/
	public function testUnsafe()
	{
		$this->configurator->Preg->replace('#<(.*)>#', '<script>$1</script>');
	}

	/**
	* @testdox replace() checks the safeness of the tag before adding it to the configurator's collection
	*/
	public function testUnsafeBeforeAdd()
	{
		try
		{
			$this->configurator->Preg->replace('#<(.*)>#', '<script>$1</script>');
		}
		catch (Exception $e)
		{
		}

		$this->assertSame(0, count($this->configurator->tags));
	}

	/**
	* @testdox asConfig() returns NULL if no replacements were set
	*/
	public function testNullConfig()
	{
		$plugin = $this->configurator->plugins->load('Preg');
		$this->assertNull($plugin->asConfig());
	}

	/**
	* @testdox asConfig() returns the regexps in a "generics" array where each element is in the form [<tagName>,<regexp>,<passthrough index>]
	*/
	public function testAsConfig()
	{
		$plugin = $this->configurator->plugins->load('Preg');
		$plugin->replace('/(?<foo>[0-9]+)/', '');
		$plugin->replace('/(?<bar>[a-z]+)/', '');

		$config = $plugin->asConfig();
		ConfigHelper::filterVariants($config);

		$this->assertSame(
			[
				'generics' => [
					['PREG_C53BB427', '/(?<foo>[0-9]+)/', 0],
					['PREG_DCEA6E9C', '/(?<bar>[a-z]+)/', 0]
				]
			],
			$config
		);
	}

	/**
	* @testdox asConfig() does not creates a JavaScript variant by default
	*/
	public function testAsConfigNoJSVariant()
	{
		$plugin = $this->configurator->plugins->load('Preg');
		$plugin->replace('/(?<foo>[0-9]+)/', '');
		$plugin->replace('/(?<bar>[a-z]+)/', '');

		$config = $plugin->asConfig();

		$this->assertInstanceOf(
			's9e\\TextFormatter\\Configurator\\Items\\Variant',
			$config['generics']
		);

		$this->assertFalse($config['generics']->has('JS'));
	}

	/**
	* @testdox asConfig() creates a JavaScript variant of generics if JavaScript is enabled
	*/
	public function testAsConfigVariant()
	{
		$plugin = $this->configurator->plugins->load('Preg');
		$plugin->replace('/(?<foo>[0-9]+)/', '');
		$plugin->replace('/(?<bar>[a-z]+)/', '');

		$this->configurator->enableJavaScript();
		$config = $plugin->asConfig();

		$this->assertInstanceOf(
			's9e\\TextFormatter\\Configurator\\Items\\Variant',
			$config['generics']
		);

		$this->assertTrue($config['generics']->has('JS'));
	}

	/**
	* @testdox asConfig() creates JavaScript variants that contain a RegExp object instead of a regexp string, plus a map of named subpatterns
	*/
	public function testAsConfigVariantContent()
	{
		$plugin = $this->configurator->plugins->load('Preg');
		$plugin->replace('/(?<foo>[0-9]+)/', '');

		$regexp = new RegExp('([0-9]+)', 'g');
		$regexp->map = ['', 'foo'];

		$this->configurator->enableJavaScript();
		$config = $plugin->asConfig();
		ConfigHelper::filterVariants($config, 'JS');

		$this->assertEquals(
			[['PREG_C53BB427', $regexp, 0, $regexp->map]],
			$config['generics']
		);
	}

	/**
	* @testdox match() uses the last catch-all expression as passthrough
	*/
	public function testMatch()
	{
		$this->configurator->tags->add('X');
		$this->configurator->Preg->match('/`(.*?)`/', 'X');

		$config   = $this->configurator->Preg->asConfig();
		$generics = $config['generics']->get();

		$this->assertSame(1, $generics[0][2]);
	}
}

class FakeToken implements \ArrayAccess
{
	public $i = 0;

	public function offsetExists($offset)
	{
		return (bool) (++$this->i % 2);
	}

	public function offsetGet($offset)
	{
		return 'capturingSubpatternStart';
	}

	public function offsetSet($offset, $value)
	{
	}

	public function offsetUnset($offset)
	{
	}
}