<?php
/*
	Copyright (c) 2015, Zhaofeng Li
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
	Citation parser
*/

namespace Reflinks;

use Reflinks\Metadata;

class CitationParser {
	public $supportedAttributes = array( "name", "group" );
	public $rules = array(
		Citation::TYPE_BARE => array(
			"regex" => "/^\[?([^ \]]+)\]?$/",
			"metadata" => array( "url" => 1 )
		),
		Citation::TYPE_CAPTIONED => array(
			"regex" => "/^\[([^ ]+) (.+)\]$/",
			"metadata" => array( "url" => 1, "title" => 2 )
		),
		Citation::TYPE_BARETEMPLATE => array(
			"regex" => "/^\{\{cite web\s*\|\s*url=([^\| ]+)\s*\}\}$/i",
			"metadata" => array( "url" => 1 )
		)
	);

	/**
	 * Parse the inner content of a citation.
	 *
	 * @param string $content The content inside the <ref></ref> tag
	 * @param Metadata $metadata (Pass by reference) Requires a initialized Metadata object. To be filled with bibliographical metadata
	 * @param int $type (Pass by reference) To be filled with the type of the citation. The value is one of the TYPE_* constants of the Citation class.
	 *
	 * @return bool True if successful
	 */
	public function parseContent( $content, Metadata &$metadata, &$type ) {
		$content = trim( $content );
		foreach ( $this->rules as $rtype => $rule ) {
			$regex = $rule['regex'];
			if ( preg_match( $regex, $content, $matches ) ) {
				$type = $rtype;
				if ( isset( $rule['metadata'] ) ) {
					foreach ( $rule['metadata'] as $name => $key ) {
						$metadata->set( $name, $matches[$key] );
					}
				}
				if (
					!$metadata->exists( "url" ) ||
					!filter_var( $metadata->url, FILTER_VALIDATE_URL ) ||
					(
						"http" != parse_url( $metadata->url, PHP_URL_SCHEME ) &&
						"https" != parse_url( $metadata->url, PHP_URL_SCHEME )
					)
				) {
					continue;
				} else if (
					$type == Citation::TYPE_CAPTIONED
					&& strpos( $metadata->title, "''" )
				) {
					// Let's deal with an edge case: Some editors put metadata in the link caption
					// If that's the case, don't mess with it
					continue;
				}
				return true;
			}
		}
		$type = Citation::TYPE_UNKNOWN;
		return true;
	}

	/**
	 * Parse citation code and extract information from it
	 *
	 * @param string $code The complete wikitext of the citation, with <ref> tags or not
	 * @param string $openingTag (Pass by reference) To be filled with the opening <ref> tag. If the citation is a stub, it will be filled with the complete code.
	 * @param string $rawAttributes (Pass by reference) To be filled with the raw HTML attributes. If there is no attributes, it will be empty, even if the opening tag looks like <ref    >.
	 * @param string $content (Pass by reference) To be filled with the inner content of the citation. If the citation is a stub, it will be empty.
	 * @param string $closingTag (pass by reference) To be filled with the closing </ref> tag. If the citation is a stub, it will be empty.
	 *
	 * @return bool True if successful
	 */
	public function parseCode( $code, &$openingTag, &$rawAttributes, &$content, &$closingTag ) {
		$pattern = "/"
			 // scenario #1: A full <ref></ref> pair
			 . "(?'pair'"
		         . "(?'openingTag'\<ref(?'rawAttributes'[^\>\/]*)\>)" // the opening <ref> tag, possibly with attributes
		         . "(?'content'.*?)" // content of the citation (inside the surrounding tags)
			 . "(?'closingTag'\<\/ref\>)" // the closing </ref> tag
			 . ")"
			 // end scenario #1
			 . "|" // or...
			 // scenario #2: A self-closing <ref/>
			 . "(?'stub'"
			 . "\<ref"
			 . "(?'stubAttributes'[^\>\/]*)"
			 . "\/>"
			 . ")"
			 // end scenario #2
			 . "/i";
		if ( preg_match( $pattern, $code, $matches ) ) { // is wrapped in <ref> tags
			if ( !empty( $matches['stub'] ) ) { // is a self-closing <ref/> tag
				$openingTag = $matches['stub'];
				$rawAttributes = trim( $matches['stubAttributes'], " " );
				$content = "";
				$closingTag = "";
				return true;
			} else { // is a complete <ref></ref> pair
				$openingTag = $matches['openingTag'];
				$rawAttributes = trim( $matches['rawAttributes'], " " );
				$content = $matches['content'];
				$closingTag = $matches['closingTag'];
				return true;
			}
		} else { // without <ref> tags - Let's treat the whole thing as the $content
			$openingTag = "<ref>";
			$rawAttributes = "";
			$content = $code;
			$closingTag = "</ref>";
			return true;
		}
	}

	/**
	 * Parse raw HTML attributes
	 *
	 * @param string $rawAttributes Raw HTML attributes
	 * @param string[] $attributes (Pass by reference) To be filled by an associative array of HTML attributes
	 *
	 * @return bool True if successful
	 */
	public function parseAttributes( $rawAttributes, &$attributes ) {
		$attributes = array();
		foreach ( $this->supportedAttributes as $attribute ) {
			$value = $this->parseSingleAttribute( $rawAttributes, $attribute );
			if ( !empty( $value ) ) {
				$attributes[$attribute] = $value;
			}
		}
		return true;
	}

	/**
	 * Parse a raw HTML attribute
	 *
	 * @param string $rawAttributes The string containing the raw attributes
	 * @param string $attribute The name of the attribute to parse
	 *
	 * @return string|false The value of the attribute
	 */
	public function parseSingleAttribute( $rawAttributes, $attribute ) {
		$template = "/"
		         . "\\s*" // allow whitespace
		         . "%s" // %s is the attribute name ("name" or "group" or whatever), to be filled with sprintf()
		         . "\\s*" // allow whitespace
		         . "\\=" // Equal sign
		         . "\\s*" // allow whitespace
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
		if ( preg_match( $pattern,  $rawAttributes, $matches ) ) {
			foreach ( array( "doubleQuotedValue", "singleQuotedValue", "unquotedValue" ) as $type )
				if ( !empty( $matches[$type] ) )
					return $matches[$type];
		}
		return false;
	}
}
