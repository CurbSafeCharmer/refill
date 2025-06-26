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

class UtilsTest extends \PHPUnit_Framework_TestCase {
    protected function setUp() {
        date_default_timezone_set( "America/Los_Angeles" );
    }

    public function testTimestamp() {
        $this->assertEquals( "20151215014046", Utils::generateWikiTimestamp( 1450172446 ) );
    }

    public function testEmptyCitation() {
        $this->assertFalse( Utils::isCitationEmpty( "[http://example.com]" ) );
        $this->assertTrue( Utils::isCitationEmpty( "" ) );
        $this->assertTrue( Utils::isCitationEmpty( "{{cite web}}" ) );
    }

    public function testEndsWith() {
        // #1: Does not match at all
        $this->assertFalse( Utils::endsWith( "jpg", "f" ) );
        $this->assertFalse( Utils::endsWith( "jpg", "f", true ) );
        $this->assertFalse( Utils::endsWith( "jpg", "F", true ) );
        // #2: $endsWith longer than $subject
        $this->assertFalse( Utils::endsWith( "g", "jpg" ) );
        $this->assertFalse( Utils::endsWith( "g", "jpg", true ) );
        $this->assertFalse( Utils::endsWith( "g", "JPG", true ) );
        // #3: Case sensitivity
        $this->assertFalse( Utils::endsWith( "jpg", "G" ) );
        $this->assertTrue( Utils::endsWith( "jpg", "G", true ) ); // case-insensitive
    }
}
