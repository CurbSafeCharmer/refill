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
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/src/config.default.php";

set_time_limit( $config['maxtime'] );

$app = new Reflinks();
$result = $app->getResult();

if ( $result['status'] !== Reflinks::STATUS_SUCCESS ) {
	switch ( $result['failure'] ) {
		case Reflinks::FAILURE_NOSOURCE:
			echo "Error: No source is specified!";
			die;
		case Reflinks::FAILURE_PAGENOTFOUND:
			echo "Error: Page not found!";
			die;
	}
}

$counter = count( $result['log']['fixed'] );
// santize for displaying
$sold = htmlspecialchars( $result['old'], ENT_QUOTES );
$sresult = htmlspecialchars( $result['new'] );
if ( $result['source'] == Reflinks::SOURCE_WIKI ) {
	$stitle = htmlspecialchars( $result['actualname'] );
	$utitle = urlencode( $result['actualname'] );
}

// display the result
?>
<!doctype html>
<html>
<head>
	<title>Reflinks</title>
	<meta charset="utf-8"/>
	<link rel="stylesheet" href="style/core.css"/>
	<script src="bower_components/jquery/dist/jquery.min.js"/></script>
	<?php echo "<script src='" . $config['wdiff'] . "'></script>";?>
</head>
<body>
	<?php
		if ( $result['source'] == Reflinks::SOURCE_WIKI ) {
			echo "<h1>Reflinks - $stitle</h1>";
		} else {
			echo "<h1>Reflinks</h1>";
		}
		if ( file_exists( __DIR__ . "/config/banner.php" ) ) {
			include __DIR__ . "/config/banner.php";
		}
		echo "<input id='wikitext-old' type='hidden' value='" . $sold . "'/>";
		echo "<form id='form-wikitext' name='editform' method='post' action='{$result['indexphp']}?title=$utitle&action=submit' enctype='multipart/form-data'>";
		echo "<h2>Result</h2>";
		echo "<p class='notice'>You are responsible for every edit you make. Please double-check the edit before saving!</p>";
		if ( $app->options->get( "noaccessdate" ) ) {
			echo "<p>Note: Dates of access are omitted in the result. Please verify whether the references still support the statements, and add the dates where appropriate.</p>";
		}
		if ( !$app->options->get( "plainlink" ) ) {
			echo "<p>Note: The publisher field is intentionally left blank for filling out manually.</p>";
		}
		if ( !$counter ) {
			echo "<p>No reference fixed.</p>";
		} else {
			echo "<p>$counter reference(s) fixed!</p>";
		}
		echo "<div id='wdiff'></div>";
		if ( count( $result['log']['skipped'] ) ) {
			echo "<p>The following reference(s) could not be filled:<ul id='skipped-refs'>";
			foreach( $result['log']['skipped'] as $skipped ) {
				$sref = htmlspecialchars( $skipped['ref'] );
				$reason = $app->getSkippedReason( $skipped['reason'] );
				echo "<li><code class='url'>$sref</code> <span class='reason'>$reason ({$skipped['status']})</span></li>";
			}
			echo "</ul></p>";
		}
		echo "<h3>New wikitext</h3>";
		echo "<textarea id='wikitext-new' class='wikitext' name='wpTextbox1'>$sresult</textarea>";
		echo "<input type='hidden' name='wpSummary' value='{$result['summary']}'/>";
		echo "<input type='hidden' name='wpAutoSummary' value='y'/>";
		echo "<input type='hidden' name='wpStarttime' value='{$result['timestamp']}'/>";
		echo "<input type='hidden' name='wpEdittime' value='{$result['edittimestamp']}'/>";
		if ( !$app->options->get( "nowatch" ) ) { // Let's watch this!
			echo "<input type='hidden' name='wpWatchthis' value='y'/>";
		}
		if ( $result['source'] == Reflinks::SOURCE_WIKI && $result['old'] != $result['new'] ) {
			echo "<input type='submit' name='wpDiff' value='Preview / Save on wiki'/>";
		}
		echo "</form>";
	?>
	<a href='index.php' class='back'>Fix another page...</a>
	<?php
		include __DIR__ . "/src/footer.php";
	?>
	<script src="scripts/result.js"></script>
</body>
</html>
		
