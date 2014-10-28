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
	Main class
*/

namespace Reflinks;

use Reflinks\CitationGenerators\CiteTemplateGenerator;
use Reflinks\CitationGenerators\PlainCs1Generator;
use Masterminds\HTML5;

class Reflinks {
	public $optionsProvider = null;
	public $options = null;
	public $spider = null;
	public $metadataParserChain = null;
	public $spamFilter = null;
	public $wikiProvider = null;
	
	const SKIPPED_NOTITLE = 1;
	const SKIPPED_SPAM = 2;
	const SKIPPED_FETCHERROR = 3;
	const SKIPPED_HTTPERROR = 4;
	const SKIPPED_EMPTY = 5;
	
	const STATUS_SUCCESS = 0;
	const STATUS_FAILED = 1;
	
	const FAILURE_NOSOURCE = 1;
	const FAILURE_PAGENOTFOUND = 2;
	
	const SOURCE_TEXT = 0;
	const SOURCE_WIKI = 1;
	
	function __construct( array $objects = array() ) {
		global $config;
		if ( $objects['optionsProvider'] !== null ) {
			$this->optionsProvider = $objects['optionsProvider'];
		} else {
			$this->optionsProvider = new UserOptionsProvider();
		}
		
		if ( $objects['options'] !== null ) {
			$this->options = $objects['options'];
		} else {
			$this->options = new UserOptions( $this->optionsProvider );
			$this->options->load( $_GET );
			$this->options->load( $_POST );
		}
		
		if ( $objects['spider'] !== null ) {
			$this->spider = $objects['spider'];
		} else {
			$this->spider = new Spider( $config['useragent'] );
		}
		
		if ( $objects['metadataParserChain'] !== null ) {
			$this->metadataParserChain = $objects['metadataParserChain'];
		} else {
			$this->metadataParserChain = new MetadataParserChain( $config['parserchain'] );
		}
		
		if ( $objects['spamFilter'] !== null ) {
			$this->spamFilter = $objects['spamFilter'];
		} else {
			$this->spamFilter = new SpamFilter();
		}
		
		if ( $objects['wikiProvider'] !== null ) {
			$this->wikiProvider = $objects['wikiProvider'];
		} else {
			$this->wikiProvider = new WikiProvider();
		}
	}
	
	public function fix( $wikitext, &$log = array() ) {
		$pattern = "/(\<ref[^\>]*\>)([^\<\>]+)(\<\/ref\>)/i";
		$matches = array();
		$log = array(
			'fixed' => array(), // ['url'] contains the original link
			'skipped' => array(), // ['ref'] contains the original ref, ['reason'] contains the reason const, ['status'] contains the status code
		);
		$dateformat = Utils::detectDateFormat( $wikitext );
		preg_match_all( $pattern, $wikitext, $matches );
		foreach ( $matches[2] as $key => $core ) {
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
				if ( filter_var( $cmatches[1], FILTER_VALIDATE_URL ) && !$this->options->get( "nofixcplain" ) ) {
					$oldref['url'] = $cmatches[1];
					$oldref['caption'] = $cmatches[2];
				} else {
					continue;
				}
			} elseif ( preg_match( "/^\[(http[^ ]+)\]$/i", $tcore, $cmatches ) ) {
				// an uncaptioned plain link (consists of only a URL, surrounded with [])
				if ( filter_var( $cmatches[1], FILTER_VALIDATE_URL ) && !$this->options->get( "nofixuplain" ) ) {
					$oldref['url'] = $cmatches[1];
				} else {
					continue;
				}
			} elseif ( preg_match( "/^\{\{cite web\s*\|\s*url=(http[^ \|]+)\s*\}\}$/i", $tcore, $cmatches ) ) {
				// an uncaptioned {{cite web}} template (Please improve the regex)
				if ( filter_var( $cmatches[1], FILTER_VALIDATE_URL ) && !$this->options->get( "nofixutemplate" ) ) {
					$oldref['url'] = $cmatches[1];
				} else {
					continue;
				}
			} else {
				// probably already filled in, let's skip it
				continue;
			}
			
			if ( $this->spamFilter->check( $oldref['url'] ) ) {
				$log['skipped'][] = array(
					'ref' => $core,
					'reason' => self::SKIPPED_SPAM,
					'status' => $status,
				);
				continue;
			}
		
			// Fetch the webpage
			$response = $this->spider->fetch( $oldref['url'] );
			if ( !$response->successful ) { // failed
				$log['skipped'][] = array(
					'ref' => $core,
					'reason' => self::SKIPPED_FETCHERROR,
					'failure' => $response->header['failure'],
				);
				continue;
			} elseif ( $response->header['http_code'] != 200 ) { // http error
				$log['skipped'][] = array(
					'ref' => $core,
					'reason' => self::SKIPPED_HTTPERROR,
					'status' => $response->header['http_code'],
				);
				continue;
			} elseif ( empty( $response->html ) ) { // empty response
				$log['skipped'][] = array(
					'ref' => $core,
					'reason' => self::SKIPPED_EMPTY,
					'status' => $response->header['http_code'],
				);
				continue;
			}

			// Extract the metadata
			$metadata = new Metadata();
			$metadata->url = $oldref['url'];
			if ( $this->options->get( "usedomainaswork" ) ) { // Use the base domain as work
				$metadata->work = Utils::getBaseDomain( $oldref['url'] );
			}
			$html5 = new HTML5();
			$dom = $html5->loadHTML( $response->html );
			$metadata->merge( $this->metadataParserChain->parse( $dom ) );
			
			if ( !empty( $oldref['caption'] ) && !$this->options->get( "nouseoldcaption" ) ) {
				// Use the original caption
				$metadata->title = $oldref['caption'];
			}
			
			if ( empty( $metadata->title ) ) {
				$log['skipped'][] = array(
					'ref' => $core,
					'reason' => self::SKIPPED_NOTITLE,
					'status' => $response->header['http_code'],
				);
				continue;
			}
			
			// Generate cite template
			if ( $this->options->get( "plainlink" ) ) { // use plain CS1
				$generator = new PlainCs1Generator( $this->options );
			} else { // use {{cite web}}
				$generator = new CiteTemplateGenerator( $this->options );
			}
			$newcore = $generator->getCitation( $metadata, $dateformat );
		
			// Replace the old core
			$replacement = $matches[1][$key] . $newcore . $matches[3][$key]; // for good measure
			$wikitext = str_replace( $matches[0][$key], $replacement, $wikitext );
			$log['fixed'][] = array(
				'url' => $oldref['url'],
			);
		}
		return $wikitext;
	}
	public function getResult() {
		global $config;
		$result = array();
		
		// Fetch the source wikitext
		if ( $text = $this->options->get( "text" ) ) {
			$result['old'] = $text;
			$result['source'] = self::SOURCE_TEXT;
		} elseif ( $page = $this->options->get( "page" ) ) {
			if ( !$this->options->get( "wiki" ) ) {
				$this->options->set( "wiki", "en" ); // TODO: Fix this hard-coded default
			}
			if ( !$wiki = $this->wikiProvider->getWiki( $this->options->get( "wiki" ) ) ) {
				$result['status'] = self::STATUS_FAILED;
				return $result;
			}
			$source = $wiki->fetchPage( $page, $this->spider );
			if ( !$source['successful'] ) {
				$result['status'] = self::STATUS_FAILED;
				$result['failure'] = self::FAILURE_PAGENOTFOUND;
				return $result;
			}
			$result['old'] = $source['wikitext'];
			$result['source'] = self::SOURCE_WIKI;
			$result['api'] = $wiki->api;
			$result['indexphp'] = $wiki->indexphp;
			$result['actualname'] = $source['actualname'];
			$result['edittimestamp'] = Utils::generateWikiTimestamp( $source['timestamp'] );
		} else {
			$result['status'] = self::STATUS_FAILED;
			$result['failure'] = self::FAILURE_NOSOURCE;
			return $result;
		}
		
		// Fix the wikitext
		$result['new'] = $this->fix( $result['old'], $result['log'] );
		if ( !$this->options->get( "noremovetag" ) ) {
			$result['new'] = Utils::removeBareUrlTags( $result['new'] );
		}
		
		// Generate default summary
		$counter = count( $result['log']['fixed'] );
		$counterskipped = count( $result['log']['skipped'] );
		$result['summary'] = str_replace( "%numfixed%", $counter, $config['summary'] );
		$result['summary'] = str_replace( "%numskipped%", $counterskipped, $result['summary'] );
		$result['timestamp'] = Utils::generateWikiTimestamp();
		
		$result['status'] = self::STATUS_SUCCESS;
		return $result;
	}
	public function getSkippedReason( $reason ) {
		switch ( $reason ) {
			case self::SKIPPED_FETCHERROR:
				return "Fetching error";
			case self::SKIPPED_HTTPERROR:
				return "HTTP Error";
			case self::SKIPPED_EMPTY:
				return "Empty response";
			case self::SKIPPED_NOTITLE:
				return "No title is found";
			case self::SKIPPED_SPAM:
				return "Spam blacklist";
			default:
				return "Unknown error";
		}
	}
}

