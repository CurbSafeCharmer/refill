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
	Metadata parsing
*/

require_once __DIR__ . "/constants.php";

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
	if ( !empty( $metadata['work'] ) ) {
		// |work=Google Books
		if ( $metadata['work'] == "Google Books" ) {
			unset( $metadata['work'] );
		}
		// |work=Los Angeles Times Articles -> |work=Los Angeles Times
		if ( $metadata['work'] == "Los Angeles Times Articles" ) {
			$metadata['work'] = "Los Angeles Times";
		}
	}
	
	return $metadata;
}

function getFirstNodeValue( $nodelist ) {
	return trim( $nodelist->item( 0 )->nodeValue );
}

function getFirstNodeAttrContent( $nodelist ) {
	return trim( $nodelist->item( 0 )->attributes->getNamedItem( "content" )->nodeValue );
}
