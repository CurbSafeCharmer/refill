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
require __DIR__ . "/config.php";

function fixRef( $text, $plainlink = false ) {
	$pattern = "/\<ref[^\>]*\>([^\<\>]+)\<\/ref\>/i";
	$matches = array();
	$status = 0;
	preg_match_all( $pattern, $text, $matches );
	foreach ( $matches[1] as $key => $ref ) {
		if ( filter_var( $ref, FILTER_VALIDATE_URL ) && strpos( $ref, "http" ) === 0 ) { // a bare link
			$html = fetchWeb( $ref, null, $status );
			if ( !$html || $status != 200 ) { // failed
				continue;
			}
			$dom = new DOMDocument();
			$dom->loadHTML( $html );
			$titlenodes = $dom->getElementsByTagName( "title" );
			if ( !$titlenodes->length ) { // no title found
				continue;
			}
			$title = ltrim( rtrim( $titlenodes->item( 0 )->nodeValue ) );
			if ( $plainlink ) { // use plain links
				$core = generatePlainLink( $ref, $title );
			} else {
				$core = generateCiteTemplate( $ref, $title );
			}
			$replacement = str_replace( $ref, $core, $matches[0][$key] ); // for good measure
			$text = str_replace( $matches[0][$key], $replacement, $text );
		}
	}
	return $text;
}

function fetchWiki( $page, &$actualname = "" ) { // bug-prone
	global $config;
	$url = $config['api'] . "?action=query&prop=revisions&rvlimit=1&rvprop=content&format=json&titles=" . urlencode( $page );
	$curl = curl_init( $url );
	curl_setopt( $curl, CURLOPT_USERAGENT, $config['useragent'] );
	curl_setopt( $curl, CURLOPT_HEADER, false );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $curl, CURLOPT_MAXREDIRS, 10 );
	$result = json_decode( curl_exec( $curl ), true );
	curl_close( $curl );
	foreach( $result['query']['pages'] as $page ) {
		if ( isset( $page['missing'] ) )
			return;
		else {
			$actualname = $page['title'];
			return $page['revisions'][0]['*'] ;
		}
	}
}

function fetchWeb( $url, $referer, &$status ) {
	global $config;
	$curl = curl_init( $url );
	curl_setopt( $curl, CURLOPT_USERAGENT, $config['useragent'] );
	curl_setopt( $curl, CURLOPT_HEADER, true );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $curl, CURLOPT_MAXREDIRS, 10 );
	curl_setopt( $curl, CURLOPT_TIMEOUT, 10 );
	if ( $referer ) curl_setopt( $curl, CURLOPT_REFERER, $referer );
	
	// step 1: make sure it's text/html
	curl_setopt( $curl, CURLOPT_NOBODY, true );
	curl_exec( $curl );
	$header = curl_getinfo( $curl );
	if ( strpos( $header['content_type'], "text/html" ) !== 0 ) {
		return;
	}
	
	// step 2: actually fetch the page
	curl_setopt( $curl, CURLOPT_NOBODY, false );
	$content = curl_exec( $curl );
	$header = curl_getinfo( $curl );
	curl_close( $curl );
	$status = $header['http_code'];
	return $content;
}

function generatePlainLink( $url, $caption ) {
	return "[$url $caption]";
}

function generateCiteTemplate( $url, $caption ) {
	$date = date( "j F Y" );
	$scaption = str_replace( "|", "-", $caption );
	return "{{cite web|url=$url|title=$scaption|accessdate=$date}}";
}
