<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2019 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Utils\Http;
abstract class Client
{
	public $sslVerifyPeer = \false;
	public $timeout = 10;
	abstract public function get($url, $headers = array());
	abstract public function post($url, $headers = array(), $body = '');
}