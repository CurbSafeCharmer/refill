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
	Core
*/

require_once __DIR__ . "/config.default.php";
require_once __DIR__ . "/constants.php";
require_once __DIR__ . "/citegen.php";
require_once __DIR__ . "/source.php";
require_once __DIR__ . "/metadata.php";
require_once __DIR__ . "/date.php";
require_once __DIR__ . "/spam.php";
require_once __DIR__ . "/options.php";

function fixRef( $source, &$log = "", $options = array() ) {
	global $config;
	initSpamBlacklist();
	$pattern = "/\<ref[^\>]*\>([^\<\>]+)\<\/ref\>/i";
	$matches = array();
	$log = array(
		'fixed' => array(), // ['url'] contains the original link
		'skipped' => array(), // ['ref'] contains the original ref, ['reason'] contains the reason const, ['status'] contains the status code
	);
	$dateformat = detectDateFormat( $source );
	if ( $options['plainlink'] ) {
		$options['nofixcplain'] = true;
	}
	preg_match_all( $pattern, $source, $matches );
	foreach ( $matches[1] as $key => $core ) {
		$status = 0;
		$oldref = array();
		// Let's check if we are supposed to mess with it first...
		if ( preg_match( "/\{\{(Dead link|404|dl|dead|Broken link)/i", $core ) ) { // dead link tag
			continue;
		}
 
		// Let's find out what kind of reference it is...
		$tcore = trim( $core );
		if ( filter_var( $tcore, FILTER_VALIDATE_URL ) && strpos( $tcore, "http" ) === 0 ) {
			// a bare link (consists of only a URL)
			$oldref['url'] = $tcore;
		} elseif ( preg_match( "/^\[(http[^\] ]+) ([^\]]+)\]$/i", $tcore, $cmatches ) ) {
			// a captioned plain link (consists of a URL and a caption, surrounded with [], with /no/ other stuff after it)
			if ( filter_var( $cmatches[1], FILTER_VALIDATE_URL ) && !$options['nofixcplain'] ) {
				$oldref['url'] = $cmatches[1];
				$oldref['caption'] = $cmatches[2];
			} else {
				continue;
			}
		} elseif ( preg_match( "/^\[(http[^ ]+)\]$/i", $tcore, $cmatches ) ) {
			// an uncaptioned plain link (consists of only a URL, surrounded with [])
			if ( filter_var( $cmatches[1], FILTER_VALIDATE_URL ) && !$options['nofixuplain'] ) {
				$oldref['url'] = $cmatches[1];
			} else {
				continue;
			}
		} elseif ( preg_match( "/^\{\{cite web\s*\|\s*url=(http[^ \|]+)\s*\}\}$/i", $tcore, $cmatches ) ) {
			// an uncaptioned {{cite web}} template (Please improve the regex)
			if ( filter_var( $cmatches[1], FILTER_VALIDATE_URL ) && !$options['nofixutemplate'] ) {
				$oldref['url'] = $cmatches[1];
			} else {
				continue;
			}
		} else {
			// probably already filled in, let's skip it
			continue;
		}
		
		// Check if it's blacklisted
		foreach( $config['hostblacklist'] as $blentry ) {
			if ( preg_match( "/^" . $blentry . "$/", parse_url( $oldref['url'], PHP_URL_HOST ) ) ) { // blacklisted
				$log['skipped'][] = array(
					'ref' => $core,
					'reason' => SKIPPED_HOSTBL,
					'status' => $status,
				);
				continue 2;
			}
		}
		if ( checkSpam( $oldref['url'] ) ) {
			$log['skipped'][] = array(
				'ref' => $core,
				'reason' => SKIPPED_SPAM,
				'status' => $status,
			);
			continue;
		}
		
		// Fetch the webpage and extract the metadata
		$html = fetchWeb( $oldref['url'], null, $status );
		if ( $status != 200 ) { // failed
			$log['skipped'][] = array(
				'ref' => $core,
				'reason' => SKIPPED_HTTPERROR,
				'status' => $status,
			);
			continue;
		} elseif ( !$html ) { // empty response
			$log['skipped'][] = array(
				'ref' => $core,
				'reason' => SKIPPED_EMPTY,
				'status' => $status,
			);
			continue;
		}
		$metadata = extractMetadata( $html );
		if ( isset( $oldref['caption'] ) && !$options['nouseoldcaption'] ) {
			// Use the original caption
			$metadata['title'] = $oldref['caption'];
		}
		
		if ( empty( $metadata['title'] ) ) {
			$log['skipped'][] = array(
				'ref' => $core,
				'reason' => SKIPPED_NOTITLE,
				'status' => $status,
			);
			continue;
		}
		
		// Generate cite template
		if ( $options['plainlink'] ) { // use captioned plain link
			$newcore = generatePlainLink( $oldref['url'], $metadata, $dateformat, $options );
		} else { // use {{cite web}}
			$newcore = generateCiteTemplate( $oldref['url'], $metadata, $dateformat, $options );
		}
		
		// Replace the old core
		$replacement = str_replace( $core, $newcore, $matches[0][$key] ); // for good measure
		$source = str_replace( $matches[0][$key], $replacement, $source );
		$log['fixed'][] = array(
			'url' => $oldref['url'],
		);
	}
	return $source;
}

function getSkippedReason( $code ) {
	switch ( $code ) {
		case SKIPPED_HTTPERROR:
			return "HTTP Error";
		case SKIPPED_EMPTY:
			return "Empty response or not HTML";
		case SKIPPED_NOTITLE:
			return "No title is found";
		case SKIPPED_HOSTBL:
			return "Host blacklisted";
		case SKIPPED_SPAM:
			return "Spam blacklist";
		default:
			return "Unknown error";
	}
}

// Remove all bare URL tags. Use only if all bare links are fixed.
function removeBareUrlTags( $source ) {
	$pattern = "/\{\{(Bare|Bare links|Barelinks|Bare references|Bare refs|Bare URLs|Cleanup link rot|Cleanup link-rot|Cleanup-link-rot|Cleanup-linkrot|Link rot|Linkrot|Cleanup-bare URLs)([^\}])*\}\}/i";
	return preg_replace( $pattern, "", $source );
}


