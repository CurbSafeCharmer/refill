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

require_once( __DIR__ . "/CitationGeneratorMock.php" );
class CitationTest extends \PHPUnit_Framework_TestCase {
	public function dataChanges() {
			return array(
				"Tag reuse" => array(
					"<ref    name='reusethis'>Original</ref>", // original
					"<ref    name='reusethis'>Changed</ref>", // expected
					array( // changes to the citation
						"content" => "Changed",
					)
				),
				"Attribute reuse #1" => array(
					"<ref name='reusethis'>Remove content</ref>",
					"<ref name='reusethis'/>",
					array(
						"isStub" => true,
					)
				),
				"Attribute reuse #2" => array(
					"<ref name='reusethis'/>",
					"<ref name='reusethis'>Content added</ref>",
					array(
						"isStub" => false,
						"content" => "Content added",
					)
				),
				"Attribute generation" => array(
					"<ref name='roar'>Content</ref>",
					"<ref name=\"meow\" group=\"nb\">Content</ref>",
					array(
						"attributes" => array(
							"name" => "meow",
							"group" => "nb",
						)
					)
				)
			);
	}
	/**
	 * @dataProvider dataChanges
	 */
	public function testChanges( $original, $expected, $changes ) {
		$citation = new Citation( $original );
		foreach ( $changes as $property => $value ) {
			$citation->{$property} = $value;
		}
		$this->assertEquals( $expected, $citation->getCode() );
	}

	public function testCitationGenerator() {
		$original = "<ref>Throw this away</ref>";
		$expectedContent = "We need this instead :P";
		$expected = "<ref>$expectedContent</ref>";
		$citation = new Citation( $original );
		$generator = $this->getMockBuilder( 'Reflinks\\CitationGenerators\\UnitTestMockCitationGenerator' )
		                  ->disableOriginalConstructor()
						  ->getMock();
		$generator->expects( $this->any() )
		          ->method( "getCitation" )
		          ->willReturn( $expectedContent );
		$metadata = $this->getMockBuilder( "Metadata" )
		                 ->getMock();
		$citation->generator = $generator;
		$citation->useGenerator = true;
		$this->assertEquals( $expected, $citation->getCode() );
	}
}
