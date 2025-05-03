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
				),
				Citation::TYPE_BARE
			),
			"bare-malformed" => array(
				"scheme://not.a.supported.scheme",
				false,
				Citation::TYPE_UNKNOWN
			),
			"bare-malformed2" => array(
				"httphttp://not.really.http.mind.you",
				false,
				Citation::TYPE_UNKNOWN
			),
			"bare-malformed3" => array(
				"httpshttps://not.really.https.mind.you",
				false,
				Citation::TYPE_UNKNOWN
			),
			"uncaptioned" => array(
				"[http://zhaofeng.li]",
				array(
					"url" => "http://zhaofeng.li"
				),
				Citation::TYPE_BARE
			),
			"captioned" => array(
				"[http://zhaofeng.li Caption]",
				array(
					"url" => "http://zhaofeng.li",
					"title" => "Caption"
				),
				Citation::TYPE_CAPTIONED
			),
			"template" => array(
				"{{cite web|url=http://zhaofeng.li}}",
				array(
					"url" => "http://zhaofeng.li"
				),
				Citation::TYPE_BARETEMPLATE
			),
			"nottemplate" => array(
				"{{cite web|url=http://zhaofeng.li|title=PleaseLeaveThisAlone}}",
				false,
				Citation::TYPE_UNKNOWN
			),
			"nottemplate2" => array(
				"{{cite web|url=http://zhaofeng.li|title=Please leave this alone}}",
				false,
				Citation::TYPE_UNKNOWN
			)
		);
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testCitationParser( $citation, $expectedMetadata, $expectedType ) {
		$parser = new CitationParser;
		$metadata = new Metadata();
		$type = -1;
		$parser->parseContent( $citation, $metadata, $type );
		if ( is_array( $expectedMetadata ) ) {
			foreach ( $expectedMetadata as $name => $value ) {
				$this->assertEquals( $value, $metadata->get( $name ) );
			}
		}
		if ( false !== $expectedType ) {
			$this->assertEquals( $expectedType, $type );
		}
	}
}
