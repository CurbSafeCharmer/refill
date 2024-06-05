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
	schema.org metadata parser
*/

namespace Reflinks\MetadataParsers;

use Reflinks\MetadataParser;
use Reflinks\Metadata;
use Reflinks\Utils;

class SchemaOrgMetadataParser extends MetadataParser {
	public function parse( \DOMDocument $dom ) {
		$xpath = Utils::getXpath( $dom );
		$result = new Metadata();

		$authornodes = $xpath->query( "//x:*[@itemprop='author']" );
		if ( $authornodes->length ) { // author found
			if ( $authornodes->item( 0 )->childNodes->length > 1 ) { // It has child nodes!
				$authornodes = $xpath->query( "//x:*[@itemprop='author']//*[@itemprop='name']" ); // dirty...
				if ( $authornodes->length ) {
					$result->addAuthors( Utils::getFirstNodeValue( $authornodes ) );
				}
			} else { // Okay, simple one...
				$result->addAuthors( Utils::getFirstNodeValue( $authornodes ) );
			}
		}

		$datenodes = $xpath->query( "//x:*[@itemprop='datePublished']" );
		if ( $datenodes->length ) { // date found
			$result->date = Utils::getFirstNodeAttrContent( $datenodes );
		}

		return $result;
	}
}
