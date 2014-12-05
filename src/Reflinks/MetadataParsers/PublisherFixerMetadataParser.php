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
	Publisher metadata fixer
	
	This parser, when used in a MetadataParserChain, fills in
	the "publisher" field according to a hard-coded list.
*/

namespace Reflinks\MetadataParsers;

use Reflinks\MetadataParser;
use Reflinks\Metadata;
use Reflinks\Utils;

class PublisherFixerMetadataParser extends MetadataParser {
	public $publishers = array(
		"CBS Interactive" => array(
			"download.com", "cnet.com", "cbs.com", "tv.com"
		),
		"Gawker Media" => array(
			"gawker.com", "lifehacker.com", "kotaku.com", "gizmodo.com"
		),
		"Vox Media" => array(
			"theverge.com", "sbnation.com"
		),
		"Microsoft" => array(
			"microsoft.com", "msdn.com"
		),
		"AOL" => array(
			"engadget.com", "techcrunch.com"
		)
	);
	public function parse( \DOMDocument $dom ) {}
	public function chain( \DOMDocument $dom, Metadata &$metadata ) {
		if ( empty( $metadata->url ) ) return;
		$domain = Utils::getBaseDomain( $metadata->url );
		foreach ( $this->publishers as $publisher => $domains ) {
			if ( in_array( $domain, $domains ) ) {
				$metadata->publisher = $publisher;
				return;
			}
		}
	}
}
