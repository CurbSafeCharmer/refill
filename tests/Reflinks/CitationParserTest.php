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

class CitationParserTest extends \PHPUnit_Framework_TestCase {
	public function dataProvider() {
		return array(
			"bare" => array(
				"http://zhaofeng.li",
				array(
					"url" => "http://zhaofeng.li"
				)
			),
			"bare-malformed" => array(
				"scheme://not.a.supported.scheme",
				false
			),
			"uncaptioned" => array(
				"[http://zhaofeng.li]",
				array(
					"url" => "http://zhaofeng.li"
				)
			),
			"captioned" => array(
				"[http://zhaofeng.li Caption]",
				array(
					"url" => "http://zhaofeng.li",
					"title" => "Caption"
				)
			),
			"template" => array(
				"{{cite web|url=http://zhaofeng.li}}",
				array(
					"url" => "http://zhaofeng.li"
				)
			)
		);
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testCitationParser( $citation, $expected ) {
		$parser = new CitationParser;
		$result = $parser->parse( $citation );
		if ( is_array( $expected ) ) {
			foreach ( $expected as $name => $value ) {
				$this->assertEquals( $value, $result->get( $name ) );
			}
		} else {
			$this->assertEquals( $expected, $result );
		}
	}
}
