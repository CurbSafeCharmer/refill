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
	Title parser
*/

namespace Reflinks\MetadataParsers;

use Reflinks\MetadataParser;
use Reflinks\Metadata;
use Reflinks\Utils;

class TitleMetadataParser extends MetadataParser {
	public function parse( \DOMDocument $dom ) {
		$xpath = Utils::getXpath( $dom );
		$result = new Metadata();

		$titlenodes = $xpath->query( "//x:title" );
		if ( $titlenodes->length ) {
			$result->title = Utils::getFirstNodeValue( $titlenodes );
		}

		$titles = array();
		$titlenodes = $xpath->query( "//x:*[@itemprop='headline'] | //x:h1" );
		if ( $titlenodes->length ) {
			for ( $i = 0; $i < $titlenodes->length; $i++ ) {
				$titles[] = trim( $titlenodes->item( $i )->nodeValue );
			}
		}

		$titlenodes = $xpath->query( "//x:meta[@property='og:title'] | //x:meta[@name='sailthru.title']" );
		if ( $titlenodes->length ) {
			$titles[] = Utils::getFirstNodeAttrContent( $titlenodes );
		}

		foreach ( $titles as $title ) { // loop through the titles we found...
			if ( !$result->exists( "title" ) ) {
				$result->title = $title;
			} elseif ( !empty( $title ) && strlen( $title ) < strlen( $result->title ) && strpos( $result->title, $title ) === 0 ) {
				$result->title = $title;
			}
		}
		$result->title = str_replace( "\r", "", $result->title );
		$result->title = str_replace( "\n", "", $result->title );
		return $result;
	}
}
