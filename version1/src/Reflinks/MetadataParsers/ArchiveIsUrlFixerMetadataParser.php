<?php
/*
	Copyright (c) 2017, Zhaofeng Li
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
	Archive.is URL fixer

	Per <https://en.wikipedia.org/wiki/Wikipedia_talk:Using_archive.is#RfC:_Should_we_use_short_or_long_format_URLs.3F>,
	long URLs are referred over short ones. This MetadataParser, when used in
	a chain, expands the short URLs.
*/

namespace Reflinks\MetadataParsers;

use Reflinks\MetadataParser;
use Reflinks\Metadata;
use Reflinks\Utils;
use Purl\Url;

class ArchiveIsUrlFixerMetadataParser extends MetadataParser {
	public $domains = array(
		"archive.is",
		"archive.fo",
		"archive.li",
		"archive.today",
	);
	public function parse( \DOMDocument $dom ) {}
	public function chain( \DOMDocument $dom, Metadata &$metadata ) {
		if ( !isset( $metadata->url ) ) {
			return;
		}

		$parsed = parse_url( $metadata->url );
		if ( !in_array( $parsed['host'], $this->domains ) ) {
			return;
		}

		$xpath = Utils::getXpath( $dom );
		$metadata->url = $this->getUrlFromDocument( $xpath );

		$oinfo = $this->getOriginalInfo( $metadata->url );
		if ( $oinfo ) {
			$metadata->archiveurl = $metadata->url;
			$metadata->archivedate = $oinfo['date'];
			$metadata->url = $oinfo['url'];
		}
	}

	protected function getUrlFromDocument( \DOMXPath $xpath ) {
		$nodes = $xpath->query( "//x:input[@id='SHARE_LONGLINK']" );
		if ( !$nodes->length ) {
			return;
		}

		$purl = new Url(trim( $nodes->item( 0 )->attributes->getNamedItem( "value" )->nodeValue ));
		if ( $purl->get('scheme') == 'http' ) {
			$purl->set('scheme', 'https');
		}
		$purl->set( 'path', preg_replace( '|^\/(\d{4})\.(\d{2})\.(\d{2})\-(\d{6})\/|', '/$1$2$3$4/', $purl->get('path') ) );
		return $purl->getUrl();
	}

	protected function getOriginalInfo( $url ) {
		if ( preg_match( '|^https?\:\/\/[A-Za-z\.]+\/(\d{8})\d{6}\/(.+)$|', $url, $matches ) ) {
			return ['url' => $matches[2], 'date' => $matches[1]];
		}
		return false;
	}
}
