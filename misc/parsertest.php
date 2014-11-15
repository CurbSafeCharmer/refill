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
	MetadataParser test page
*/

namespace Reflinks;

use Reflinks\CitationGenerators\CiteTemplateGenerator;
use Reflinks\CitationGenerators\PlainCs1Generator;
use Masterminds\HTML5;

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/config.default.php";

if ( empty( $_POST['parsers'] ) ) {
	$parsers = $config['parserchain'];
} else {
	$parsers = explode( "\r\n", trim( $_POST['parsers'] ) );
}

if ( empty( $_POST['html'] ) ) {
	$html = <<<EOF
<!doctype html>
<html>
<head>
	<title>Example page - MySite</title>
	<meta name="author" content="Zhaofeng Li"/>
	<meta property="og:site_name" content="MySite"/>
</head>
<body itemscope itemtype="http://schema.org/Article">
	<!-- An inappropriate use of h1 -->
	<h1 class="logo">MySite</h1>
	
	<!-- This is the actual title -->
	<h1>Example page</h1>
	<span itemprop="author">by Zhaofeng Li</span>
	
	<p>Hello, world!</p>
</body>
</html>
EOF;
} else {
	$html = $_POST['html'];
}

if ( empty( $_POST['url'] ) ) {
	$url = "http://mysite.tld";
} else {
	$url = $_POST['url'];
}

$chain = new MetadataParserChain( $parsers );
$html5 = new HTML5;
$dom = $html5->loadHTML( $html );
$metadata = new Metadata();
$metadata->url = $url;
$metadata = $chain->parse( $dom, $metadata );
?>
<!doctype html>
<html>
<head>
	<title>Reflinks</title>
	<meta charset="utf-8"/>
	<link rel="stylesheet" href="../style/core.css"/>
</head>
<body>
	<h1>Metadata parser test</h1>
	
	<form id="form" method="post" action="parsertest.php">
		<h2>URL</h2>
		<input name="url" type="text" placeholder="Fake URL of the page" value="<?php echo htmlspecialchars( $url );?>"/>
		<h2>Parser chain</h2>
		<textarea name="parsers" class="wikitext"><?php echo implode( "\r\n", $parsers );?></textarea>
		<h2>HTML</h2>
		<textarea name="html" class="wikitext"><?php echo htmlspecialchars( $html );?></textarea>
		<input type="submit" value="Parse"/>
	</form>
	
	<h2>Result</h2>
	<pre><?php var_dump( $metadata );?></pre>
	
	<h2>Generated references</h2>
	<h3>CiteTemplateGenerator</h3>
	<pre><?php
		$generator = new CiteTemplateGenerator( new UserOptions() );
		echo $generator->getCitation( $metadata, new DateFormat() );
	?></pre>
	
	<h3>PlainCs1Generator</h3>
	<pre><?php
		$generator = new PlainCs1Generator( new UserOptions() );
		echo $generator->getCitation( $metadata, new DateFormat() );
	?></pre>
</body>
</html>
