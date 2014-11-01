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
	function __construct( UserOptions $options ) {
		$this->options = $options;
	}
	public function getCitation( Metadata $metadata, DateFormat $format ) {
		global $config;
		foreach ( $metadata as &$field ) { // we don't want | here
			$field = str_replace( "|", "-", $field );
		}
		$core = "{{cite web|url=" . $metadata->url;
		if ( $metadata->exists( "title" ) ) {
			$core .= "|title=" . $metadata->title;
		}
		if ( $metadata->exists( "author" ) ) {
			$core .= "|author=" . $metadata->author;
		} elseif ( $this->options->get( "addblankmetadata" ) ) { // add a blank field
			$core .= "|author=";
		}
		if ( $timestamp = strtotime( $metadata->date ) ) { // successfully parsed
			$core .= "|date=" . Utils::generateDate( $timestamp, $format );
		} elseif ( $this->options->get( "addblankmetadata" ) ) { // add a blank field
			$core .= "|date=";
		}
		if ( $metadata->exists( "work" ) ) {
			$core .= "|work=" . $metadata->work;
		} else { // no |work= extracted , add an empty |publisher=
			$core .= "|publisher=";
		}
		if ( !$this->options->get( "noaccessdate" ) ) {
			$core .= "|accessdate=" . Utils::generateDate( 0, $format );
		}
		$core .= $config['citeextra'] . "}}";
		return $core;
	}
}
