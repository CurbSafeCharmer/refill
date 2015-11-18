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
	Cite template generator
*/

namespace Reflinks\CitationGenerators;

use Reflinks\CitationGenerator;
use Reflinks\UserOptions;
use Reflinks\Metadata;
use Reflinks\DateFormat;
use Reflinks\Utils;

class CiteTemplateGenerator extends CitationGenerator {
	public $options;
	public $dateFormat;
	function __construct( UserOptions $options, DateFormat $dateFormat ) {
		$this->options = $options;
		$this->dateFormat = $dateFormat;
	}
	public function getCitation( Metadata $metadata ) {
		global $config;
		foreach ( $metadata as $key => $value ) { // we don't want | here
			if ( $key != "url" ) {
				$metadata->set( $key, str_replace( "|", "-", $value ) );
			}
		}
		$metadata->url = str_replace( "|", "%7c", $metadata->url );
		// Type
		if ( $metadata->exists( "type" ) ) {
			$core = "{{cite " . $metadata->type;
		} else {
			$core = "{{cite web";
		}
		// URL
		$core .= "|url=" . $metadata->url;
		// Archive URL
		if ( $metadata->exists( "archiveurl" ) ) {
			$core .= "|archiveurl=" . $metadata->archiveurl;
		}
		// Title
		if ( $metadata->exists( "title" ) ) {
			$core .= "|title=" . $metadata->title;
		}
		// Author
		if ( $metadata->exists( "author" ) ) {
			$core .= "|author=" . $metadata->author;
		} elseif ( $this->options->get( "addblankmetadata" ) ) { // add a blank field
			$core .= "|author=";
		}
		// Date
		if ( $timestamp = strtotime( $metadata->date ) ) { // date
			$core .= "|date=" . Utils::generateDate( $timestamp, $this->dateFormat );
		} elseif ( $this->options->get( "addblankmetadata" ) ) { // add a blank field
			$core .= "|date=";
		}
		// Archive date
		if ( $archivets = strtotime( $metadata->archivedate ) ) { // archivedate
			$core .= "|archivedate=" . Utils::generateDate( $archivets, $this->dateFormat );
		}
		// Publisher
		if ( $metadata->exists( "publisher" ) ) {
			$core .= "|publisher=" . $metadata->publisher;
		}
		// Work (and an empty |publisher=)
		if ( $metadata->exists( "work" ) ) {
			$core .= "|work=" . $metadata->work;
		} elseif( !$metadata->exists( "publisher" ) ) { // no |work= or |publisher= extracted, add an empty |publisher=
			$core .= "|publisher=";
		}
		// Access date
		if ( !$this->options->get( "noaccessdate" ) ) {
			$core .= "|accessdate=" . Utils::generateDate( 0, $this->dateFormat );
		}
		// Via
		if ( $metadata->exists( "via" ) ) {
			$core .= "|via=" . $metadata->via;
		}
		$core .= $config['citeextra'] . "}}";
		return $core;
	}
}
