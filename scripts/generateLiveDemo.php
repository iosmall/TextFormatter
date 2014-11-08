#!/usr/bin/php
<?php

include __DIR__ . '/../src/autoloader.php';

$configurator = new s9e\TextFormatter\Configurator;
$configurator->enableJavaScript();

$configurator->BBCodes->addFromRepository('B');
$configurator->BBCodes->addFromRepository('I');
$configurator->BBCodes->addFromRepository('U');
$configurator->BBCodes->addFromRepository('S');
$configurator->BBCodes->addFromRepository('URL');
$configurator->BBCodes->addFromRepository('QUOTE');
$configurator->BBCodes->addFromRepository('LIST');
$configurator->BBCodes->addFromRepository('*');
$configurator->BBCodes->addFromRepository('C');
$configurator->BBCodes->addFromRepository('COLOR');
$configurator->BBCodes->addFromRepository('FLOAT');
$configurator->BBCodes->addFromRepository('CODE');

$configurator->Censor->add('apple', 'banana');
$configurator->Emoticons->add(':)', '<img src="s.png" alt=":)"/>');
$configurator->Generic->add(
	'/#(?<tag>[a-z0-9]+)/i',
	'<a href="https://twitter.com/#!/search/%23{@tag}"><xsl:apply-templates/></a>'
);
$configurator->HTMLElements->allowElement('a');
$configurator->HTMLElements->allowElement('b');
$configurator->HTMLElements->allowAttribute('a', 'href');
$configurator->HTMLElements->allowAttribute('a', 'title');

$configurator->Autolink;
$configurator->HTMLEntities;
$configurator->FancyPants;

$configurator->MediaEmbed->add('youtube');
$configurator->tags['YOUTUBE']->template = '<iframe width="240" height="180" src="http://www.youtube.com/embed/{@id}" allowfullscreen=""/>';

$configurator->javascript
	->setMinifier('ClosureCompilerService')
	->cacheDir = __DIR__ . '/../tests/.cache';

$configurator->javascript->exportMethods = ['disablePlugin', 'enablePlugin', 'preview'];

extract($configurator->finalize([
	'returnParser'   => false,
	'returnRenderer' => false
]));

ob_start();
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>s9e\TextFormatter &bull; Demo</title>
	<base href="http://s9e.github.io/TextFormatter/demo.html" />
	<style type="text/css">
		#preview
		{
			font-family: sans;
			padding: 5px;
			background-color: #f8f8f8;
			border: dashed 1px #ddd;
			border-radius: 5px;
		}

		code
		{
			padding: 2px;
			background-color: #fff;
			border-radius: 3px;
			border: solid 1px #ddd;
		}
	</style>

</head>
<body>
	<div style="float:left;width:80%;max-width:800px">
		<form>
			<textarea style="width:99%" rows="15">[float=right][youtube]http://www.youtube.com/watch?v=QH2-TGUlwu4[/youtube][/float]

This is a demo of the JavaScript port of [url=https://github.com/s9e/TextFormatter/tree/master/src/ title="s9e\TextFormatter at GitHub.com"]s9e\TextFormatter[/url].

The following plugins have been enabled:

[list]
  [*][b]Autolink[/b] --- loose URLs such as http://github.com are automatically turned into links
  [*][b]BBCodes[/b]
  [list=circle]
    [*][b]bold[/b], [i]italic[/i], [u]underline[/u], [s]strikethrough[/s],
    [*][color=#f05]co[/color][color=#2f2]lo[/color][color=#02f]r,[/color]
    [*][C][URL][/C], [C:123][C][/C:123], [C][YOUTUBE][/C], [C][FLOAT][/C], and [C][LIST][/C]
    [*][C][CODE][/C] with real-time syntax highlighting via [url=http://softwaremaniacs.org/soft/highlight/en/]Highlight.js[/url]
	[code]$who = "world";
printf("Hello %s\n", $who);[/code]
  [/list]
  [*][b]Censor[/b] --- the word "apple" is censored and automatically replaced with "banana"
  [*][b]Emoticons[/b] --- one emoticon :) has been added
  [*][b]FancyPants[/b] --- some typography is enhanced, e.g. (c) (tm) and "quotes"
  [*][b]Generic[/b] --- the Generic plugin provides a way to perform generic regexp-based replacements that are HTML-safe. Here, text that matches [C]/#(?<tag>[a-z0-9]+)/i[/C] is replaced with the template [C]<a href="https://twitter.com/#!/search/%23{@tag}"><xsl:apply-templates/></a>[/C] -- For example: #PHP, #fml
  [*][b]HTMLElements[/b] --- [C]<a>[/C] and [C]<b>[/C] tags are allowed, with two whitelisted attributes for [C]<a>[/C]: [C]href[/C] and [C]title[/C]. Example: <a href="https://github.com" title="GitHub - Social Coding"><b>GitHub</b></a>
  [*][b]HTMLEntities[/b] --- HTML entities such as &amp;hearts; are decoded
[/list]

The parser/renderer used on this page page has been generated by [url=https://github.com/s9e/TextFormatter/blob/master/scripts/generateLiveDemo.php]this script[/url]. It's been minified with Google Closure Compiler to <?php printf('%.1f', strlen($js) / 1024); ?> KB (<?php printf('%.1f', strlen(gzcompress($js, 9)) / 1024); ?> KB compressed)</textarea>
		</form>
	</div>

	<div style="float:left;">
		<form><?php

			$list = [];

			foreach ($configurator->plugins as $pluginName => $plugin)
			{
				$list[$pluginName] = '<input type="checkbox" id="' . $pluginName . '" checked="checked" onchange="toggle(this)"><label for="' . $pluginName . '">&nbsp;'. $pluginName . '</label>';
			}

			ksort($list);
			echo implode('<br>', $list);

		?></form>
	</div>

	<div style="clear:both"></div>

	<div id="preview"></div>

	<script type="text/javascript"><?php echo $js; ?>

		var text,
			textareaEl = document.getElementsByTagName('textarea')[0],
			previewEl = document.getElementById('preview');

		window.setInterval(function()
		{
			if (textareaEl.value === text)
			{
				return;
			}

			text = textareaEl.value;
			s9e.TextFormatter.preview(text, previewEl);
		}, 20);

		function toggle(el)
		{
			(el.checked) ? s9e.TextFormatter.enablePlugin(el.id)
			             : s9e.TextFormatter.disablePlugin(el.id);

			text = '';
		}
	</script>
	<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/punycode/1.0.0/punycode.min.js"></script>
</body>
</html><?php

file_put_contents(__DIR__ . '/../../s9e.github.io/TextFormatter/demo.html', ob_get_clean());

echo "Done.\n";