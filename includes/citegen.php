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
	Citation generation
*/

require_once __DIR__ . "/constants.php";

// Per https://en.wikipedia.org/wiki/Help:Citation_Style_1
function generatePlainLink( $url, $metadata, $dateformat = DATE_DMY, $options = array() ) {
	$core = "";
	if ( !empty( $metadata['date'] ) && $timestamp = strtotime( $metadata['date'] ) ) {
		$date = generateDate( $dateformat, $timestamp );
	}
	// Author (Date).
	if ( !empty( $metadata['author'] ) ) {
		$core .= $metadata['author'];
		if ( !empty( $date ) ) {
			$core .= " ($date)";
		}
		$core .= ". ";
	}
	// "Title".
	$core .= "[$url \"{$metadata['title']}\"]. ";
	
	// ''Work'' (Publisher).
	if ( !empty( $metadata['work'] ) ) {
		$core .= "''{$metadata['work']}''";
		if ( !empty( $metadata['publisher'] ) ) {
			$core .= " ({$metadata['publisher']})";
		}
		$core .= ". ";
	} elseif ( !empty( $metadata['publisher'] ) ) { // Publisher
		$core .= $metadata['publisher'] . ". ";
	}
	
	// Date. <-- When without author
	if ( empty( $metadata['author'] ) && !empty( $date ) ) {
		$core .= "{$date}. ";
	}
	
	if ( !$options['noaccessdate'] ) {
		// Retrived on
		$core .= "Retrieved on " . generateDate( $dateformat ) . ".";
	}
	return $core;
}

function generateCiteTemplate( $url, $metadata, $dateformat = DATE_DMY, $options = array() ) {
	global $config;
	$date = date( "j F Y" );
	foreach ( $metadata as &$field ) { // we don't want | here
		$field = str_replace( "|", "-", $field );
	}
	$core = "{{cite web|url=$url";
	if ( !empty( $metadata['title'] ) ) {
		$core .= "|title=" . $metadata['title'];
	}
	if ( !empty( $metadata['author'] ) ) {
		$core .= "|author=" . $metadata['author'];
	} elseif ( $options['addblankmetadata'] ) { // add a blank field
		$core .= "|author=";
	}
	if ( !empty( $metadata['date'] ) && $timestamp = strtotime( $metadata['date'] ) ) { // successfully parsed
		$core .= "|date=" . generateDate( $dateformat, $timestamp );
	} elseif ( $options['addblankmetadata'] ) { // add a blank field
		$core .= "|date=";
	}
	if ( !empty( $metadata['work'] ) ) {
		$core .= "|work=" . $metadata['work'];
	} else { // no |work= extracted , add an empty |publisher=
		$core .= "|publisher=";
	}
	// Let's not use guesswork now, as it's unstable
	if ( !$options['noaccessdate'] ) {
		$core .= "|accessdate=" . generateDate( $dateformat );
	}
	$core .= $config['citeextra'] . "}}";
	return $core;
}
