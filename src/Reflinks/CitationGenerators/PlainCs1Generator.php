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
	Plain CS1 citation generator
*/

namespace Reflinks\CitationGenerators;

use Reflinks\CitationGenerator;
use Reflinks\UserOptions;
use Reflinks\Metadata;
use Reflinks\DateFormat;
use Reflinks\Utils;

class PlainCs1Generator extends CitationGenerator {
	public $options;
	function __construct( UserOptions $options ) {
		$this->options = $options;
	}
	public function getCitation( Metadata $metadata, DateFormat $format ) {
		$core = "";
		// Is the page archived or not?
		if ( $metadata->exists( "archiveurl" ) ) {
			$url = $metadata->archiveurl;
			$isArchived = true;
		} else {
			$url = $metadata->url;
			$isArchived = false;
		}
		
		// Generate dates
		if ( $timestamp = strtotime( $metadata->date ) ) {
			$date = Utils::generateDate( $timestamp, $format );
		}
		if ( $archivets = strtotime( $metadata->archivedate ) ) {
			$archivedate = Utils::generateDate( $archivets, $format );
		}
		
		// Author (Date).
		if ( $metadata->exists( "author" ) ) {
			$core .= $metadata->author;
			if ( !empty( $date ) ) {
				$core .= " ($date)";
			}
			$core .= ". ";
		}
		
		// "Title".
		$core .= "[" . $url . ' "' . $metadata->title . '"]. ';
		
		// ''Work'' (Publisher).
		if ( $metadata->exists( "work" ) ) {
			$core .= "''" . $metadata->work . "''";
			if ( $metadata->exists( "publisher" ) ) {
				$core .= " (" . $metadata->publisher . ")";
			}
			$core .= ". ";
		} elseif ( $metadata->exists( "publisher" ) ) { // Publisher
			$core .= $metadata->publisher . ". ";
		}
		
		// Date. <-- When without author
		if ( !$metadata->exists( "author" ) && !empty( $date ) ) {
			$core .= $date . ". ";
		}
		
		// Archived from
		if ( $isArchived ) {
			$core .= "Archived from [{$metadata->url} the original] on $archivedate. ";
		}
		
		// Retrived on
		if ( !$this->options->get( "noaccessdate" ) ) {
			// Retrived on
			$core .= "Retrieved on " . Utils::generateDate( 0, $dateformat ) . ".";
		}
		return $core;
	}
}
