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

namespace Reflinks;

use Reflinks\Metadata;

require_once __DIR__ . "/MetadataParserMock.php";

class MetadataParserChainTest extends \PHPUnit_Framework_TestCase {
	public function testAppendObject() {
		$chain = new MetadataParserChain();
		$mock = $this->getMock( "MetadataParser" );
		$this->assertTrue( $chain->append( $mock ) );
	}

	public function testAppendClassName() {
		$chain = new MetadataParserChain();
		$chain->append( "Reflinks\UnitTestMockMetadataParserA" );
		$this->assertTrue( $chain->append( "UnitTestMockMetadataParserB" ) );
	}

	/**
	 * @depends testAppendClassName
	 * @expectedException Reflinks\Exceptions\NoSuchMetadataParserException
	 */
	public function testAppendNonExistantClassName() {
		$chain = new MetadataParserChain();
		$chain->append( "NoSuchNamespace\ThisIsANonExistantMetadataParser" . time() );
	}

	/**
	 * @depends testAppendObject
	 * @expectedException Reflinks\Exceptions\ErroneousMetadataParserException
	 */
	public function testAppendErroneousObject() {
		$chain = new MetadataParserChain();
		$object = new \stdClass();
		$chain->append( $object );
	}

	/**
	 * @depends testAppendClassName
	 */
	public function testConstructArray() {
		$chain = new MetadataParserChain( array( "UnitTestMockMetadataParserB" ) );
	}

	/**
	 * @depends testAppendObject
	 * @depends testAppendClassName
	 */
	public function testChainCallWithoutBaseMetadata() {
		$chain = new MetadataParserChain();
		$mock = $this->getMockBuilder( "MetadataParser" )
		             ->setMethods( array( "chain" ) )
			     ->getMock();
		$mock->expects( $this->once() )
		     ->method( "chain" )
		     ->with( $this->isInstanceOf( "DOMDocument" ),
		             $this->isInstanceOf( "Reflinks\\Metadata" ) );
		$chain->append( $mock );
		$chain->parse( new \DOMDocument() );
	}

	/**
	 * @depends testChainCallWithoutBaseMetadata
	 */
	public function testChainCallWithBaseMetadata() {
		if ( defined( "HHVM_VERSION" ) ) {
			$this->markTestSkipped( "Can't mock Metadata in HHVM." );
			return;
		}
		$chain = new MetadataParserChain();
		$metadata = $this->getMockBuilder( "Reflinks\\Metadata" )
		                 ->getMock();
		$mock = $this->getMockBuilder( "MetadataParser" )
		             ->setMethods( array( "chain" ) )
		             ->getMock();
		$mock->expects( $this->once() )
		     ->method( "chain" )
		     ->with( $this->anything(),
		             $this->identicalTo( $metadata ) );
		$chain->append( $mock );
		$chain->parse( new \DOMDocument(), $metadata );
	}
}

class UnitTestMockMetadataParserA extends MetadataParser {
	public function parse( \DOMDocument $dom ){}	
}
