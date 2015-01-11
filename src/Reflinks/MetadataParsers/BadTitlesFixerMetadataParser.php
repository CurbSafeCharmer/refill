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
	Bad titles metadata fixer
	
	This parser, when used in a MetadataParserChain, clears bad titles like
	"Cookies must be enabled", "No cookies", etc.
*/

namespace Reflinks\MetadataParsers;

use Reflinks\MetadataParser;
use Reflinks\Metadata;

class BadTitlesFixerMetadataParser extends MetadataParser {
	public $blacklist = array(
		"Log in",
		"Sign in",
		"Not found",
		"Forbidden",
		"Cookies must be enabled",
		"No cookies",
	);
	public function parse( \DOMDocument $dom ) {}
	public function chain( \DOMDocument $dom, Metadata &$metadata ) {
		foreach ( $this->blacklist as $entry ) {
			if ( stripos( $metadata->title, $entry ) === 0 && strlen( $metadata->title ) < 2 * strlen( $entry ) ) {
				$metadata->title = "";
			}
		}
	}
}
