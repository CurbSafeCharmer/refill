<?php
/*
	Copyright (c) 2014-2015, Zhaofeng Li
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

use Reflinks\Exceptions\LinkHandlerException;
use Reflinks\LinkHandler;
use Reflinks\WikiProvider;
use Reflinks\Utils;

// Let's leave them hard-coded for now...
use Reflinks\CitationGenerators\CiteTemplateGenerator;
use Reflinks\CitationGenerators\PlainCs1Generator;

class Reflinks {
	public $optionsProvider = null;
	public $options = null;
	public $spamFilter = null;
	public $wikiProvider = null;
	public $linkHandlers = array();
	public $wiki = null;
	
	const SKIPPED_UNKNOWN = 0;
	const SKIPPED_NOTITLE = 1;
	const SKIPPED_HANDLER = 2;
	const SKIPPED_SPAM = 3;
	const SKIPPED_CONFIGBL = 4;
	const SKIPPED_NOHANDLER = 5;
	
	const STATUS_SUCCESS = 0;
	const STATUS_FAILED = 1;
	
	const FAILURE_NOSOURCE = 1;
	const FAILURE_PAGENOTFOUND = 2;
	
	const SOURCE_TEXT = 0;
	const SOURCE_WIKI = 1;
	
	function __construct() {
		global $config;
		$this->optionsProvider = new UserOptionsProvider();

		$this->options = new UserOptions( $this->optionsProvider );
		$this->options->load( $_GET );
		$this->options->load( $_POST );

		$this->spider = new Spider( $config['useragent'] );

		$this->spamFilter = new SpamFilter();

		$this->linkHandlers = $config['linkhandlers'];

		$wikiProvider = Utils::getClass( $config['wikiprovider'], "Reflinks\\WikiProviders" );
		if ( !$wikiProvider ) {
			throw new NoSuchWikiProviderException( $config['wikiprovider'] );
		} else if ( $wikiProvider instanceof WikiProvider ) {
			throw new ErroneousWikiProviderException( $config['wikiprovider'] );
		} else { // All good
			$this->wikiProvider = new $wikiProvider( $config['wikiproviderargs'] );
		}

		if ( !$this->options->get( "wiki" ) ) {
			$this->options->set( "wiki", "en" ); // TODO: Fix this hard-coded default
		}

		if ( $wiki = $this->wikiProvider->getWiki( $this->options->get( "wiki" ) ) ) {
			$this->wiki = $wiki;
		} else {
			$this->wiki = null;
		}
	}

	protected function getLinkHandler( $url ) {
		$handler = null;
		foreach ( $this->linkHandlers as $handlerinfo ) {
			if ( !is_array( $handlerinfo ) ) { // matches all links
				$handler = $handlerinfo;
				break;
			} else { // matches specific links
				if ( preg_match( $handlerinfo['regex'], $url ) ) {
					$handler = $handlerinfo['handler'];
					break;
				}
			}
		}
		if ( $handler ) {
			$linkHandler = Utils::getClass( $handler, "Reflinks\\LinkHandlers" );
			if ( !$linkHandler ) {
				throw new NoSuchLinkHandlerException( $handler );
			} else if ( $linkHandler instanceof LinkHandler ) {
				throw new ErroneousLinkHandlerException( $handler );
			} else {
				return new $linkHandler( $this->spider );
			}
		} else {
			return false;
		}
	}
	
	public function fix( $wikitext, &$log = array(), &$unfinished = false ) {
		/*
			FIXME: This is, by far, one of the worst
			pieces of code in the whole project. Clean it up.
		*/
		$cm = new CitationManipulator( $wikitext );
		$log = array(
			'fixed' => array(), // ['url'] contains the original link
			'skipped' => array(), // ['ref'] contains the original ref, ['reason'] contains the reason const, ['status'] contains the status code
		);
		$dateFormat = Utils::detectDateFormat( $wikitext );
		$options = &$this->options;
		$spamFilter = &$this->spamFilter;
		$limit = $this->options->get( "limit" );
		$app = &$this;
		$callback = function( $citation ) use ( &$cm, &$log, &$options, &$spamFilter, &$limit, $dateFormat, $app ) {
			global $I18N;
			$status = 0;
			$core = $citation['content'];
			$unchanged = false;
			// Let's check if we are supposed to mess with it first...
			if ( preg_match( "/\{\{(Dead link|404|dl|dead|Broken link)/i", $core ) ) { // dead link tag
				return;
			}
			if ( Utils::isCitationEmpty( $citation['content'] ) ) {
				if ( count( $citation['attributes'] ) > 0 ) { // has some attributes - let's turn it into a stub
					$stub = $cm->generateStub( $citation['attributes'] );
					$cm->replaceIdentical( $citation['complete'], $stub );
				}
				return;
			}

			// Let's find out what kind of reference it is...
			$parser = new CitationParser();
			$metadata = $parser->parse( $core );

			if ( $metadata ) { // Needs fixing
				if ( $limit !== -1 ) {
					$limit--;
				}

				if ( $spam = $spamFilter->check( $metadata->url ) ) {
					switch ( $spam ) {
						default:
						case SpamFilter::TYPE_SPAM:
							$log['skipped'][] = array(
								'ref' => $core,
								'reason' => $app::SKIPPED_SPAM,
								'status' => $status,
							);
							return;
						case SpamFilter::TYPE_CONFIGBL:
							$log['skipped'][] = array(
								'ref' => $core,
								'reason' => $app::SKIPPED_CONFIGBL,
								'status' => $status,
							);
							return;	
					}
				}
				$linkHandler = $this->getLinkHandler( $metadata->url );
				if ( !$linkHandler ) {
					$log['skipped'][] = array(
						'ref' => $core,
						'reason' => $app::SKIPPED_NOHANDLER,
						'status' => $status,
					);
					return;
				}
				try {
					$metadata = $linkHandler->getMetadata( $metadata->url, $metadata );
				} catch ( LinkHandlerException $e ) {
					$message = $e->getMessage();
					if ( !empty( $message ) ) {
						$description = $message;
					} else {
						$description = $app->linkHandler->explainErrorCode( $e->getCode() );
					}
					$log['skipped'][] = array(
						'ref' => $core,
						'reason' => $app::SKIPPED_HANDLER,
						'description' => $description,
					);
					$unchanged = true;
					$newcore = $core;
				}

				// finally{} is available on PHP 5.5+, but we need to maintain compatibility with 5.3... What a pity :(
				if ( !$unchanged ) {
					if ( empty( $metadata->title ) ) {
						$log['skipped'][] = array(
							'ref' => $core,
							'reason' => $app::SKIPPED_NOTITLE,
							'status' => $response->header['http_code'],
						);
						$unchanged = true;
					} else {
						if ( !$metadata->exists( "work" ) && !$metadata->exists( "via" ) && $options->get( "usedomainaswork" ) ) { // Use the base domain as work
							$metadata->work = Utils::getBaseDomain( $metadata->url );
						}
						// Generate cite template
						if ( $options->get( "plainlink" ) ) { // use plain CS1
							$generator = new PlainCs1Generator( $options, $dateFormat );
						} else { // use {{cite web}}
							$generator = new CiteTemplateGenerator( $options, $dateFormat );
						}
						$generator->setI18n( $I18N );
						if ( $app->wiki ) {
							$generator->setWikiContext( $app->wiki );
						}
						$newcore = $generator->getCitation( $metadata );
						$log['fixed'][] = array(
							'url' => $metadata->url
						);
					}
				}
			} else {
				$unchanged = true;
				$newcore = $core;
			}

			if ( $cm->hasDuplicates( $core ) ) {
				$duplicates = $cm->searchByContent( $core );
				$attributes = array();
				$startAttrs = "";
				foreach ( $duplicates as $duplicate ) {
					if ( isset( $duplicate['attributes'] ) ) { // So one of the duplicates has a name
						foreach ( $duplicate['attributes'] as $name => $value ) {
							$attributes[$name] = $value;
						}
					}
				}
				if ( empty( $attributes['name'] ) ) {
					if ( $metadata ) {
						if ( !empty( $metadata->author ) ) {
							$attributes['name'] = strtolower( str_replace( " ", "", $metadata->author ) );
						} else {
							$attributes['name'] = Utils::getBaseDomain( $metadata->url );
						}
					} else {
						$attributes['name'] = "auto";
					}
					if ( $cm->hasExactAttribute( "name", $attributes['name'] ) ) {
						$suffix = 1;
						while ( true ) {
							if ( $cm->hasExactAttribute( "name", $attributes['name'] . $suffix ) ) {
								$suffix++;
							} else {
								break;
							}
						}
						$attributes['name'] .= $suffix;
					}
				}
				$replacement = $cm->generateCitation( $newcore, $attributes );
				$stub = $cm->generateStub( $attributes );
				$cm->replaceByContent( $core, $replacement, $stub );
			} elseif ( !$unchanged ) { // Just keep the original surrounding tags
				$replacement = $citation['startTag'] . $newcore . $citation['endTag'];
				$cm->replaceByContent( $core, $replacement );
			}
			if ( $limit === 0 ) {
				return true; // limit exceeded
			}
		};
		if ( $cm->loopCitations( $callback ) === false ) { // stopped prematurely
			$unfinished = true;
		} else {
			$unfinished = false;
		}
		return $cm->exportWikitext();
	}

	public function getResult() {
		global $config, $I18N;
		$result = array();
		
		// Fetch the source wikitext
		if ( $text = $this->options->get( "text" ) ) {
			$result['old'] = $text;
			$result['source'] = self::SOURCE_TEXT;
		} elseif ( $page = $this->options->get( "page" ) ) {
			if ( !$this->wiki ) {
				$result['status'] = self::STATUS_FAILED;
				return $result;
			}
			$source = $this->wiki->fetchPage( $page, $this->spider );
			if ( !$source['successful'] ) {
				$result['status'] = self::STATUS_FAILED;
				$result['failure'] = self::FAILURE_PAGENOTFOUND;
				return $result;
			}
			$result['old'] = $source['wikitext'];
			$result['source'] = self::SOURCE_WIKI;
			$result['api'] = $this->wiki->api;
			$result['indexphp'] = $this->wiki->indexphp;
			$result['actualname'] = $source['actualname'];
			$result['edittimestamp'] = Utils::generateWikiTimestamp( $source['timestamp'] );
		} else {
			$result['status'] = self::STATUS_FAILED;
			$result['failure'] = self::FAILURE_NOSOURCE;
			return $result;
		}
		
		// Fix the wikitext
		$result['new'] = $this->fix( $result['old'], $result['log'], $result['unfinished'] );
		if (
			!count( $result['log']['skipped'] )
			&& !$result['unfinished']
			&& !$this->options->get( "noremovetag" )
		) { // Remove bare URL tags
			$result['new'] = Utils::removeBareUrlTags( $result['new'] );
		}
		
		// Generate default summary
		$counter = count( $result['log']['fixed'] );
		$counterskipped = count( $result['log']['skipped'] );
		if ( !isset( $config['summary'] ) ) { //Use the I18N engine
			$toollink = $I18N->msg( "toollink" );
			$result['summary'] = $I18N->msg( "summary", array(
				"lang" => $this->wiki->language,
				"variables" => array(
					$counter, $counterskipped, $toollink
				),
			) );
		} else { // Use the one supplied by the local config
			$result['summary'] = str_replace( "%numfixed%", $counter, $config['summary'] );
			$result['summary'] = str_replace( "%numskipped%", $counterskipped, $result['summary'] );
		}
		if ( isset( $config['summaryextra'] ) ) {
			$result['summary'] .= $config['summaryextra']; // Add extra information
		}
		$result['timestamp'] = Utils::generateWikiTimestamp();
		
		$result['status'] = self::STATUS_SUCCESS;
		return $result;
	}
	public function getSkippedReason( $reason ) {
		switch ( $reason ) {
			default:
			case self::SKIPPED_UNKNOWN:
				return "Unknown error";
			case self::SKIPPED_HANDLER:
				return "Processing error";
			case self::SKIPPED_NOTITLE:
				return "No title is found";
			case self::SKIPPED_SPAM:
				return "Spam blacklist";
			case self::SKIPPED_CONFIGBL:
				return "Blacklisted";
			case self::SKIPPED_NOHANDLER:
				return "No suitable handler found";
		}
	}
}

