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
	Simple wiki provider

	This is the default wiki provider of reFill. It reads in the list of wikifarms
	from $config['wikis'] or $args, and generates URLs for individual wikis according 
	to the template URLs provided.
*/

namespace Reflinks\WikiProviders;

use Reflinks\WikiProvider;
use Reflinks\Wiki;

class SimpleWikiProvider extends WikiProvider {
	public $wikis = array();
	
	function __construct( $args = null ) {
		global $config;
		if ( $args !== null ) {
			$this->wikis = $wikis;
		} else {
			$this->wikis = $config['wikis'];
		}
	}

	public function getWiki( $identifier ) {
		foreach ( $this->wikis as $type => $details ) {
			if ( in_array( $identifier, $details['identifiers'] ) ) {
				$api = str_replace( "%id%", $identifier, $details['api'] );
				$indexphp = str_replace( "%id%", $identifier, $details['indexphp'] );
				return new Wiki( $api, $indexphp );
			}
		}
		return false;
	}

	public function listWikis() {
		$result = array();
		foreach ( $this->wikis as $type => $details ) {
			$result = array_merge( $result, $details['identifiers'] );
		}
		return $result;
	}
}

