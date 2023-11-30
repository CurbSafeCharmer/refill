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

namespace Reflinks;

/**
 * The Citation class
 *
 * An instance of this class represents a citation.
 */
class Citation {
	/**
	 * The Citation ID used in CitationManipulator
	 * @var int
	 */
	public $id = 0;

	/**
	 * The content of the citation
	 * @var string
	 */
	public $content = "";

	/**
	 * Whether the citation should be completely removed in the output
	 * @var bool
	 */
	public $isDeleted = false;

	/**
	 * Whether the citation should be represented as a self-closing <ref/> tag with $content ignored
	 * @var bool
	 */
	public $isStub = false;

	/**
	 * Whether a CitationGenerator should be used to generate the inner $content from $metadata
	 * @var bool
	 */
	public $useGenerator = false;

	/**
	 * An associative array storing the HTML attributes of the <ref> tag
	 * @var string[]
	 */
	public $attributes = array();

	/**
	 * The CitationGenerator to be used to generate the inner $content
	 * @var CitationGenerator
	 */
	public $generator = null;

	/**
	 * Bibilography metadata of the referenced material
	 * @var Metadata
	 */
	public $metadata = null;

	/**
	 * Type of the citation. The value is one of the TYPE_* constants
	 * @var int
	 */
	public $type = 0;

	/**
	 * The CitationParser to be used
	 * @var CitationParser
	 */
	protected $parser = null;

	/**
	 * The original attributes of the citation
	 *
	 * Used to prevent dirty diffs
	 *
	 * @var string[]
	 */
	protected $origAttributes = array();

	/**
	 * The original isStub attribute
	 *
	 * Used to prevent dirty diffs
	 *
	 * @var bool
	 */
	protected $origIsStub = false;

	/**
	 * The original opening <ref> tag of the citation
	 *
	 * Used to prevent dirty diffs. When the original citation is a stub,
	 * this property contains the complete code of the citation.
	 *
	 * @var string
	 */
	protected $origOpeningTag = "";

	/**
	 * The original closing </ref> tag of the citation
	 *
	 * Used to prevent dirty diffs. When the original citation is a stub,
	 * this property is empty.
	 *
	 * @var string
	 */
	protected $origClosingTag = "";

	/**
	 * The original inner content of the citation
	 *
	 * Used to prevent dirty diffs. When the original citation is a stub,
	 * this property is empty.
	 *
	 * @var string
	 */
	protected $origContent = "";

	/**
	 * The original raw HTML attributes
	 *
	 * Used to prevent dirty diffs
	 *
	 * @var string
	 */
	protected $origRawAttributes = "";

	/**
	 * An unknown kind of citation.
	 */
	const TYPE_UNKNOWN = 1;

	/**
	 * A citation with only a plain link.
	 */
	const TYPE_BARE = 2;

	/**
	 * A citation with only a captioned plain link.
	 */
	const TYPE_CAPTIONED = 4;

	/**
	 * A citation with only a template containing only a bare URL.
	 */
	const TYPE_BARETEMPLATE = 8;

	/**
	 * Initialize the Citation object with some wikitext
	 *
	 * The parser will try its best to get as much information as possible from the wikitext.
	 * $metadata and $attributes will be automatically populated.
	 *
	 * @param string $code The complete wikitext of the citation, with <ref> tags or not
	 */
	public function __construct( $code ) {
		$this->parser = new CitationParser();
		$this->loadCode( $code );
	}

	/**
	 * Generate citation code
	 *
	 * @return string The full citation code, including the <ref> tags
	 */
	public function getCode() {
		$openingTag = "";
		$closingTag = "";
		$this->getTags( $openingTag, $closingTag );
		$content = $this->getContent();
		return $openingTag . $content . $closingTag;
	}

	/**
	 * Generate inner content
	 * @return string The inner content
	 */
	public function getContent() {
		if ( $this->isStub ) {
			return "";
		} else {
			if ( $this->useGenerator ) {
				return $this->generator->getCitation( $this->metadata );
			} else {
				return $this->content;
			}
		}
	}

	/**
	 * Get <ref> tags
	 * @param string $openingTag (Pass by reference) To be filled with the opening <ref> tag. If the citation is a stub, it will be filled with the complete code.
	 * @param string $closingTag (pass by reference) To be filled with the closing </ref> tag. If the citation is a stub, it will be empty.
	 */
	protected function getTags( &$openingTag, &$closingTag ) {
		if (
			( $this->attributes == $this->origAttributes ) &&
			( $this->isStub == $this->origIsStub )
		) {
			// use the original tags to prevent dirty diffs
			$openingTag = $this->origOpeningTag;
			$closingTag = $this->origClosingTag;
		} else {
			// generate the tags
			$openingTag = "<ref";
			if ( $this->attributes == $this->origAttributes ) {
				$rawAttributes = $this->origRawAttributes;
			} else {
				$rawAttributes = $this->generateAttributes();
			}
			if ( !empty( $rawAttributes ) ) {
				$openingTag .= " $rawAttributes";
			}
			if ( $this->isStub ) {
				$openingTag .= "/>";
				$closingTag = "";
				return true;
			} else {
				$openingTag .= ">";
				$closingTag = "</ref>";
				return true;
			}
		}
	}

	/**
	 * Generate HTML attributes code
	 * @return string The HTML attributes code
	 */
	protected function generateAttributes() {
		$rawAttributes = "";
		foreach ( $this->attributes as $name => $attribute ) {
			$rawAttributes .= $name . '="' . htmlentities( $attribute ) . '" ';
		}
		$rawAttributes = rtrim( $rawAttributes, " " );
		return $rawAttributes;
	}

	/**
	 * Load raw citation code
	 *
	 * @param string $code The complete wikitext of the citation, with <ref> tags or not
	 */
	protected function loadCode( $code ) {
		// Parse the code
		$this->parser->parseCode(
			$code,
			$this->origOpeningTag,
			$this->origRawAttributes,
			$this->origContent,
			$this->origClosingTag
		);
		if ( "" === $this->origClosingTag ) {
			$this->isStub = true;
			$this->origIsStub = true;
		} else {
			$this->isStub = false;
			$this->origIsStub = false;
		}

		// Parse raw attributes
		$this->parser->parseAttributes(
			$this->origRawAttributes,
			$this->origAttributes
		);
		$this->attributes = $this->origAttributes;

		// Parse inner content
		$this->loadContent( $this->origContent );
	}

	/**
	 * Load citation content
	 */
	protected function loadContent( $content ) {
		$this->metadata = new Metadata();
		$this->content = $content;
		$this->parser->parseContent(
			$this->content,
			$this->metadata,
			$this->type
		);
	}
}
