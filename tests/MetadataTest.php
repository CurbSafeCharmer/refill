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
	Metadata parser tests
*/

require_once __DIR__ . "/../includes/metadata.php";

class MetadataTest extends PHPUnit_Framework_TestCase {
	// Actual tests
	
	// Part 1 - Title extraction
	public function testBasicTitle() { // <title>
		$title = $this->getTestTitle();
		$this->quickTest( "<title>$title</title>", "", "title", $title );
	}
	public function testH1Title() {
		$title = $this->getTestTitle();
		$this->quickTest( "", "<h1>$title</h1>", "title", $title );
	}
	public function testItempropHeadlineTitle() { // schema.org
		$title = $this->getTestTitle();
		$this->quickTest( "", "<span itemprop='headline'>$title</span>", "title", $title );
	}
	public function testOpenGraphTitle() { // Facebook
		$title = $this->getTestTitle();
		$this->quickTest( "<meta property='og:title' content='$title'>", "", "title", $title );
	}
	/**
	 * @depends testBasicTitle
	 * @depends testH1Title
	 */
	public function testSuffixedTitles() { // A suffixed <title> plus a clean <h1> (which is what we want)
		$title = $this->getTestTitle();
		$stitle = $this->getSuffixedTestTitle();
		$this->quickTest( "<title>$stitle</title>", "<h1>$title</h1>", "title", $title );
	}
	/**
	 * @depends testBasicTitle
	 * @depends testH1Title
	 */
	public function testUnrelatedTitle() {
		// Some sites use <h1> inappropriately for stuff that are not titles at all (e.g. site name) *facepalm*
		$title = $this->getTestTitle();
		$stitle = $this->getSuffixedTestTitle();
		$utitle = $this->getUnrelatedTestTitle();
		$this->quickTest( "<title>$stitle</title>", "<h1>$utitle</h1><h1>$title</h1>", "title", $title );
	}
	
	// Utils for Part 1
	public function getTestTitle() {
		return "Such grammar. Many mess. So stress.";
	}
	public function getSuffixedTestTitle() {
		return $this->getTestTitle() . " - Doge News";
	}
	public function getUnrelatedTestTitle() {
		return "The Doge of Venice";
	}
	
	// Part 2 - Author extraction
	public function testItempropAuthor() { // schema.org
		$author = $this->getTestAuthor();
		// Pass 1 - Author name is the content of <* itemprop="author">
		$this->quickTest( "", "<span itemprop='author'>$author</span>", "author", $author );
		// Pass 2 - Author name is in the <* itemprop="name"> element under <* itemprop="author">
		$this->quickTest( "", "<div itemprop='author'><span itemprop='name'>$author</span></div>", "author", $author );
	}
	public function testMetaAuthor() { // <meta name="author">
		$author = $this->getTestAuthor();
		$this->quickTest( "<meta name='author' content='$author'>", "", "author", $author );
	}
	/**
	 * @depends testItempropAuthor
	 * @depends testMetaAuthor
	 */
	public function testDomainNameAuthor() { // We don't want domain name in the author field
		$domain = $this->getTestDomainName();
		$html = $this->generateCompleteHTML( "<meta name='author' content='$domain'>", "" );
		$metadata = extractMetadata( $html );
		$this->assertEmpty( $metadata['author'] );
	}
	/**
	 * @depends testItempropAuthor
	 * @depends testMetaAuthor
	 */
	public function testStripByFromAuthor() { // Strip "by" or "from"
		$author = $this->getTestAuthor();
		$sauthor = "By $author";
		$this->quickTest( "", "<span itemprop='author'>$sauthor</span>", "author", $author );
		$sauthor = "From $author";
		$this->quickTest( "", "<span itemprop='author'>$sauthor</span>", "author", $author );
	}
	
	// Utils for Part 2
	public function getTestAuthor() {
		return "Cave Johnson";
	}
	public function getTestDomainName() {
		return "example.com";
	}
	
	// Part 3 - Date extraction
	public function testItempropDatePublished() { // schema.org
		$date = $this->getTestDate();
		$this->quickTest( "", "<span itemprop='datePublished' content='$date'></span>", "date", $date );
	}
	public function testMetaDate() { // various other metadata schemas in <meta>
		$date = $this->getTestDate();
		$this->quickTest( "<meta name='date' content='$date'>", "", "date", $date );
		$this->quickTest( "<meta name='article:published_time' content='$date'>", "", "date", $date );
		$this->quickTest( "<meta name='sailthru.date' content='$date'>", "", "date", $date );
	}
	
	// Utils for Part 3
	public function getTestDate() {
		return "1412425654";
	}
	
	// Part 4 - Site name (work) extraction
	public function testOpenGraphWork() { // Facebook
		$work = $this->getTestWork();
		$this->quickTest( "<meta property='og:site_name' content='$work'>", "", "work", $work );
	}
	
	// Utils for Part 4
	public function getTestWork() {
		return "Doge News";
	}
	
	// Common utils
	public function quickTest( $head = "", $body = "", $field = "title", $equals = "" ) {
		$html = $this->generateCompleteHTML( $head, $body );
		$metadata = extractMetadata( $html );
		return $this->assertEquals( $equals, $metadata[$field] );
	}
	public function generateCompleteHTML( $head = "", $body = "" ) {
		return "<!doctype html><html><head>$head</head><body>$body</body></html>";
	}
}
