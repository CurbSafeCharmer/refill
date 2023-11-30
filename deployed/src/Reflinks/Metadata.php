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
	Metadata model
*/

namespace Reflinks;

use Reflinks\Exceptions\MetadataException;
use Reflinks\Exceptions\NoSuchMetadataFieldException;

class Metadata implements \Iterator {
	public $rawMetadata = array(
		"authors" => array(),
		"editors" => array()
	);
	// Descriptions of the fields are licensed under CC-BY-SA 3.0.
	// Copyright (c) 2015 Wikipedia contributors.
	public static $fields = array(
		// Type of the referenced material. Valid values are: "web", "av media", "journal"
		"type",
		// The URL of the online location where the text of the publication can be found
		"url",
		// Title of the referenced material.
		"title",
		// Date of the source
		"date",
		// Date when the original material was accessed
		"accessdate",
		// (May be an array or string) A list of authors. If it's an array, it can contain arrays or strings.
		// If an element is an array, it must contain exactly 2 strings, the first of which being the author's first name
		// If an element is a string, it must contain the full name of the author
		// Note: Consider using the helper functions to add authors and editors.
		// When read, this property is always an array
		"authors",
		// (Maybe be an array or string) A list of editors. If it's an array, it can contain arrays or strings.
		// If an element is an array, it must contain exactly 2 strings, the first of which being the editor's first name
		// If an element is a string, it must contain the full name of the editor
		// Note: Consider using the helper functions to add authors and editors.
		// When read, this property is always an array
		"editors",
		// Name of the publisher
		"publisher",
		// The meaning of this field is dubious. Looks like we should avoid using it?
		"work",
		// The name of the website hosting the referenced material
		"website",
		// The URL of the archived copy of the referenced material
		"archiveurl",
		// Date when the archive was made
		"archivedate",
		// If the original URL is dead
		"deadurl",
		// Name of the content deliverer
		"via",
		// Journal
		"journal",
		// Volume
		"volume",
		// Issue
		"issue",
		// Pages
		"pages",
		// PMID
		"pmid",
		// PMCID
		"pmc",
		// DOI
		"doi"
	);

	function __construct( array $rawMetadata = array() ) {
		$this->load( $rawMetadata );
	}

	// Iterator interface
	public function rewind() {
		reset( $this->rawMetadata );
	}

	public function current() {
		return current( $this->rawMetadata );
	}

	public function key() {
		return key( $this->rawMetadata );
	}

	public function next() {
		return next( $this->rawMetadata );
	}

	public function valid() {
		$key = key( $this->rawMetadata );
		return $this->validField( $key );
	}

	public static function validField( $name ) {
		return in_array( $name, self::$fields );
	}

	public function exists( $name ) {
		if ( !self::validField( $name ) ) {
			throw new NoSuchMetadataFieldException( $name );
		} else {
			return !empty( $this->rawMetadata[$name] );
		}
	}

	public function __isset( $name ) {
		return $this->exists( $name );
	}

	public function __set( $name, $value ) {
		if ( !self::validField( $name ) ) {
			throw new NoSuchMetadataFieldException( $name );
		} else if ( "authors" == $name || "editors" == $name ) {
			if ( is_string( $value ) ) { // a free-form list of people
				$this->rawMetadata[$name] = Utils::parseAuthors( $value );
			} else if ( is_array( $value ) ) {
				$this->rawMetadata[$name] = $value;
			} else {
				// wrong type
				trigger_error( "Change failed: `authors` and `editors` must be an array or string", E_USER_WARNING );
			}
		} else {
			$this->rawMetadata[$name] = $value;
		}
	}

	public function set( $name, $value ) {
		return $this->__set( $name, $value );
	}

	public function &__get( $name ) {
		$null = null;
		if ( !self::validField( $name ) ) {
			throw new NoSuchMetadataFieldException( $name );
		} else if ( !isset( $this->rawMetadata[$name] ) ) {
			return $null;
		} else {
			return $this->rawMetadata[$name];
		}
	}

	public function get( $name ) {
		return $this->__get( $name );
	}

	public function __unset( $name ) {
		if ( !self::validField( $name ) ) {
			throw new NoSuchMetadataFieldException( $name );
		} elseif ( isset( $this->rawMetadata[$name] ) ) {
			unset( $this->rawMetadata[$name] );
		}
	}

	public function dump() {
		return $this->rawMetadata;
	}

	public function load( array $rawMetadata = array() ) {
		foreach( $rawMetadata as $name => $value ) {
			$this->__set( $name, $value );
		}
		return $this;
	}

	public function merge( self $metadata ) {
		$this->load( $metadata->dump() );
		return $this;
	}

	public function addAuthors( $authors ) {
		$this->addPeople( "authors", $authors );
	}

	public function addEditors( $editors ) {
		$this->addPeople( "editors", $editors );
	}

	protected function addPeople( $type = "authors", $people ) {
		if ( is_string( $people ) ) {
			$people = Utils::parseAuthors( $people );
		}
		foreach ( $people as $person ) {
			if ( !in_array( $person, $this->{$type} ) ) {
				$this->{$type}[] = $person;
			}
		}
	}
}
