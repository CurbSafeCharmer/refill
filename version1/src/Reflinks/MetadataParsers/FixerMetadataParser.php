<?php
/*
	Copyright (c) 2016, Zhaofeng Li
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
	Metadata fixer as a parser

	This parser, when used in a MetadataParserChain,  cleans up the metadata
	using hard-coded rules.
*/

namespace Reflinks\MetadataParsers;

use Reflinks\MetadataParser;
use Reflinks\Metadata;
use Reflinks\Utils;

class FixerMetadataParser extends MetadataParser {
	public function parse( \DOMDocument $dom ) {}
	public function chain( \DOMDocument $dom, Metadata &$metadata ) {
		if ( $metadata->work == "Google Books" ) {
			unset( $metadata->work );
		}

		if ( $metadata->work == "Los Angeles Times Articles" ) {
			$metadata->work = "Los Angeles Times";
		}

		if ( $metadata->work == "YouTube" ) {
			$metadata->via = "YouTube";
			unset( $metadata->work );
		}

		if ( $metadata->exists( "authors" ) ) {
			foreach ( $metadata->authors as &$author ) {
				$author = preg_replace( "/(?:by|from)\s+(.+)/i", "$1", $author ); // clean it up a bit
				if ( preg_match( "/(www.|.com|\w{5,}\.\w{2,3})/", $author ) ) { // looks like a domain name (Actually, there are exceptions, like will.i.am)
					unset( $author );
				}
				if (
					Utils::endsWith( $author, "corporation", true ) ||
					Utils::endsWith( $author, "company", true )
				) {
					$metadata->publisher = $author;
					unset( $author );
				}
				if ( $author == $metadata->publisher ) {
					unset( $author );
				}
			}
		}
	}
}
