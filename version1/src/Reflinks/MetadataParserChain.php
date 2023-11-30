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
	Metadata parser chain
*/

namespace Reflinks;

use Reflinks\Exceptions\MetadataParserException;
use Reflinks\Exceptions\NoSuchMetadataParserException;
use Reflinks\Exceptions\ErroneousMetadataParserException;

class MetadataParserChain {
	private $chain = array();
	function __construct( array $chain = array() ) {
		foreach ( $chain as $parser ) {
			$this->append( $parser );
		}
	}
	public function append( $parser ) {
		if ( is_subclass_of( $parser, "MetadataParser" ) ) {
			$this->chain[] = $parser;
			return true;
		} elseif ( is_string( $parser ) ) {
			// The autoloader doesn't automatically resolve the namespace here, so...
			if ( strpos( $parser, '\\' ) ) { // absolute namespace
				$class = $parser;
			} else { // Let's assume it's under Reflinks\
				$class = '\\Reflinks\\MetadataParsers\\' . $parser;
			}
			if ( class_exists( $class ) ) {
				$this->chain[] = new $class();
				return true;
			} else {
				throw new NoSuchMetadataParserException( $class );
			}
		} else {
			throw new ErroneousMetadataParserException();
		}
	}
	public function parse( \DOMDocument $dom, Metadata $baseMetadata = null ) {
		if ( $baseMetadata ) {
			$result = $baseMetadata;
		} else {
			$result = new Metadata();
		}
		foreach ( $this->chain as $parser ) {
			$parser->chain( $dom, $result );
		}
		return $result;
	}
}

