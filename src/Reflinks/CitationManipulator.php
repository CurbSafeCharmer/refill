<?php

/*
	Citation manipulator
*/

namespace Reflinks;

class CitationManipulator {
	private $wikitext;
	private $citations;
	private $supportedAttributes = array( "name", "group" );
	public static $markerStart = "?REFILL";
	public static $markerEnd = "REF?";
	public static $markerProtector = "x";
	public static $markerRegex = "/\?REFILL(?'citationId'[0-9]+)REF\?/";
	public static $markerProtectedRegex = "/\?REFILLx(?'citationId'[0-9]+)REF\?/";
	function __construct( $wikitext ) {
		$this->citations = array();
		$this->wikitext = $wikitext;
		$this->parse();
	}
	private function protectMarkers( $wikitext ) {
		$callback = function( $matches ) {
			return self::$markerStart . self::$markerProtector . $matches[1] . self::$markerEnd;
		};
		return preg_replace_callback( self::$markerRegex, $callback, $wikitext );
	}
	private function unprotectMarkers( $wikitext ) {
		$callback = function( $matches ) {
			return self::$markerStart . $matches[1] . self::$markerEnd;
		};
		return preg_replace_callback( self::$markerProtectedRegex, $callback, $wikitext );
	}
	private function parse() {
		$this->citations = array();
		$this->wikitext = $this->protectMarkers( $this->wikitext );
		$id = 0; // Citation ID counter
		$pattern = "/"
			 // scenario #1: A full <ref></ref> pair
			 . "("
		         . "(?'startTag'\<ref(?'startAttrs'[^\>\/]*)\>)" // the starting <ref> tag, possibly with attributes
		         . "(?'content'.*?)" // content of the citation (inside the surround tags)
			 . "(?'endTag'\<\/ref\>)" // the ending </ref> tag
			 . ")"
			 // end scenario #1
			 . "|" // or...
			 // scenario #2: A self-closing <ref/>
			 . "("
			 . "\<ref"
			 . "(?'stubAttrs'[^\>\/]*)"
			 . "\/>"
			 . ")"
			 // end scenario #2
			 . "/i";
		$callback = function( $matches ) use ( &$id ) {
			if ( empty( $matches['stubAttrs'] ) ) { // a full <ref></ref> pair
				$citation = array(
					'complete' => $matches[0],
					'startTag' => $matches['startTag'],
					'startAttrs' => $matches['startAttrs'],
					'content' => $matches['content'],
					'endTag' => $matches['endTag'],
					'stub' => false
				);
			} else { // a self-closing <ref/>
				$citation = array(
					'complete' => $matches[0],
					'startAttrs' => $matches['stubAttrs'],
					'stub' => true
				);
			}
			foreach ( $this->supportedAttributes as $attribute ) {
				$value = $this->parseAttribute( $citation['startAttrs'], $attribute );
				if ( !empty( $value ) ) {
					$citation['attributes'][$attribute] = $value;
				}
			}
			$this->citations[$id] = $citation;
			$marked = self::$markerStart . $id . self::$markerEnd;
			$id++;
			return $marked;
		};
		$this->wikitext = preg_replace_callback( $pattern, $callback, $this->wikitext );
	}
	public function parseAttribute( $startAttrs, $attribute ) {
		$template = "/"
		         . "\\s*" // allow whitespace
		         . "%s\\=" // %s is the attribute name ("name" or "group" or whatever), to be filled with sprintf()
			 . "(" // there are two possiblities here...
			 // scenario #1: the value is quoted with "
			 . "("
			 . "\\\""
			 . "(?'doubleQuotedValue'[^\\\"]*)"
			 . "\\\""
			 . ")"
			 // end scenario #1
			 . "|" // or...
			 // scenario #2: the value is quoted with '
			 . "("
			 . "\\'"
			 . "(?'singleQuotedValue'[^\\']*)"
			 . "\\'"
			 . ")"
			 // end scenario #2
			 . "|" // or...
			 // scenario #3: the value is not quoted, so no spaces allowed in the value
			 . "(?'unquotedValue'\S+)"
			 . ")"
			 // end scenario #3
			 . "\\s*" // allow whitespace
			 . "/i";
		$pattern = sprintf( $template, $attribute );
		if ( preg_match( $pattern,  $startAttrs, $matches ) ) {
			foreach ( array( "doubleQuotedValue", "singleQuotedValue", "unquotedValue" ) as $type )
				if ( !empty( $matches[$type] ) )
					return $matches[$type];
		}
	}
	public function hasExactAttribute( $name, $value ) {
		return preg_match( "/$name\\=." . preg_quote( $value ) . "/", $this->wikitext );
	}
	public function generateAttribute( $attribute, $value ) {
		return $attribute . '="' . htmlentities( $value ) . '"';
	}
	public function generateAttributes( $attributes ) {
		$startAttr = "";
		foreach ( $attributes as $name => $attribute ) {
			$startAttr .= $this->generateAttribute( $name, $attribute ) . " ";
		}
		return $startAttr;

	}
	public function generateCitation( $content, $startAttrs = "" ) {
		if ( is_array( $startAttrs ) ) {
			$startAttrs = $this->generateAttributes( $startAttrs );
		}
		$startAttrs = trim( $startAttrs );
		if ( !empty( $startAttrs ) ) {
			return "<ref $startAttrs>$content</ref>";
		} else {
			return "<ref>$content</ref>";
		}
	}
	public function generateStub( $startAttrs ) {
		if ( is_array( $startAttrs ) ) {
			$startAttrs = $this->generateAttributes( $startAttrs );
		}
		$startAttrs = trim( $startAttrs );
		return "<ref $startAttrs/>";
	}
	public function hasDuplicates( $content ) {
		return count( $this->searchByContent( $content ) ) > 1;
	}
	public function searchByContent( $content ) {
		$result = array();
		foreach ( $this->citations as $id => $citation ) {
			if ( $citation['content'] == $content ) {
				$result[$id] = $citation;
			}
		}
		return $result;
	}
	public function searchByAttribute( $attribute, $value ) {
		$result = array();
		foreach ( $this->citations as $id => $citation ) {
			if ( isset( $citation['attributes'][$attribute] ) && $citation['attributes'][$attribute] == $value ) {
				$result[$id] = $citation;
			}
		}
		return $result;
	}
	public function replace( $id, $complete ) {
		if ( !isset( $this->citations[$id] ) ) {
			return false;
		} else {
			$this->citations[$id] = array( "complete" => $complete );
			return true;
		}
	}
	/*
		Change all citations with identical content
	*/
	public function replaceByContent( $content, $first, $remaining = null ) {
		$citations = $this->searchByContent( $content );
		$i = 0;
		// Some code smell here...
		foreach ( $citations as $id => $citation ) {
			if ( !empty( $remaining ) && $i++ != 0 ) {
				$new = array( "complete" => $remaining );
			} else {
				$new = array( "complete" => $first );
			}
			$this->citations[$id] = $new;
		}
	}
	public function dumpCitations() {
		return $this->citations;
	}
	public function exportWikitext() {
		$result = "";
		$callback = function( $matches ) {
			$id = $matches['citationId'];
			if ( !isset( $this->citations[$id] ) ) {
				// TODO: Properly report this error to the caller
				return "<ref>reFill error: Missing citation #$id</ref>";
			} else {
				$citation = $this->citations[$id];
				if ( empty( $citation['complete'] ) ) {
					return ""; // citation deleted
				} else {
					return $citation['complete']; // When will Tool Labs have PHP 7.0?
				}
			}
		};
		$result = preg_replace_callback( self::$markerRegex, $callback, $this->wikitext );
		$result = $this->unprotectMarkers( $result );
		return $result;
	}
	/*
		Loop through the citations. A citation is
		passed to the first and only parameter of
		the callback function. You can modify other
		citations in the callback, as it's smart
		enough.
	*/
	public function loopCitations( $callback ) {
		foreach ( $this->citations as $id => $citation ) {
			if ( !in_array( $citation['content'], $processed ) ) {
				if ( call_user_func( $callback, $citation, $id ) === true ) { // stop the loop
					return false; // we stopped prematurely
				}
			}
		}
		return true; // all references traversed!
	}
}
