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
	Wikimedia wiki provider

	This wiki provider is designed to handle Wikimedia wiki farms, but should work 
	fine with similar ones.

	Wikimedia wikis have a language code and a project name. For example, the 
	full identifier for English Wikipedia is "en.wikipedia". For maximum flexibility, 
	this WikiProvider will accept "en", "enwiki", "en.wikipedia", "en.wikipedia.org"
	and "wikipedia" as valid identifiers for English Wikipedia.

	A sample configuration looks like:
	$config['wm-defaultproject'] = "wikipedia";
	$config['wm-projects'] = array(
		'wikipedia' => array(
			'default' => 'en',
			'api' => 'https://%id%.wikipedia.org/w/api.php',
			'indexphp' => 'https://%id%.wikipedia.org/w/index.php',
			'wikis' => array(
				'en', // no special handling
				'simple' => array( // override language code
					'language' => 'en'
				)
			)
		),
		'wikimedia' => array(
			'default' => 'meta',
			'language' => 'en', // the default language for the project
			'api' => 'https://%id%.wikimedia.org/w/api.php',
			'indexphp' => 'https://%id%.wikimedia.org/w/index.php',
			'wikis' => array( 'meta', 'commons' )
		)
	);
*/

namespace Reflinks\WikiProviders;

use Reflinks\WikiProvider;
use Reflinks\Wiki;

class WikimediaWikiProvider extends WikiProvider {
	public $defaultProject;
	public $projects = array();
	
	function __construct( $args = null ) {
		global $config;
		$this->defaultProject = $config['wm-defaultproject'];
		$this->projects = $config['wm-projects'];
	}

	// does not necessarily return a existent wiki
	public function resolveIdentifier( $identifier ) {
		$dots = substr_count( $identifier, "." );
		if ( $dots == 0 ) {
			// strip off the trailing "wiki"
			$pos = -1 * strlen( "wiki" );
			if ( "wiki" == substr( $identifier, $pos ) ) {
				$identifier = substr( $identifier, 0, $pos );
			}
			// something like "en"?
			return array( 'project' => $this->defaultProject, 'language' => $identifier );
		} else { // "{language}.{project}" or "{language}.{project}.org"?
			$array = explode( ".", $identifier );
			return array( 'project' => $array[1], 'language' => $array[0] );
		}
	}

	public function getWikiDetails( $project, $language ) {
		$result = array();
		if ( empty( $this->projects[$project] ) ) {
			return false;
		} else {
			$result['api'] = str_replace( "%id%", $language, $this->projects[$project]['api'] );
			$result['indexphp'] = str_replace( "%id%", $language, $this->projects[$project]['indexphp'] );

			if ( empty( $this->projects[$project]['language'] ) ) {
				$result['language'] = $language;
			} else {
				$result['language'] = $this->projects[$project]['language'];
			}
			if ( !empty( $this->projects[$project]['wikis'][$language] ) ) {
				foreach ( $this->projects[$project]['wikis'][$language] as $key => $value ) {
					$result[$key] = $value;
				}
				return $result;
			} else if ( in_array( $language, $this->projects[$project]['wikis'] ) ) {
				return $result;
			} else {
				return false;
			}
		}
	}

	public function getWiki( $identifier ) {
		$path = $this->resolveIdentifier( $identifier );
		$details = $this->getWikiDetails( $path['project'], $path['language'] );
		if ( $details ) {
			$wiki = new Wiki( $details['api'], $details['indexphp'] );
			$wiki->language = $details['language'];
			return $wiki;
		} else {
			return false;
		}
	}

	public function listWikis() {
		$result = array();
		foreach ( $this->projects as $project => $p ) {
			foreach ( $p['wikis'] as $key => $value ) {
				if ( is_numeric( $key ) ) {
					$language = $value;
				} else {
					$language = $key;
				}
				if ( $project == $this->defaultProject ) {
					$result[] = $language;
				} else {
					$result[] = "$language.$project";
				}
			}
		}
		return $result;
	}
}

