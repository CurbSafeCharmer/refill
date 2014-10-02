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
	Date & time
*/

require_once __DIR__ . "/constants.php";

function generateWikiTimestamp( $timestamp = 0 ) {
	if ( !$timestamp ) {
		$timestamp = time();
	}
	return date( "YmdHis", $timestamp );
}

function generateDate( $format, $timestamp = 0 ) {
	if ( !$timestamp ) {
		$timestamp = time();
	}
	if ( $format == DATE_MDY ) { // mdy
		return date( "F j, Y", $timestamp );
	} else { // dmy (default)
		return date( "j F Y", $timestamp );
	}
}

function generateShortDate( $timestamp = 0 ) {
	if ( !$timestamp ) {
		$timestamp = date();
	}
	return date( "F Y", $timestamp );
}

// DATE_DMY if dmy (default), DATE_MDY if mdy
function detectDateFormat( $source ) {
	if ( stripos( $source, "{{Use mdy dates" ) !== false ) {
		return DATE_MDY;
	} else {
		return DATE_DMY;
	}
}

