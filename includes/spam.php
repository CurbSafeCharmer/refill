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
	Spam blacklist
*/

require_once __DIR__ . "/constants.php";

function initSpamBlacklist() {
	global $config;
	if ( $config['spam']['enable'] ) {
		return loadSpamBlacklist();
	} else {
		return false;
	}
}

// Use initSpamBlacklist() instead
function loadSpamBlacklist() {
	global $config;
	$file = fopen( $config['spam']['file'], "r" );
	if ( $file ) {
		while ( false !== $line = fgets( $file ) ) { 
			addSpamRegex( $line );
		}
		fclose( $file );
		return countSpamBlacklist();
	} else {
		return false;
	}
}

function addSpamRegex( $line ) {
	global $config;
	// Remove comments from the line, and trim the whitespaces
	$line = trim( preg_replace( "/#.*$/", "", $line ) );
	if ( !empty( $line ) ) { // Okay, we've got a regex
		$config['spam']['blacklist'][] = $line;
	}
}

function checkSpam( $url ) {
	global $config;
	foreach( $config['spam']['blacklist'] as $oregex ) {
		// Those entries on the list are fragments, let's complete them
		$regex = "|^https?\:\/\/[A-Za-z0-9\-\_\.]*" . $oregex . "|";
		if ( @preg_match( $regex, $url ) ) { // Gotcha!
			return true;
		}
	}
	return false;
}

function countSpamBlacklist() {
	global $config;
	return count( $config['spam']['blacklist'] );
}
