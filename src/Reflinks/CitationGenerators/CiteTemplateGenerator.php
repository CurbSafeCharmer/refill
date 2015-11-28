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
	Cite template generator
*/

namespace Reflinks\CitationGenerators;

use Reflinks\CitationGenerator;
use Reflinks\UserOptions;
use Reflinks\Metadata;
use Reflinks\DateFormat;
use Reflinks\Utils;
use Reflinks\Wiki;

class CiteTemplateGenerator extends CitationGenerator {
	public $options;
	public $dateFormat;
	public $lang = "en";
	public $wiki = null;
	public $i18n = null;

	function __construct( UserOptions $options, DateFormat $dateFormat ) {
		$this->options = $options;
		$this->dateFormat = $dateFormat;
	}

	public function setWikiContext( Wiki $wiki ) {
		$this->wiki = $wiki;
		if ( !$this->wiki->language ) {
			$this->lang = "en";
		} else {
			$this->lang = $this->wiki->language;
		}
	}

	public function setI18n( $i18n ) {
		$this->i18n = $i18n;
	}
	
	protected function getMessage( $key, $fallback = false ) {
		if (
			$this->i18n && 
			$this->i18n->msgExists( $key, array( "lang" => $this->lang ) )
		) { 
			return $this->i18n->msg( $key, array( "lang" => $this->lang ) );
		} else { // fallback to $fallback
			return $fallback;
		}

	}

	public function getTemplateName( $type = "web" ) {
		if ( empty( $type ) ) return false;
		$typekey = str_replace( " ", "-", $type );
		return $this->getMessage( "wikitext-template-$typekey", "cite $type" );
	}

	public function getParameterName( $parameter = "" ) {
		if ( empty( $parameter ) ) return false;
		$parameterkey = str_replace( " ", "-", $parameter );
		return $this->getMessage( "wikitext-parameter-$parameterkey", $parameter );
	}

	public function getBlankParameter( $parameter ) {
		return "|" . $this->getParameterName( $parameter ) . "=";
	}

	public function getFragment( $metadata, $parameter ) {
		if ( $metadata->exists( $parameter ) ) {
			return $this->getBlankParameter( $parameter ) . $metadata->get( $parameter );
		} else {
			return "";
		}
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
			$type = $metadata->type;
		} else {
			$type = "web";
		}
		$core = "{{" . $this->getTemplateName( $type );

		// URL
		$core .= $this->getFragment( $metadata, "url" );

		// Archive URL
		$core .= $this->getFragment( $metadata, "archiveurl" );

		// Title
		$core .= $this->getFragment( $metadata, "title" );

		// Author
		if ( $fragment = $this->getFragment( $metadata, "author" ) ) {
			$core .= $fragment;
		} elseif ( $this->options->get( "addblankmetadata" ) ) { // add a blank field
			$core .= $this->getBlankParameter( "author" );
		}

		// Date
		if ( $timestamp = strtotime( $metadata->date ) ) { // date
			$core .= $this->getBlankParameter( "date" ) . Utils::generateDate( $timestamp, $this->dateFormat, $this->lang );
		} elseif ( $this->options->get( "addblankmetadata" ) ) { // add a blank field
			$core .= $this->getBlankParameter( "date" );
		}

		// Archive date
		if ( $archivets = strtotime( $metadata->archivedate ) ) { // archivedate
			$core .= $this->getBlankParameter( "archivedate" ) . Utils::generateDate( $archivets, $this->dateFormat, $this->lang );
		}

		// Publisher
		$core .= $this->getFragment( $metadata, "publisher" );

		// Work (and an empty |publisher=)
		if ( $fragment = $this->getFragment( $metadata, "work" ) ) {
			$core .= $fragment;
		} elseif( !$metadata->exists( "publisher" ) ) { // no |work= or |publisher= extracted, add an empty |publisher=
			$core .= $this->getBlankParameter( "publisher" );
		}
		// Access date
		if ( !$this->options->get( "noaccessdate" ) ) {
			$core .= $this->getBlankParameter( "accessdate" ) . Utils::generateDate( 0, $this->dateFormat, $this->lang );
		}
		// Via
		$core .= $this->getFragment( $metadata, "via" );

		$core .= $config['citeextra'] . "}}";
		return $core;
	}
}
