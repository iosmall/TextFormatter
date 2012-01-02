#!/usr/bin/php
<?php

include __DIR__ . '/../src/ConfigBuilder.php';

$cb = new \s9e\TextFormatter\ConfigBuilder;

$cb->disallowHost('*.example.com');

$cb->BBCodes->addPredefinedBBCode('B');
$cb->BBCodes->addPredefinedBBCode('I');
$cb->BBCodes->addPredefinedBBCode('U');
$cb->BBCodes->addPredefinedBBCode('S');
$cb->BBCodes->addPredefinedBBCode('URL');
$cb->BBCodes->addPredefinedBBCode('LIST');
$cb->BBCodes->addPredefinedBBCode('COLOR');
$cb->BBCodes->addPredefinedBBCode('YOUTUBE');
$cb->BBCodes->addPredefinedBBCode('FLOAT');

$cb->BBCodes->addBBCode('CODE', array(
	'template' => '<code style="display:inline"><xsl:apply-templates/></code>',
	'defaultDescendantRule' => 'deny'
));

$cb->Emoticons->addEmoticon(':)', '<img alt=":)" src="https://github.com/images/icons/public.png"/>');

$cb->Censor->addWord('apple', 'banana');

$cb->Generic->addReplacement(
	'#\\$(?<amount>[0-9]+(?:\\.[0-9]+)?)#',
	'<a href="http://www.google.com/search?q={@amount}+USD+in+EUR"><xsl:apply-templates/></a>'
);

$cb->RawHTML->allowElement('a');
$cb->RawHTML->allowElement('b');
$cb->RawHTML->allowAttribute('a', 'href');
$cb->RawHTML->allowAttribute('a', 'title');

$cb->loadPlugin('Autolink');
$cb->loadPlugin('HTMLEntities')->disableEntity('&lt;');
$cb->loadPlugin('Linebreaker');
$cb->loadPlugin('WittyPants');

$cb->addRulesFromHTML5Specs();

$jsParser = $cb->getJSParser(array(
	'compilationLevel'     => 'ADVANCED_OPTIMIZATIONS',
	'setOptimizationHints' => true
));

ob_start();
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>s9e\TextFormatter &bull; Demo</title>
	<style type="text/css">
		div
		{
			margin-bottom: 10px;
		}

		#logdiv
		{
			max-height: 120px;
			overflow: auto;
		}

		#logdiv,
		#preview
		{
			font-family: sans;
			padding: 5px;
			background-color: #eee;
			border: dashed 1px #8af;
			border-radius: 5px;
		}

		label
		{
			cursor: pointer;
		}

		code
		{
			padding: 3px;
			background-color: #fff;
			border-radius: 3px;
		}

		pre
		{
			margin: 0;
		}

		object
		{
			transition: width .5s, height .5s;
			-transition: width .5s, height .5s;
			-o-transition: width .5s, height .5s;
			-moz-transition: width .5s, height .5s;
			-webkit-transition: width .5s, height .5s;
		}
	</style>
</head>
<body>
	<div style="float:left;width:80%;max-width:800px">
		<form>
			<textarea style="width:99%" rows="15">[float=right][youtube width=240 height=180]http://www.youtube.com/watch?v=QH2-TGUlwu4[/youtube][/float]

This is a demo of the Javascript port of [url=https://github.com/s9e/TextFormatter/tree/master/src/ title="s9e\TextFormatter at GitHub.com"]s9e\TextFormatter[/url].

The following plugins have been enabled:

[list]
  [*][b]Autolink[/b] --- loose URLs such as http://github.com are automatically turned into links

  [*][b]BBCodes[/b]
  [list=circle]
    [*][b]bold[/b], [i]italic[/i], [u]underline[/u], [s]strikethrough[/s],
    [*][color=#f05]co[/color][color=#2f2]lo[/color][color=#02f]r,[/color]
    [*][CODE][URL][/CODE], [CODE:123][CODE][/CODE:123], [CODE][YOUTUBE][/CODE], [CODE][FLOAT][/CODE], and [CODE][LIST][/CODE]
  [/list][/*]

  [*][b]Censor[/b] --- the word "apple" is censored and automatically replaced with "banana"
  [*][b]Emoticons[/b] --- one emoticon :) has been added
  [*][b]Generic[/b] --- the Generic plugin provides a way to perform generic regexp-based replacements that are HTML-safe. Here, text that matches [CODE]#\$(?<amount>[0-9]+(?:\.[0-9]+)?)#[/CODE] is replaced with the template [CODE]<a href="http://www.google.com/search?q={@amount}+USD+in+EUR"><xsl:apply-templates/></a>[/CODE] -- For example: $2, $4.50
  [*][b]HTMLEntities[/b] --- HTML entities such as &amp;hearts; are decoded
  [*][b]Linebreaker[/b] --- Linefeeds are converted to &lt;br&gt;
  [*][b]RawHTML[/b] --- [CODE]<a>[/CODE] tags are allowed, with two whitelisted attributes: [CODE]href[/CODE] and [CODE]title[/CODE]. Example: <a href="https://github.com" title="GitHub - Social Coding">GitHub</a>
  [*][b]WittyPants[/b] --- some typography is enhanced, e.g. (c) (tm) and "quotes"
[/list]

Additionally, in order to demonstrate some other features:

[list]
  [*][b]ConfigBuilder::disallowHost()[/b] --- links to [url=http://example.com]example.com[/url] are disabled. This applies to [b]Autolink[/b] and [b]RawHTML[/b] as well: <a href="http://example.com">example.com</a>
  [*][b]HTMLEntitiesConfig::disableEntity()[/b] --- the HTML entity &amp;lt; is arbitrarily disabled
  [*]a YouTube video, at the right, keeps playing as you're editing the text [i](including its own tag!)[/i] to demonstrate the partial-update algorithm used to refresh the live preview
[/list]

You can take a look at the log, hover the messages with the mouse and click them to get to the part of the text that generated them.

The parser/renderer used on this page page has been generated via [url=https://github.com/s9e/TextFormatter/blob/master/scripts/generateJSParserDemo.php]this script[/url]. It has been minified to <?php echo round(strlen($jsParser) / 1024, 1) . 'KB (' . round(strlen(gzencode($jsParser, 9)) / 1024, 1) . 'KB gzipped)'; ?> with [url=http://closure-compiler.appspot.com/home]Google Closure Compiler[/url].' The raw sources can be found [url=https://github.com/s9e/TextFormatter/blob/master/src/TextFormatter.js]at GitHub[/url].</textarea>
			<br>
			<input type="checkbox" id="rendercheck" checked="checked"><label for="rendercheck"> Render</label>
			<input type="checkbox" id="logcheck"><label for="logcheck"> Show log</label>
		</form>
	</div>

	<div style="float:left;">
		<form><?php

			$plugins = $cb->getLoadedPlugins();
			ksort($plugins);

			foreach ($plugins as $pluginName => $plugin)
			{
				echo '<input type="checkbox" id="', $pluginName, '" checked="checked" onchange="toggle(this)"><label for="', $pluginName, '">&nbsp;', $pluginName, '</label><br>';
			}

		?></form>
	</div>

	<div style="clear:both"></div>

	<div id="logdiv" style="display:none"></div>

	<div id="preview"></div>

	<script type="text/javascript"><?php echo $jsParser; ?>

		var text,

			textarea = document.getElementsByTagName('textarea')[0],
			preview = document.getElementById('preview'),

			rendercheck = document.getElementById('rendercheck'),

			logcheck = document.getElementById('logcheck'),
			logdiv = document.getElementById('logdiv'),
			disableHighlight = false;

		rendercheck.onchange = refreshOutput;

		textarea.onmouseout = function()
		{
			disableHighlight = false;
		}

		logcheck.onchange = function()
		{
			if (logcheck.checked)
			{
				logdiv.style.display = '';
				refreshLog();
			}
			else
			{
				logdiv.style.display = 'none';
			}
		}

		function refreshOutput()
		{
			if (rendercheck.checked)
			{
				s9e.TextFormatter.preview(text, preview);
			}
			else
			{
				var xml = s9e.TextFormatter.parse(text);
				preview.innerHTML = '<pre></pre>';

				if ('XMLSerializer' in window)
				{
					preview.firstChild.textContent = new XMLSerializer().serializeToString(xml);
				}
				else
				{
					preview.firstChild.innerText = xml.xml
				}
			}
		}

		function refreshLog()
		{
			var log = s9e.TextFormatter.getLog(),
				msgs = [];

			['error', 'warning', 'debug'].forEach(function(type)
			{
				if (!log[type])
				{
					return;
				}

				log[type].forEach(function(entry)
				{
					var msg = '[' + type + '] [' + entry.pluginName + '] ' + entry.msg.replace(
							/%(?:([0-9])\$)?[sd]/g,
							function(str, p1)
							{
								return entry.params[(p1 ? p1 - 1 : 0)];
							}
						);

					if (entry.pos !== undefined)
					{
						if (!entry.len)
						{
							entry.len = 0;
						}

						msg = '<a style="cursor:pointer" onmouseover="highlight(' + entry.pos + ',' + entry.len + ')" onclick="select(' + entry.pos + ',' + entry.len + ')">' + msg + '</a>';
					}

					msgs.push(msg);
				});
			});

			logdiv.innerHTML = (msgs.length) ? msgs.join('<br/>') : 'No log';
		}

		function highlight(pos, len)
		{
			if (disableHighlight)
			{
				return;
			}

			if (textarea.setSelectionRange)
			{
				textarea.setSelectionRange(pos, pos + len);
			}
			else
			{
				var range = textarea.createTextRange();
				range.collapse(true);
				range.moveEnd('character', pos + len);
				range.moveStart('character', pos);
				range.select();
			}
		}

		function select(pos, len)
		{
			disableHighlight = false;
			textarea.focus();
			highlight(pos, len);
			disableHighlight = true;
		}

		function toggle(el)
		{
			s9e.TextFormatter[(el.checked) ? 'enablePlugin' : 'disablePlugin'](el.id);
			text = '';
		}

		window.setInterval(function()
		{
			if (textarea.value === text)
			{
				return;
			}

			text = textarea.value;
			refreshOutput();

			if (logcheck.checked)
			{
				refreshLog();
			}
		}, 20);
	</script>
</body>
</html><?php

file_put_contents(__DIR__ . '/../../s9e.github.com/TextFormatter/demo.html', ob_get_clean());

echo "Done.\n";