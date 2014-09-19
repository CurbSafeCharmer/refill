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
require_once __DIR__ . "/config.php";

define( "SKIPPED_NOTBARE", 1 ); // UNUSED
define( "SKIPPED_HTTPERROR", 2 );
define( "SKIPPED_EMPTY", 3 );
define( "SKIPPED_NOTITLE", 4 );
define( "SKIPPED_HOSTBL", 5 );

define( "DATE_DMY", false ); // default
define( "DATE_MDY", true );

function fixRef( $source, &$log = "", $options = array() ) {
	global $config;
	$pattern = "/\<ref[^\>]*\>([^\<\>]+)\<\/ref\>/i";
	$matches = array();
	$status = 0;
	$log = array(
		'fixed' => array(), // ['url'] contains the original link
		'skipped' => array(), // ['ref'] contains the original ref, ['reason'] contains the reason const, ['status'] contains the status code
	);
	$dateformat = detectDateFormat( $source );
	preg_match_all( $pattern, $source, $matches );
	foreach ( $matches[1] as $key => $core ) {
		$oldref = array();
		// Let's check if we are supposed to mess with it first...
		if ( preg_match( "/\{\{(Dead link|404|dl|dead|Broken link)/i", $core ) ) { // dead link tag
			continue;
		}
 
		// Let's find out what kind of reference it is...
		$tcore = trim( $core );
		if ( filter_var( $tcore, FILTER_VALIDATE_URL ) && strpos( $tcore, "http" ) === 0 ) {
			// a bare link (consists of only a URL)
			$oldref['url'] = $tcore;
		} elseif ( preg_match( "/^\[(http[^\] ]+) ([^\]]+)\]/i", $tcore, $cmatches ) ) {
			// a captioned plain link (consists of a URL and a caption, surrounded with [], possibly with other stuff after it)
			if ( filter_var( $cmatches[1], FILTER_VALIDATE_URL ) && !$options['nofixcplain'] ) {
				$oldref['url'] = $cmatches[1];
				$oldref['caption'] = $cmatches[2];
			} else {
				continue;
			}
		} elseif ( preg_match( "/^\[(http[^ ]+)\]$/i", $tcore, $cmatches ) ) {
			// an uncaptioned plain link (consists of only a URL, surrounded with [])
			if ( filter_var( $cmatches[1], FILTER_VALIDATE_URL ) && !$options['nofixuplain'] ) {
				$oldref['url'] = $cmatches[1];
			} else {
				continue;
			}
		} else {
			// probably already filled in, let's skip it
			continue;
		}
		
		// Check if it's blacklisted
		foreach( $config['hostblacklist'] as $blentry ) {
			if ( preg_match( "/^" . $blentry . "$/", parse_url( $oldref['url'], PHP_URL_HOST ) ) ) { // blacklisted
				$log['skipped'][] = array(
					'ref' => $core,
					'reason' => SKIPPED_HOSTBL,
					'status' => $status,
				);
				continue 2;
			}
		}
		
		// Fetch the webpage and extract the metadata
		$html = fetchWeb( $oldref['url'], null, $status );
		if ( $status != 200 ) { // failed
			$log['skipped'][] = array(
				'ref' => $core,
				'reason' => SKIPPED_HTTPERROR,
				'status' => $status,
			);
			continue;
		} elseif ( !$html ) { // empty response
			$log['skipped'][] = array(
				'ref' => $core,
				'reason' => SKIPPED_EMPTY,
				'status' => $status,
			);
			continue;
		}
		$metadata = extractMetadata( $html );
		if ( isset( $oldref['caption'] ) && !$options['nouseoldcaption'] ) {
			// Use the original caption
			$metadata['title'] = $oldref['caption'];
		}
		
		if ( empty( $metadata['title'] ) ) {
			$log['skipped'][] = array(
				'ref' => $core,
				'reason' => SKIPPED_NOTITLE,
				'status' => $status,
			);
			continue;
		}
		
		// Generate cite template
		if ( isset( $options['plainlink'] ) ) { // use captioned plain link
			$newcore = generatePlainLink( $oldref['url'], $metadata, $dateformat );
		} else { // use {{cite web}}
			$newcore = generateCiteTemplate( $oldref['url'], $metadata, $dateformat );
		}
		
		// Replace the old core
		$replacement = str_replace( $core, $newcore, $matches[0][$key] ); // for good measure
		$source = str_replace( $matches[0][$key], $replacement, $source );
		$log['fixed'][] = array(
			'url' => $oldref['url'],
		);
	}
	return $source;
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
	@$dom->loadHTML( "<?xml encoding='utf-8'?>" . $html );
	$xpath = new DOMXPath( $dom );
	
	$result = array();
	
	// Extract title to ['title']
	$titlenodes = $xpath->query( "//title" );
	if ( $titlenodes->length ) {
		$result['title'] = getFirstNodeValue( $titlenodes );
	}
	
	$titles = array();
	$titlenodes = $xpath->query( "//*[@itemprop='headline'] | //h1" );
	if ( $titlenodes->length ) {
		for ( $i = 0; $i < $titlenodes->length; $i++ ) {
			$titles[] = trim( $titlenodes->item( $i )->nodeValue );
		}
	}
	$titlenodes = $xpath->query( "//meta[@property='og:title']" );
	if ( $titlenodes->length ) {
		$titles[] = getFirstNodeAttrContent( $titlenodes );
	}
	
	foreach ( $titles as $title ) { // loop through the titles we found...
		if ( !empty( $title ) && strlen( $title ) < strlen( $result['title'] ) && strpos( $result['title'], $title ) === 0 ) {
			$result['title'] = $title;
		}
	}
	
	// Extract author to ['author']
	$authornodes = $xpath->query( "//*[@itemprop='author']" ); // 1st try - schema.org
	if ( $authornodes->length ) { // author found
		if ( $authornodes->item( 0 )->childNodes->length ) { // It has child nodes!
			$authornodes = $xpath->query( "//*[@itemprop='author']//*[@itemprop='name']" ); // dirty...
			if ( $authornodes->length ) {
				$result['author'] = getFirstNodeValue( $authornodes );
			}
		} else { // Okay, simple one...
			$result['author'] = getFirstNodeValue( $authornodes );
		}
	} else { // 2nd try - <meta name="author">
		$authornodes = $xpath->query( "//meta[@name='author']" );
		if ( $authornodes->length ) {
			$author = getFirstNodeAttrContent( $authornodes );
			if ( !preg_match( "/(www.|.com|\w{5,}.\w{2,3})/", $author ) ) { // does not look like a domain name (Actually, there are exceptions, like will.i.am)
				$result['author'] = preg_replace( "/(?:by|from)\s+(.+)/i", "$1", $author ); // clean it up a bit
			}
		}
	}
	
	// Extract publication date to ['date']
	$datenodes = $xpath->query( "//*[@itemprop='datePublished'] | //meta[@name='date' or @name='article:published_time' or @name='sailthru.date']" );
	if ( $datenodes->length ) { // date found
		$result['date'] = getFirstNodeAttrContent( $datenodes );
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
	
	// A dirty way to make those case-by-case adjustments
	$result = fixMetadata( $result );
	
	return $result; // Done! ;)
}

function fixMetadata( $metadata ) {
	// |work=Google Books
	if ( $metadata['work'] == "Google Books" ) {
		unset( $metadata['work'] );
	}
	
	// |work=Los Angeles Times Articles -> |work=Los Angeles Times
	if ( $metadata['work'] == "Los Angeles Times Articles" ) {
		$metadata['work'] = "Los Angeles Times";
	}
	
	return $metadata;
}

function getFirstNodeValue( $nodelist ) {
	return trim( $nodelist->item( 0 )->nodeValue );
}

function getFirstNodeAttrContent( $nodelist ) {
	return trim( $nodelist->item( 0 )->attributes->getNamedItem( "content" )->nodeValue );
}

function generatePlainLink( $url, $metadata, $dateformat = DATE_DMY ) {
	$title = $metadata['title'];
	$core = "[$url $title] Retrieved on " . generateDate( $dateformat ) . ".";
	return $core;
}

function generateCiteTemplate( $url, $metadata, $dateformat = DATE_DMY ) {
	global $config;
	$date = date( "j F Y" );
	foreach ( $metadata as &$field ) { // we don't want | here
		$field = str_replace( "|", "-", $field );
	}
	$core = "{{cite web|url=$url";
	if ( !empty( $metadata['title'] ) ) {
		$core .= "|title=" . $metadata['title'];
	}
	if ( !empty( $metadata['author'] ) ) {
		$core .= "|author=" . $metadata['author'];
	}
	if ( !empty( $metadata['date'] ) && $timestamp = strtotime( $metadata['date'] ) ) { // successfully parsed
		$core .= "|date=" . generateDate( $dateformat, $timestamp );
	}
	if ( !empty( $metadata['work'] ) ) {
		$core .= "|work=" . $metadata['work'];
	} else { // no |work= extracted , add an empty |publisher=
		$core .= "|publisher=";
	}
	// Let's not use guesswork now, as it's unstable
	$core .= "|accessdate=" . generateDate( $dateformat );
	$core .= $config['citeextra'] . "}}";
	return $core;
}

function generateWikiTimestamp() {
	return date( "YmdHis" );
}

function generateDate( $format, $timestamp = 0 ) {
	if ( !$timestamp ) {
		$timestamp = time();
	}
	if ( $format == DATE_MDY ) { // mdy
		return date( "F j, Y", $timestamp );
	} else { // dmy (default)
		return date( "j F Y", $timestamp );
	}
}

function getSkippedReason( $code ) {
	switch ( $code ) {
		case SKIPPED_HTTPERROR:
			return "HTTP Error";
		case SKIPPED_EMPTY:
			return "Empty response or not HTML";
		case SKIPPED_NOTITLE:
			return "No title is found";
		case SKIPPED_HOSTBL:
			return "Host blacklisted";
		default:
			return "Unknown error";
	}
}

// Remove all bare URL tags. Use only if all bare links are fixed.
function removeBareUrlTags( $source ) {
	$pattern = "/\{\{(Bare|Bare links|Barelinks|Bare references|Bare refs|Bare URLs|Cleanup link rot|Cleanup link-rot|Cleanup-link-rot|Cleanup-linkrot|Link rot|Linkrot|Cleanup-bare URLs)([^\}])*\}\}/i";
	return preg_replace( $pattern, "", $source );
}

// DATE_DMY if dmy (default), DATE_MDY if mdy
function detectDateFormat( $source ) {
	if ( stripos( $source, "{{Use mdy dates" ) !== false ) {
		return DATE_MDY;
	} else {
		return DATE_DMY;
	}
}

function getOption( $option ) {
	if ( isset( $_GET[$option] ) ) {
		return empty( $_GET[$option] ) ? true : $_GET[$option];
	} elseif ( isset( $_POST[$option] ) ) {
		return empty( $_POST[$option] ) ? true : $_POST[$option];
	} else {
		return null;
	}
}

function getOptions() {
	$optionlist = array( 'text', 'page', 'plainlink', 'nofixuplain', 'nofixcplain', 'nouseoldcaption', 'noremovetag' );
	$options = array();
	foreach( $optionlist as $option ) {
		if ( null !== $o = getOption( $option ) ) {
			$options[$option] = $o;
		}
	}
	return $options;
}
