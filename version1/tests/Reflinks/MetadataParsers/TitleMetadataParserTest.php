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
	TitleMetadatsParser tests
*/

namespace Reflinks\MetadataParsers;

class TitleMetadataParserTest extends MetadataParserTestCase {
	public function setUp() {
		$this->parser = new TitleMetadataParser();
	}
	
	public function testHeadTitle() {
		$this->assertTitleEquals( "<title>Test title</title>", "", "Test title" );
	}

	public function testMetaTagTitle() {
		$this->assertTitleEquals( "<meta property='og:title' content='Test title'/>", "", "Test title" );
		$this->assertTitleEquals( "<meta name='sailthru.title' content='Test title'/>", "", "Test title" );
	}

	public function testH1Title() {
		$this->assertTitleEquals( "", "<h1>Test title</h1>", "Test title" );
	}

	/**
	  * @depends testHeadTitle
	  * @depends testMetaTagTitle
	  * @depends testH1Title
	  */
	public function testSuffixedTitle() {
		$this->assertTitleEquals( "<title>Test title - MySite</title>", "<h1>MySite</h1><h1>Test title</h1>", "Test title" );
	}

	public function assertTitleEquals( $head = "", $body = "", $expected ) {
		$result = $this->parse( $head, $body );
		$this->assertEquals( $expected, $result->title );
	}
}
