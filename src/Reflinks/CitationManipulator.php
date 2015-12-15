<?php

/*
	Citation manipulator
*/

namespace Reflinks;

class CitationManipulator {
	protected $wikitext;
	protected $citations;
	protected $replacedList = array();

	public static $markerStart = "?REFILL";
	public static $markerEnd = "REF?";
	public static $markerProtector = "x";
	public static $markerRegex = "/\?REFILL(?'citationId'[0-9]+)REF\?/";
	public static $markerProtectedRegex = "/\?REFILLx(?'citationId'[0-9]+)REF\?/";

	/**
	 * Initialize the object
	 * @param string $wikitext The wikitext
	 */
	public function __construct( $wikitext ) {
		$this->citations = array();
		$this->wikitext = $wikitext;
		$this->parse();
	}

	/**
	 * Get a citation
	 *
	 * @param int $id The citation ID
	 *
	 * @return Citation|false
	 */
	public function getCitation( $id ) {
		if ( !isset( $this->citations[$id] ) ) {
			return false;
		} else {
			return $this->citations[$id];
		}
	}

	/**
	 * Check whether there are more than one citation with the specified content
	 *
	 * @param string $content The content to search for
	 *
	 * @return bool True if there are indeed duplicates
	 */
	public function hasDuplicates( $content ) {
		return count( $this->searchByContent( $content ) ) > 1;
	}

	/**
	 * Search for citations with the specified content
	 *
	 * @param string $content The content to search for
	 *
	 * @return Citation[] An associative array containing the matching citations
	 */
	public function searchByContent( $content ) {
		$result = array();
		foreach ( $this->citations as $id => $citation ) {
			if ( $citation->isStub || $citation->useGenerator ) {
				// Skip if there is no content or it's dynamically generated
				continue;
			}
			if ( $citation->content == $content ) {
				$result[$id] = $citation;
			}
		}
		return $result;
	}

	/**
	 * Search for citations with a specified attribute-value pair
	 *
	 * @param string $attribute The name of the attribute
	 * @param string $value The value of the attribute
	 *
	 * @return Citation[] An associative array containing the matching citations
	 */
	public function searchByAttribute( $attribute, $value ) {
		$result = array();
		foreach ( $this->citations as $id => $citation ) {
			if ( isset( $citation->attributes[$attribute] ) && $citation->attributes[$attribute] == $value ) {
				$result[$id] = $citation;
			}
		}
		return $result;
	}

	/**
	 * Replace a citation
	 *
	 * @param int $id The ID of the citation being replaced
	 * @param Citation $replacement The replacement citation
	 *
	 * @return bool True if successful
	 */
	public function replace( $id, Citation $replacement ) {
		if ( !isset( $this->citations[$id] ) ) {
			return false;
		} else {
			$replacement->id = $id;
			$this->citations[$id] = $replacement;
			if ( !in_array( $id, $this->replacedList ) ) {
				$this->replacedList[] = $id;
			}
			return true;
		}
	}

	/**
	 * Change all citations with identical content
	 *
	 * Replace the first citation with content identical to $content with $first,
	 * and the remaining with $remaining
	 *
	 * @param string $search The content to search for
	 * @param Citation $first The replacement citation for the first match
	 * @param Citation $remaining The replacement citation for the remaining match(es)
	 */
	public function replaceByContent( $search, Citation $first, Citation $remaining = null ) {
		$citations = $this->searchByContent( $search );
		$i = 0;
		// Some code smell here...
		foreach ( $citations as $id => $citation ) {
			if ( !empty( $remaining ) && $i++ != 0 ) {
				$this->replace( $id, $remaining );
			} else {
				$this->replace( $id, $first );
			}
		}
	}

	/**
	 * Get the resulting wikitext
	 * @return string The resulting wikitext
	 */
	public function exportWikitext() {
		$result = "";
		$callback = function( $matches ) {
			$id = $matches['citationId'];
			if ( !isset( $this->citations[$id] ) ) {
				// TODO: Properly report this error to the caller
				return "<ref>reFill error: Missing citation #$id</ref>";
			} else {
				$citation = $this->citations[$id];
				if ( $citation->isDeleted ) {
					return ""; // citation deleted
				} else {
					return $citation->getCode();
				}
			}
		};
		$result = preg_replace_callback( self::$markerRegex, $callback, $this->wikitext );
		$result = $this->unprotectMarkers( $result );
		return $result;
	}

	/**
	 * Loop through the citations
	 *
	 * @param callable $callback The callback function
	 * 	A Citation is passed to the first and only parameter of
	 * 	the callback function.
	 * @param bool $skipReplaceed Skip references that have been replaced
	 *	earlier in the loop
	 *
	 * @return bool True if all references have been traversed or replaced
	 */
	public function loopCitations( $callback, $skipReplaced = true ) {
		foreach ( $this->citations as $id => $citation ) {
			if ( !$skipReplaced || !in_array( $id, $this->replacedList ) ) {
				if ( call_user_func( $callback, $citation ) === true ) { // stop the loop
					return false; // we stopped prematurely
				}
			}
		}
		return true; // all references traversed!
	}

	/**
	 * Protect the markers already in the wikitext before processing
	 *
	 * @param string $wikitext The wikitext
	 *
	 * @return string The resulting wikitext
	 */
	protected function protectMarkers( $wikitext ) {
		$callback = function( $matches ) {
			return self::$markerStart . self::$markerProtector . $matches[1] . self::$markerEnd;
		};
		return preg_replace_callback( self::$markerRegex, $callback, $wikitext );
	}

	/**
	 * Unprotect the markers already in the wikitext before processing
	 *
	 * @param string $wikitext The wikitext
	 *
	 * @return The resulting wikitext
	 */
	protected function unprotectMarkers( $wikitext ) {
		$callback = function( $matches ) {
			return self::$markerStart . $matches[1] . self::$markerEnd;
		};
		return preg_replace_callback( self::$markerProtectedRegex, $callback, $wikitext );
	}

	/**
	 * Parse the given wikitext
	 */
	protected function parse() {
		$this->citations = array();
		$this->wikitext = $this->protectMarkers( $this->wikitext );
		$id = 0; // Citation ID counter
		$pattern = "/"
			 // scenario #1: A full <ref></ref> pair
			 . "(?'pair'"
		         . "(\<ref([^\>\/]*)\>)" // the starting <ref> tag, possibly with attributes
		         . "(.*?)" // content of the citation (inside the surround tags)
			 . "(\<\/ref\>)" // the ending </ref> tag
			 . ")"
			 // end scenario #1
			 . "|" // or...
			 // scenario #2: A self-closing <ref/>
			 . "(?'stub'"
			 . "\<ref"
			 . "([^\>\/]*)"
			 . "\/>"
			 . ")"
			 // end scenario #2
			 . "/i";
		$callback = function( $matches ) use ( &$id ) {
			$code = empty( $matches['pair'] ) ? $matches['stub'] : $matches['pair'];
			$this->citations[$id] = new Citation( $code );
			$this->citations[$id]->id = $id;
			$marked = self::$markerStart . $id . self::$markerEnd;
			$id++;
			return $marked;
		};
		$this->wikitext = preg_replace_callback( $pattern, $callback, $this->wikitext );
	}
}
