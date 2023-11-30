<?php
/*
	Copyright (c) 2016, Zhaofeng Li
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
	Citoid Link Handler

	This LinkHandler uses Citoid to extract the metadata from a webpage.
	See https://www.mediawiki.org/wiki/Citoid for details.
*/

namespace Reflinks\LinkHandlers;

use Reflinks\LinkHandler;
use Reflinks\Spider;
use Reflinks\Metadata;
use Reflinks\Exceptions\LinkHandlerException;

class CitoidLinkHandler extends LinkHandler {
	private $spider = null;
	public $api = "https://en.wikipedia.org/api/rest_v1/data/citation";
	public static $mapping = array(
		"default" => array(
			// Metadata => Citoid
			'url' => "url",
			'title' => "title",
			"authors" => "author",
			"editors" => "editor",
			"publisher" => "publisher",
			"date" => "date",
			"volume" => "volume",
			"issue" => "issue",
			"pages" => "pages",
			"pmid" => "PMID",
			"pmc" => "PMCID",
			"doi" => "DOI",
			"via" => "libraryCatalog",
			"work" => "websiteTitle",
		),
		"book" => array(
			"title" => "bookTitle",
		),
		"journal" => array(
			"journal" => "publicationTitle",
		)
	);
	public static $typeMapping = array(
		"journalArticle" => "journal",
		"bookSection" => "book",
	);

	const ERROR_UNKNOWN = 0;
	const ERROR_FETCH = 1;

	function __construct( Spider $spider ) {
		global $config;
		$this->spider = $spider;
		if ( isset( $config['citoid']['api'] ) ) {
			$this->api = $config['citoid']['api'];
		}
	}

	public function getMetadata( $url, Metadata $baseMetadata = null ) {
		// Call the Citoid API
		$api = $this->api . "/mediawiki/" . urlencode( urldecode( $url ) );
		$response = $this->spider->fetch( $api, "", false );
		if ( !$response->successful ) { // failed
			throw new LinkHandlerException( "Fetching error", self::ERROR_FETCH );
		}
		$json = json_decode( $response->html, true );
		$json = $json[0];

		if ( $baseMetadata ) {
			$metadata = $baseMetadata;
		} else {
			$metadata = new Metadata();
		}
		if ( isset( $this::$typeMapping[$json['itemType']] ) ) {
			$metadata->type = $this::$typeMapping[$json['itemType']];
		}
		$mapping = $this::$mapping['default'];
		if ( isset( $this::$mapping[$metadata->type] ) ) {
			$mapping = array_merge( $mapping, $this::$mapping[$metadata->type] );
		}
		// Citoid key: $mapping[$metadataKey]
		foreach ( $json as $key => $value ) {
			if ( in_array( $key, $mapping ) ) {
				$metadataKey = array_search( $key, $mapping );
				$metadata->set( $metadataKey, $value );
			}
		}

		if ( $metadata->title == $metadata->url || $metadata->title == $url ) {
			unset( $metadata->title );
		}

		return $metadata;
	}

	public static function explainErrorCode( $code ) {
		switch ( $code ) {
			default:
			case self::ERROR_UNKNOWN:
				return "Unknown error";
			case self::ERROR_FETCH:
				return "Fetching error";
		}
	}
}
