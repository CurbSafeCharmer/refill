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

function fixRef( $text, $plainlink = false, &$counter = 0 ) {
	$pattern = "/\<ref[^\>]*\>([^\<\>]+)\<\/ref\>/i";
	$matches = array();
	$status = 0;
	$counter = 0;
	preg_match_all( $pattern, $text, $matches );
	foreach ( $matches[1] as $key => $ref ) {
		if ( filter_var( $ref, FILTER_VALIDATE_URL ) && strpos( $ref, "http" ) === 0 ) { // a bare link
			$html = fetchWeb( $ref, null, $status );
			if ( !$html || $status != 200 ) { // failed
				continue;
			}
			$metadata = extractMetadata( $html );
			if ( $plainlink ) { // use plain links
				$core = generatePlainLink( $ref, $metadata );
			} else {
				$core = generateCiteTemplate( $ref, $metadata );
			}
			$replacement = str_replace( $ref, $core, $matches[0][$key] ); // for good measure
			$text = str_replace( $matches[0][$key], $replacement, $text );
			$counter++;
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

function fetchWeb( $url, $referer = "", &$status = "" ) {
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

function extractMetadata( $html ) {
	$dom = new DOMDocument();
	$dom->preserveWhiteSpace = false;
	$dom->loadHTML( $html );
	$xpath = new DOMXPath( $dom );
	
	$result = array();
	
	// Extract title to ['title']
	$titlenodes = $dom->getElementsByTagName( "title" );
	if ( $titlenodes->length ) { // title found
		$result['title'] = getFirstNodeValue( $titlenodes );
	}
	
	// Extract author to ['author']
	$authornodes = $xpath->query( "//*[@itemprop='author']" ); // 1st try - schema.org
	if ( $authornodes->length ) { // author found
		$result['author'] = getFirstNodeValue( $authornodes );
	} else { // 2nd try - <meta name="author">
		$authornodes = $xpath->query( "//meta[@name='author']" );
		if ( $authornodes->length ) {
			$result['author'] = getFirstNodeAttrContent( $authornodes );
		}
	}
	
	// Extract publication date to ['date']
	$datenodes = $xpath->query( "//*[@itemprop='datePublished'] | //meta[@name='date' or @name='article:published_time' or @name='sailthru.date']" );
	if ( $datenodes->length ) { // date found
		$date = getFirstNodeAttrContent( $datenodes );
		if ( $timestamp = strtotime( $date ) ) { // successfully parsed
			$result['date'] = date( "j F Y", $timestamp );
		}
	}

	
	// Extract website name to ['work']
	$worknodes = $xpath->query( "//meta[@property='og:site_name']" );
	if ( $worknodes->length ) {
		$result['work'] = getFirstNodeAttrContent( $worknodes );
	}
	
	// Guess website name from title to ['guessedwork']
	if ( isset( $result['title'] ) ) {
		// Is it something like "Article name & whatever - Site name"?
		$workpattern = "/.+ [\-\|] ([^\-\|]*)$/";
		$matches = array();
		if ( preg_match( $workpattern, $result['title'], $matches ) ) {
			$result['guessedwork'] = $matches[1][0];
		}
	}
	
	return $result; // Done! ;)
}

function getFirstNodeValue( $nodelist ) {
	return ltrim( rtrim( $nodelist->item( 0 )->nodeValue ) );
}

function getFirstNodeAttrContent( $nodelist ) {
	return ltrim( rtrim( $nodelist->item( 0 )->attributes->getNamedItem( "content" )->nodeValue ) );
}

function generatePlainLink( $url, $metadata ) {
	$title = $metadata['title'];
	return "[$url $title]";
}

function generateCiteTemplate( $url, $metadata ) {
	$date = date( "j F Y" );
	$stitle = str_replace( "|", "-", $metadata['title'] );
	$core = "{{cite web|url=$url|title=$stitle";
	if ( isset( $metadata['author'] ) ) {
		$core .= "|author=" . $metadata['author'];
	}
	if ( isset( $metadata['date'] ) ) {
		$core .= "|date=" . $metadata['date'];
	}
	if ( isset( $metadata['work'] ) ) {
		$core .= "|work=" . $metadata['work'];
	}
	// Let's not use guesswork now, as it's unstable
	$core .= "|accessdate=$date}}";
	return $core;
}
