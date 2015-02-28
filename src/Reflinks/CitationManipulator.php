<?php

/*
	Citation manipulator
*/

namespace Reflinks;

class CitationManipulator {
	private $wikitext;
	private $citations;
	private $supportedAttributes = array( "name", "group" );
	function __construct( $wikitext ) {
		$this->citations = array();
		$this->wikitext = $wikitext;
		$this->parse();
	}
	private function parse() {
		$pattern = "/"
		         . "(?'startTag'\<ref(?'startAttrs'[^\>]*)\>)" // the starting <ref> tag, possibly with attributes
		         . "(?'content'.*?)" // content of the citation (inside the surround tags)
			 . "(?'endTag'\<\/ref\>)" // the ending </ref> tag
			 . "/i";
		$matches = array();
		preg_match_all( $pattern, $this->wikitext, $matches );
		foreach ( $matches[0] as $key => $citation ) {
			$citation = array(
				'complete' => $matches[0][$key],
				'startTag' => $matches['startTag'][$key],
				'startAttrs' => $matches['startAttrs'][$key],
				'content' => $matches['content'][$key],
				'endTag' => $matches['endTag'][$key]
			);
			foreach ( $this->supportedAttributes as $attribute ) {
				$value = $this->parseAttribute( $citation['startAttrs'], $attribute );
				if ( !empty( $value ) ) {
					$citation['attributes'][$attribute] = $value;
				}
			}
			$this->citations[] = $citation;
		}
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
	public function generateCitation( $content, $startAttrs = "" ) {
		$startAttrs = trim( $startAttrs );
		if ( !empty( $startAttrs ) ) {
			return "<ref $startAttrs>$content</ref>";
		} else {
			return "<ref>$content</ref>";
		}
	}
	public function generateStub( $startAttrs ) {
		$startAttrs = trim( $startAttrs );
		return "<ref $startAttrs/>";
	}
	public function hasDuplicates( $content ) {
		return count( $this->searchByContent( $content ) ) > 1;
	}
	public function searchByContent( $content ) {
		$result = array();
		foreach ( $this->citations as $citation ) {
			if ( $citation['content'] == $content ) {
				$result[] = $citation;
			}
		}
		return $result;
	}
	/*
		Change all citations with identical content
	*/
	public function replaceByContent( $content, $first, $remaining = null ) {
		$citations = $this->searchByContent( $content );
		$i = 0;
		// Some code smell here...
		$escaped = array();
		foreach ( $citations as $citation )
			$escaped[] = preg_quote( $citation['complete'], "/"  );
		$pattern = "/" . implode( "|", $escaped ) . "/";
		$this->wikitext = preg_replace_callback( $pattern, function( $match ) use ( &$i, $first, $remaining ) {
			if ( !empty( $remaining ) && $i++ != 0 ) {
				return $remaining;
			} else {
				return $first;
			}
		}, $this->wikitext, -1, $count );
	}
	public function dumpCitations() {
		return $this->citations;
	}
	public function exportWikitext() {
		return $this->wikitext;
	}
	/*
		Loop through the citations. A citation is
		passed to the first and only parameter of
		the callback function. You can modify other
		citations in the callback, as it's smart
		enough.
	*/
	public function loopCitations( $callback ) {
		$processed = array();
		foreach ( $this->citations as $citation ) {
			if ( !in_array( $citation['content'], $processed ) ) {
				call_user_func( $callback, $citation );
				$processed[] = $citation['content'];
			}
		}
	}
}
