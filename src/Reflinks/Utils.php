<?php
/*
	Copyright (c) 2014-2016, Zhaofeng Li
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
	Utilities
*/

namespace Reflinks;

use Jenssegers\Date\Date;
use Reflinks\DateFormat;

class Utils {
	// @codeCoverageIgnoreStart
	// Utils should never be constructed
	function __construct() {
		return null;
	}
	// @codeCoverageIgnoreEnd

	public static function generateWikiTimestamp( $timestamp = 0 ) {
		if ( !$timestamp ) {
			$timestamp = time();
		}
		return date( "YmdHis", $timestamp );
	}

	public static function endsWith( $subject, $endsWith, $caseInsensitive = false ) {
		$strlen = strlen( $subject );
		$testlen = strlen( $endsWith );
		if ( $testlen > $strlen ) return false;
		return substr_compare( $subject, $endsWith, $strlen - $testlen, $testlen, $caseInsensitive ) === 0;
	}

	public static function generateDate( $timestamp = 0, DateFormat $format, $locale = 'en' ) {
		global $config; // FIXME: This should probably be made cleaner
		if ( !$timestamp ) {
			$timestamp = time();
		}
		// Find the correct format string
		if ( !empty( $config['dateFormatOverrides'][$locale] ) ) {
			$formatString = $config['dateFormatOverrides'][$locale];
		} else if ( DateFormat::MDY == $format->get() ) {
			$formatString = "F j, Y";
		} else {
			$formatString = "j F Y";
		}
		// Generate the date string
		if ( !class_exists( "Jenssegers\\Date\\Date" ) ) { // use date()
			if ( $locale !== "en" ) return false; // D'oh :(
			$result = date( $formatString, $timestamp );
		} else { // always use the library when available
			Date::setLocale( $locale );
			$date = new Date( $timestamp );
			$result = $date->format( $formatString );
		}
		if ( $locale == "fr" ) $result = strtolower( $result );
		return $result;
	}

	public static function generateShortDate( $timestamp = 0 ) {
		if ( !$timestamp ) {
			$timestamp = date();
		}
		return date( "F Y", $timestamp );
	}

	public static function detectDateFormat( $wikitext ) {
		if ( stripos( $wikitext, "{{Use mdy dates" ) !== false ) { // MDY tag
			return new DateFormat( DateFormat::MDY );
		} else { // Let's use DMY then
			return new DateFormat( DateFormat::DMY );
		}
	}

	public static function getFirstNodeAttrContent( \DOMNodeList $nodelist ) {
		return trim( $nodelist->item( 0 )->attributes->getNamedItem( "content" )->nodeValue );
	}

	public static function getFirstNodeValue( \DOMNodeList $nodelist ) {
		return trim( $nodelist->item( 0 )->nodeValue );
	}

	public static function getXpath( \DOMDocument $dom ) {
		$xpath = new \DOMXPath( $dom );
		$xpath->registerNamespace( "x", "http://www.w3.org/1999/xhtml" );
		return $xpath;
	}

	// @codeCoverageIgnoreStart
	public static function getBaseDomain( $url ) {
		$pslManager = new \Pdp\PublicSuffixListManager();
		$parser = new \Pdp\Parser( $pslManager->getList() );
		$result = $parser->parseUrl( $url );
		return $result->host->registerableDomain;
	}
	// @codeCoverageIgnoreEnd

	public static function removeBareUrlTags( $source ) {
		$pattern = "/\{\{(Bare|Bare links|Barelinks|Bare references|Bare refs|Bare URLs|Cleanup link rot|Cleanup link-rot|Cleanup-link-rot|Cleanup-linkrot|Link rot|Linkrot|Cleanup-bare URLs)([^\}])*\}\}/i";
		return preg_replace( $pattern, "", $source );
	}

	public static function isCitationEmpty( $content ) {
		if (
			empty( $content ) // literally empty
			|| $content == "{{cite web}}" // inserted via RefToolbar
		) {
			return true;
		}
		return false;
	}

	public static function getClass( $class, $defaultNs ) {
		if ( class_exists( $defaultNs . "\\" . $class ) ) {
			return $defaultNs . "\\" . $class;
		} else if ( class_exists( $class ) ) {
			return $class;
		} else {
			return false;
		}
	}

	public static function parseAuthors( $string ) {
		$separators = array(
			",", // comma
			"、", // Chinese comma (U+3001), see [[zh:顿号]]
			";", // semicolon
		);
		foreach ( $separators as $separator ) {
			if ( false !== strpos( $string, $separator ) ) {
				return explode( $separator, $string );
			}
		}
		return array( $string );
	}
}
