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
	Web spider
*/

namespace Reflinks;

use Reflinks\SpiderResponse;

class Spider {
	public $useragent = "";
	public $postData = array();
	
	const FAILURE_NOTHTML = 0;
	const FAILURE_RESETBYPEER = 1;
	
	function __construct( $useragent = "Reflinks/?" ) {
		$this->useragent = $useragent;
	}
	public function isAllowed( $url ) {
		// TODO: Implement this to check robots.txt of the site
	}
	public function fetch( $url, $referer = "", $htmlOnly = true ) {
		$curl = curl_init( $url );
		curl_setopt( $curl, CURLOPT_USERAGENT, $this->useragent );
		curl_setopt( $curl, CURLOPT_HEADER, true );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $curl, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 10 );
		if ( !empty( $referer ) ) curl_setopt( $curl, CURLOPT_REFERER, $referer );

		if ( !empty( $this->postData ) ) {
			$postString = http_build_query( $this->postData );
			curl_setopt( $curl, CURLOPT_POST, count( $this->postData ) );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $postString );
		}
		
		// step 1: make sure it's text/html
		if ( $htmlOnly ) {
			curl_setopt( $curl, CURLOPT_NOBODY, true );
			curl_exec( $curl );
			$header = curl_getinfo( $curl );
			if ( strpos( $header['content_type'], "text/html" ) === false ) {
				$header['failure'] = self::FAILURE_NOTHTML;
				return new SpiderResponse( false, "", $header );
			} elseif ( $header['http_code'] == 0 ) {
				$header['failure'] = self::FAILURE_RESETBYPEER;
				return new SpiderResponse( false, "", $header );
			}
		}
		
		// step 2: actually fetch the page
		curl_setopt( $curl, CURLOPT_NOBODY, false );
		curl_setopt( $curl, CURLOPT_HEADER, false ); 
		$content = curl_exec( $curl );
		$header = curl_getinfo( $curl );
		curl_close( $curl );
		if ( $header['http_code'] == 0 ) {
			$header['failure'] = self::FAILURE_RESETBYPEER;
			return new SpiderResponse( false, "", $header );
		} else {
			$response = new SpiderResponse( true, $content, $header );
			return $response;
		}
	}
}

