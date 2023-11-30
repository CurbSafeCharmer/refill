<?php
/*
	Copyright (c) 2015, Zhaofeng Li
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
	New York Times Link Handler

	This LinkHandler can only handle `nytimes.com` URLs. It needs an API key which 
	can be acquired at no charge on http://developer.nytimes.com/

	On the registration form, select the "Article Search API" option. Paste the key 
	for the Article Search API onto $config['nyt-articlesearch-key'].
*/

namespace Reflinks\LinkHandlers;

use Reflinks\Utils;
use Reflinks\LinkHandler;
use Reflinks\Exceptions\LinkHandlerException;
use Reflinks\Spider;
use Reflinks\Metadata;

class NewYorkTimesLinkHandler extends LinkHandler {
	protected $api = "";
	protected $key = "";
	protected $spider = null;

	const ERROR_UNKNOWN = 0;
	const ERROR_FETCH = 1;
	const ERROR_HTTPERROR = 2;
	const ERROR_EMPTY = 3; 
	const ERROR_UNSUPPORTED = 4;
	const ERROR_NOTFOUND = 5;
	const ERROR_NOAPIKEY = 6;

	function __construct( Spider $spider ) {
		global $config;
		$this->spider = $spider;
		$this->api = $config['nyt-articlesearch-api'];
		$this->key = $config['nyt-articlesearch-key'];
	}

	public function getMetadata( $url, Metadata $baseMetadata = null ) {
		if ( "nytimes.com" !==Utils::getBaseDomain( $url ) ) {
			throw new LinkHandlerException( "Unsupported URL", self::ERROR_UNSUPPORTED );
		} else if ( empty( $this->key ) ) { // no key...
			throw new LinkHandlerException( "No API key configured", self::ERROR_NOAPIKEY );
		} else { // Let's do it!
			if ( 0 === strpos( $url, "https://" ) ) { // Won't work with HTTPS
				$url = "http://" . substr( $url, 8 );
			}
			if ( false !== $qpos = strpos( $url, "?" ) ) { // Strip the query string off the URL
				$url = substr( $url, 0, $qpos );
			}
			$query = array(
				"api-key" => $this->key, // our API key
				"fl" => "headline,byline,pub_date,source,web_url", // what we need in the output
				"fq" => "web_url:(\"" . urlencode( $url ) . "\")", // our lookup criteron
			);
			$api = $this->api . "?";
			foreach ( $query as $key => $value ) {
				$api .= "$key=$value&"; // Remember to sanitize user input earlier
			}
			$api = rtrim( $api, "&" );
			$response = $this->spider->fetch( $api, "", false );
			if ( !$response->successful ) { // failed
				throw new LinkHandlerException( "Fetching error", self::ERROR_FETCH );
			} elseif ( $response->header['http_code'] != 200 ) { // http error
				throw new LinkHandlerException( "HTTP Error: " . $response->header['http_code'], self::ERROR_HTTPERROR, $response->header );
			} elseif ( empty( $response->html ) ) { // empty response
				throw new LinkHandlerException( "Empty response", self::ERROR_EMPTY );
			}
			$array = json_decode( $response->html, true );
			if ( !$array['response']['meta']['hits'] ) {
				throw new LinkHandlerException( "Not found in NYT catalog", self::ERROR_NOTFOUND );
			}
			$metadata = new Metadata();
			$info = $array['response']['docs'][0];
			$metadata->url = $info['web_url']; // Use the clean URL
			$metadata->title = $info['headline']['main']; // Maybe "print_headline" suits better? Not sure.
			$metadata->date = $info['pub_date'];
			$metadata->work = $info['source'];
			if ( $info['source'] !== "The New York Times" ) {
				$metadata->via = "The New York Times";
			}
			return $metadata;
		}
	}
}

