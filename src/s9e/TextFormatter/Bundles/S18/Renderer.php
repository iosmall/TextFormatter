<?php
/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2013 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/

namespace s9e\TextFormatter\Bundles\S18;

class Renderer extends \s9e\TextFormatter\Renderer
{
	protected $htmlOutput=true;
	protected $dynamicParams=[];
	protected $params=['IS_GECKO'=>'','IS_IE'=>'','IS_OPERA'=>'','L_CODE'=>'Code','L_CODE_SELECT'=>'[Select]','L_QUOTE'=>'Quote','L_QUOTE_FROM'=>'Quote from','L_SEARCH_ON'=>'on','SCRIPT_URL'=>'','SMILEYS_PATH'=>''];
	protected $xpath;
	public function __sleep()
	{
		$props = get_object_vars($this);
		unset($props["out"], $props["proc"], $props["source"], $props["xpath"]);

		return array_keys($props);
	}
	public function setParameter($paramName, $paramValue)
	{
		$this->params[$paramName] = (string) $paramValue;
		unset($this->dynamicParams[$paramName]);
	}
	public function renderRichText($xml)
	{
		$dom = $this->loadXML($xml);
		$this->xpath = new \DOMXPath($dom);
		$this->out = "";
		$this->at($dom->documentElement);

		return $this->out;
	}
	protected function at($root, $xpath = null)
	{
		if ($root->nodeType === 3)
		{
			$this->out .= htmlspecialchars($root->textContent,0);
		}
		else
		{
			foreach ($root->childNodes as $node)
			{
				$nodeName = $node->nodeName;if($nodeName==='html:u'){$this->out.='<u>';$this->at($node);$this->out.='</u>';}elseif($nodeName==='html:s'){$this->out.='<s>';$this->at($node);$this->out.='</s>';}elseif($nodeName==='html:ins'){$this->out.='<ins>';$this->at($node);$this->out.='</ins>';}elseif($nodeName==='html:img'){$this->out.='<img';if($node->hasAttribute('alt')){$this->out.=' alt="'.htmlspecialchars($node->getAttribute('alt'),2).'"';}if($node->hasAttribute('height')){$this->out.=' height="'.htmlspecialchars($node->getAttribute('height'),2).'"';}if($node->hasAttribute('src')){$this->out.=' src="'.htmlspecialchars($node->getAttribute('src'),2).'"';}if($node->hasAttribute('width')){$this->out.=' width="'.htmlspecialchars($node->getAttribute('width'),2).'"';}$this->out.='>';}elseif($nodeName==='html:i'){$this->out.='<i>';$this->at($node);$this->out.='</i>';}elseif($nodeName==='html:hr'){$this->out.='<hr>';}elseif($nodeName==='html:br'){$this->out.='<br>';}elseif($nodeName==='html:blockquote'){$this->out.='<blockquote>';$this->at($node);$this->out.='</blockquote>';}elseif($nodeName==='html:b'){$this->out.='<b>';$this->at($node);$this->out.='</b>';}elseif($nodeName==='html:a'){$this->out.='<a';if($node->hasAttribute('href')){$this->out.=' href="'.htmlspecialchars($node->getAttribute('href'),2).'"';}$this->out.='>';$this->at($node);$this->out.='</a>';}elseif($nodeName==='WHITE'){$this->out.='<span style="color: white;" class="bbc_color">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='URL'){$this->out.='<a href="'.htmlspecialchars($node->getAttribute('url'),2).'" class="bbc_link" target="_blank">';$this->at($node);$this->out.='</a>';}elseif($nodeName==='U'){$this->out.='<span class="bbc_u">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='TT'){$this->out.='<span class="bbc_tt">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='TR'){$this->out.='<tr>';$this->at($node);$this->out.='</tr>';}elseif($nodeName==='TIME'){$this->out.=htmlspecialchars($node->getAttribute('time'),0);}elseif($nodeName==='TD'){$this->out.='<td>';$this->at($node);$this->out.='</td>';}elseif($nodeName==='TABLE'){$this->out.='<table class="bbc_table">';$this->at($node);$this->out.='</table>';}elseif($nodeName==='SUP'){$this->out.='<sup>';$this->at($node);$this->out.='</sup>';}elseif($nodeName==='SUB'){$this->out.='<sub>';$this->at($node);$this->out.='</sub>';}elseif($nodeName==='SIZE'){$this->out.='<span style="font-size: '.htmlspecialchars($node->getAttribute('size'),2).';" class="bbc_size">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='SHADOW'){$this->out.='<span style="';if(!empty($this->params['IS_IE'])){$this->out.='display: inline-block; filter: Shadow(color='.htmlspecialchars($node->getAttribute('color'),2).', direction=';if($node->getAttribute('direction')==='left'){$this->out.='270';}elseif($node->getAttribute('direction')==='right'){$this->out.='90';}elseif($node->getAttribute('direction')==='top'){$this->out.='0';}elseif($node->getAttribute('direction')==='bottom'){$this->out.='180';}else{$this->out.=htmlspecialchars($node->getAttribute('direction'),2);}$this->out.='); height: 1.2em;';}else{$this->out.='text-shadow: '.htmlspecialchars($node->getAttribute('color'),2).' ';if($this->xpath->evaluate('@direction=\'top\'or@direction<50',$node)){$this->out.='0 -2px 1px';}elseif($this->xpath->evaluate('@direction=\'right\'or@direction<100',$node)){$this->out.='2px 0 1px';}elseif($this->xpath->evaluate('@direction=\'bottom\'or@direction<190',$node)){$this->out.='0 2px 1px';}elseif($this->xpath->evaluate('@direction=\'left\'or@direction<280',$node)){$this->out.='-2px 0 1px';}else{$this->out.='1px 1px 1px';}}$this->out.='">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='S'||$nodeName==='html:del'){$this->out.='<del>';$this->at($node);$this->out.='</del>';}elseif($nodeName==='RTL'){$this->out.='<div dir="rtl">';$this->at($node);$this->out.='</div>';}elseif($nodeName==='RIGHT'){$this->out.='<div style="text-align: right;">';$this->at($node);$this->out.='</div>';}elseif($nodeName==='RED'){$this->out.='<span style="color: red;" class="bbc_color">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='QUOTE'){$this->out.='<div class="quoteheader"><div class="topslice_quote">';if(!$node->hasAttribute('author')){$this->out.=htmlspecialchars($this->params['L_QUOTE'],0);}elseif($node->hasAttribute('date')&&$node->hasAttribute('link')){$this->out.='<a href="'.htmlspecialchars($this->params['SCRIPT_URL'],2).'?'.htmlspecialchars($node->getAttribute('link'),2).'">'.htmlspecialchars($this->params['L_QUOTE_FROM'],0).': '.htmlspecialchars($node->getAttribute('author'),0).' '.htmlspecialchars($this->params['L_SEARCH_ON'],0).' '.htmlspecialchars($node->getAttribute('date'),0).'</a>';}else{$this->out.=htmlspecialchars($this->params['L_QUOTE_FROM'],0).': '.htmlspecialchars($node->getAttribute('author'),0);}$this->out.='</div></div><blockquote>';$this->at($node);$this->out.='</blockquote><div class="quotefooter"><div class="botslice_quote"></div></div>';}elseif($nodeName==='PRE'||$nodeName==='html:pre'){$this->out.='<pre>';$this->at($node);$this->out.='</pre>';}elseif($nodeName==='MOVE'){$this->out.='<marquee>';$this->at($node);$this->out.='</marquee>';}elseif($nodeName==='ME'){$this->out.='<div class="meaction">* '.htmlspecialchars($node->getAttribute('me'),0).' ';$this->at($node);$this->out.='</div>';}elseif($nodeName==='LTR'){$this->out.='<div dir="ltr">';$this->at($node);$this->out.='</div>';}elseif($nodeName==='LIST'){if($node->hasAttribute('type')){$this->out.='<ul class="bbc_list" style="list-style-type: '.htmlspecialchars($node->getAttribute('type'),2).';">';$this->at($node);$this->out.='</ul>';}else{$this->out.='<ul class="bbc_list">';$this->at($node);$this->out.='</ul>';}}elseif($nodeName==='LI'){$this->out.='<li>';$this->at($node);$this->out.='</li>';}elseif($nodeName==='LEFT'){$this->out.='<div style="text-align: left;">';$this->at($node);$this->out.='</div>';}elseif($nodeName==='IURL'){$this->out.='<a href="'.htmlspecialchars($node->getAttribute('iurl'),2).'" class="bbc_link">';$this->at($node);$this->out.='</a>';}elseif($nodeName==='IMG'){$this->out.='<img src="'.htmlspecialchars($node->getAttribute('src'),2).'"';if($node->hasAttribute('alt')){$this->out.=' alt="'.htmlspecialchars($node->getAttribute('alt'),2).'"';}if($node->hasAttribute('height')){$this->out.=' height="'.htmlspecialchars($node->getAttribute('height'),2).'"';}if($node->hasAttribute('width')){$this->out.=' width="'.htmlspecialchars($node->getAttribute('width'),2).'"';}$this->out.=' class="bbc_img';if($node->hasAttribute('height')||$node->hasAttribute('width')){$this->out.=' resized';}$this->out.='">';}elseif($nodeName==='I'||$nodeName==='html:em'){$this->out.='<em>';$this->at($node);$this->out.='</em>';}elseif($nodeName==='HTML'||$nodeName==='NOBBC'){$this->at($node);}elseif($nodeName==='HR'){$this->out.='<hr>';}elseif($nodeName==='GREEN'){$this->out.='<span style="color: green;" class="bbc_color">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='GLOW'){if(!empty($this->params['IS_IE'])){$this->out.='<table border="0" cellpadding="0" cellspacing="0" style="display: inline; vertical-align: middle; font: inherit;"><tr><td style="filter: Glow(color='.htmlspecialchars($node->getAttribute('glow0'),2).', strength='.htmlspecialchars($node->getAttribute('glow1'),2).'); font: inherit;">';$this->at($node);$this->out.='</td></tr></table>';}else{$this->out.='<span style="text-shadow: '.htmlspecialchars($node->getAttribute('glow0'),2).' 1px 1px 1px">';$this->at($node);$this->out.='</span>';}}elseif($nodeName==='FTP'){$this->out.='<a href="'.htmlspecialchars($node->getAttribute('ftp'),2).'" class="bbc_ftp new_win" target="_blank">';$this->at($node);$this->out.='</a>';}elseif($nodeName==='FONT'){$this->out.='<span style="font-family: '.htmlspecialchars($node->getAttribute('font'),2).';" class="bbc_font">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='FLASH'){if(!empty($this->params['IS_IE'])){$this->out.='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'.htmlspecialchars($node->getAttribute('flash0'),2).'" height="'.htmlspecialchars($node->getAttribute('flash1'),2).'"><param name="movie" value="'.htmlspecialchars($node->getAttribute('content'),2).'"><param name="play" value="true"><param name="loop" value="true"><param name="quality" value="high"><param name="AllowScriptAccess" value="never"><embed src="'.htmlspecialchars($node->getAttribute('content'),2).'" width="'.htmlspecialchars($node->getAttribute('flash0'),2).'" height="'.htmlspecialchars($node->getAttribute('flash1'),2).'" play="true" loop="true" quality="high" allowscriptaccess="never"><noembed><a href="'.htmlspecialchars($node->getAttribute('content'),2).'" target="_blank" class="new_win">'.htmlspecialchars($node->getAttribute('content'),0).'</a></noembed></object>';}else{$this->out.='<embed type="application/x-shockwave-flash" src="'.htmlspecialchars($node->getAttribute('content'),2).'" width="'.htmlspecialchars($node->getAttribute('flash0'),2).'" height="'.htmlspecialchars($node->getAttribute('flash1'),2).'" play="true" loop="true" quality="high" allowscriptaccess="never"><noembed><a href="'.htmlspecialchars($node->getAttribute('content'),2).'" target="_blank" class="new_win">'.htmlspecialchars($node->getAttribute('content'),0).'</a></noembed>';}}elseif($nodeName==='EMAIL'){$this->out.='<a href="mailto:'.htmlspecialchars($node->getAttribute('email'),2).'" class="bbc_email">';$this->at($node);$this->out.='</a>';}elseif($nodeName==='COLOR'){$this->out.='<span style="color: '.htmlspecialchars($node->getAttribute('color'),2).';" class="bbc_color">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='CODE'){$this->out.='<div class="codeheader">'.htmlspecialchars($this->params['L_CODE'],0).':';if($node->hasAttribute('lang')){$this->out.=' ('.htmlspecialchars($node->getAttribute('lang'),0).')';}$this->out.=' <a href="#" onclick="return smfSelectText(this);" class="codeoperation">'.htmlspecialchars($this->params['L_CODE_SELECT'],0).'</a></div>';if(!empty($this->params['IS_GECKO'])||!empty($this->params['IS_OPERA'])){$this->out.='<pre style="margin: 0; padding: 0;"><code class="bbc_code">';$this->at($node);$this->out.='</code></pre>';}else{$this->out.='<code class="bbc_code">';$this->at($node);$this->out.='</code>';}}elseif($nodeName==='CENTER'){$this->out.='<div align="center">';$this->at($node);$this->out.='</div>';}elseif($nodeName==='BLUE'){$this->out.='<span style="color: blue;" class="bbc_color">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='BLACK'){$this->out.='<span style="color: black;" class="bbc_color">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='BDO'){$this->out.='<bdo dir="'.htmlspecialchars($node->getAttribute('bdo'),2).'">';$this->at($node);$this->out.='</bdo>';}elseif($nodeName==='B'){$this->out.='<span class="bbc_bold">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='ANCHOR'){$this->out.='<span id="post_'.htmlspecialchars($node->getAttribute('anchor'),2).'">';$this->at($node);$this->out.='</span>';}elseif($nodeName==='ACRONYM'){$this->out.='<acronym title="'.htmlspecialchars($node->getAttribute('acronym'),2).'">';$this->at($node);$this->out.='</acronym>';}elseif($nodeName==='ABBR'){$this->out.='<abbr title="'.htmlspecialchars($node->getAttribute('abbr'),2).'">';$this->at($node);$this->out.='</abbr>';}elseif($nodeName==='E'){if($node->textContent===':)'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'smiley.gif" alt=":)" title="Smiley" class="smiley">';}elseif($node->textContent===';)'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'wink.gif" alt=";)" title="Wink" class="smiley">';}elseif($node->textContent===':D'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'cheesy.gif" alt=":D" title="Cheesy" class="smiley">';}elseif($node->textContent===';D'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'grin.gif" alt=";D" title="Grin" class="smiley">';}elseif($node->textContent==='>:['){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'angry.gif" alt="&gt;:[" title="Angry" class="smiley">';}elseif($node->textContent===':['){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'sad.gif" alt=":[" title="Sad" class="smiley">';}elseif($node->textContent===':o'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'shocked.gif" alt=":o" title="Shocked" class="smiley">';}elseif($node->textContent==='8)'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'cool.gif" alt="8)" title="Cool" class="smiley">';}elseif($node->textContent==='???'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'huh.gif" alt="???" title="Huh?" class="smiley">';}elseif($node->textContent==='::)'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'rolleyes.gif" alt="::)" title="Roll Eyes" class="smiley">';}elseif($node->textContent===':P'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'tongue.gif" alt=":P" title="Tongue" class="smiley">';}elseif($node->textContent===':-['){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'embarrassed.gif" alt=":-[" title="Embarrassed" class="smiley">';}elseif($node->textContent===':-X'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'lipsrsealed.gif" alt=":-X" title="Lips Sealed" class="smiley">';}elseif($node->textContent===':-\\'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'undecided.gif" alt=":-\\" title="Undecided" class="smiley">';}elseif($node->textContent===':-*'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'kiss.gif" alt=":-*" title="Kiss" class="smiley">';}elseif($node->textContent===':\'['){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'cry.gif" alt=":\'[" title="Cry" class="smiley">';}elseif($node->textContent==='>:D'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'evil.gif" alt="&gt;:D" title="Evil" class="smiley">';}elseif($node->textContent==='^-^'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'azn.gif" alt="^-^" title="Azn" class="smiley">';}elseif($node->textContent==='O0'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'afro.gif" alt="O0" title="Afro" class="smiley">';}elseif($node->textContent===':))'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'laugh.gif" alt=":))" title="Laugh" class="smiley">';}elseif($node->textContent==='C:-)'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'police.gif" alt="C:-)" title="Police" class="smiley">';}elseif($node->textContent==='O:-)'){$this->out.='<img src="'.htmlspecialchars($this->params['SMILEYS_PATH'],2).'angel.gif" alt="O:-)" title="Angel" class="smiley">';}else{$this->out.=htmlspecialchars($node->textContent,0);}}elseif($nodeName==='et'||$nodeName==='i'||$nodeName==='st'){}elseif($nodeName==='BR'||$nodeName==='br'){$this->out.='<br>';}elseif($nodeName==='p'){$this->out.='<p>';$this->at($node);$this->out.='</p>';}else $this->at($node);
			}
		}
	}
}