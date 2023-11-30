<?php
/*
	Copyright (c) 2014, Zhaofeng Li
	All rights reserved.
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	* Redistributions of source code must retain the above copyright notice, this
	list of conditions and the following disclaimer.
	* Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
	FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
	DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
	SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
	CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
	OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
	OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/*
	Quick-and-dirty MediaWiki API
*/

namespace Reflinks;

class Wiki {
	public $api;
	public $indexphp;
	public $name = false;
	public $language = "en";
	
	function __construct( $api = "https://en.wikipedia.org/w/api.php", $indexphp = "https://en.wikipedia.org/w/index.php" ) {
		$this->api = $api;
		$this->indexphp = $indexphp;
	}
	public function fetchPage( $page, Spider $spider = null ) {
		global $config;
		$result = array();
		if ( $spider === null ) {
			$spider = new Spider( $config['useragent'] );
		}
		$url = $this->api . "?action=query&prop=revisions&rvlimit=1&rvprop=content|timestamp&format=json&titles=" . urlencode( $page );
		$response = $spider->fetch( $url, '', false );
		$result = json_decode( $response->html, true );
		foreach( $result['query']['pages'] as $page ) {
			if ( isset( $page['missing'] ) ) {
				$result['successful'] = false;
				return $result;
			} else {
				$result['successful'] = true;
				$result['actualname'] = $page['title'];
				$result['timestamp'] = strtotime( $page['revisions'][0]['timestamp'] );
				$result['wikitext'] = $page['revisions'][0]['*'];
				return $result;
			}
		}
	}
}
