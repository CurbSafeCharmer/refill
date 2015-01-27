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
	public $rules = array(
		"bare" => array(
			"regex" => "/^(.+)$/",
			"fields" => array( "url" => 1 )
		),
		"captioned" => array(
			"regex" => "/^\[([^ ]+) (.+)\]$/",
			"fields" => array( "url" => 1, "title" => 2 )
		),
		"uncaptioned" => array(
			"regex" => "/^\[([^ ]+)\]$/",
			"fields" => array( "url" => 1 )
		),
		"template" => array(
			"regex" => "/^\{\{cite web\s*\|\s*url=([^ ]+)\s*\}\}$/i",
			"fields" => array( "url" => 1 )
		)
	);

	public function parse( $citation ) {
		$citation = trim( $citation );
		foreach ( $this->rules as $rule ) {
			$regex = $rule['regex'];
			$fields = $rule['fields'];
			if ( preg_match( $regex, $citation, $matches ) ) {
				$metadata = new Metadata();
				foreach ( $fields as $name => $key ) {
					$metadata->set( $name, $matches[$key] );
				}
				if (
					!$metadata->exists( "url" )
					|| !filter_var( $metadata->url, FILTER_VALIDATE_URL )
					|| strpos( $metadata->url, "http" ) !== 0
				) {
					continue;
				} else {
					return $metadata;
				}
			}
		}
		return false;
	}
}
