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
require_once __DIR__ . "/includes/core.php";
require_once __DIR__ . "/includes/php-diff/lib/Diff.php";
require_once __DIR__ . "/includes/php-diff/lib/Diff/Renderer/Html/SideBySide.php";

$options = getOptions();
$title = "";
$edittimestamp = 0;
if ( !empty( $options['text'] ) ) { // Manual wikitext input
	$source = $_POST['text'];
} elseif ( !empty( $options['page'] ) ) { // Fetch from wiki (API)
	$source = fetchWiki( $options['page'], $title, $edittimestamp );
} else {
	echo "Error: No source is specified!";
	die;
}

$log = array();
$result = fixRef( $source, $log, $options );
$timestamp = generateWikiTimestamp();
$edittimestamp = generateWikiTimestamp( $edittimestamp );

// remove link rot tags
if ( !count( $log['skipped'] ) && !isset( $options['noremovetag'] ) ) { // Hurray! All fixed!
	$result = removeBareUrlTags( $result );
}

// initialize diff class
$a = explode( "\n", $source );
$b = explode( "\n", $result );
$diff = new Diff( $a, $b, $config['diffconfig'] );
$diffrenderer = new Diff_Renderer_Html_SideBySide;

// generate default summary
$counter = count( $log['fixed'] );
$counterskipped = count( $log['skipped'] );
$summary = str_replace( "%numfixed%", $counter, $config['summary'] );
$summary = str_replace( "%numskipped%", $counterskipped, $summary );

// santize for displaying
$ssource = htmlspecialchars( $source, ENT_QUOTES );
$sresult = htmlspecialchars( $result );
$stitle = htmlspecialchars( $title );
$utitle = urlencode( $title );

// display the result
?>
<!doctype html>
<html>
<head>
	<title>Reflinks</title>
	<meta charset="utf-8"/>
	<link rel="stylesheet" href="style/core.css"/>
	<script src="scripts/jquery-1.11.1.min.js"/></script>
	<?php echo "<script src='" . $config['wdiff'] . "'></script>";?>
</head>
<body>
	<?php
		if ( !empty( $title ) ) {
			echo "<h1>Reflinks - $stitle</h1>";
		} else {
			echo "<h1>Reflinks</h1>";
		}
		if ( file_exists( __DIR__ . "/includes/banner.php" ) ) {
			include __DIR__ . "/includes/banner.php";
		}
		echo "<input id='wikitext-old' type='hidden' value='" . $ssource . "'/>";
		echo "<form id='form-wikitext' name='editform' method='post' action='{$config['wiki']['indexphp']}?title=$utitle&action=submit' enctype='multipart/form-data'>";
		echo "<h2>Result</h2>";
		echo "<p class='notice'>You are responsible for every edit you make. Please double-check the edit before saving!</p>";
		if ( !isset( $options['plainlink'] ) )
			echo "<p>Note: The publisher field is intentionally left blank for filling out manually.</p>";
		if ( !$counter ) {
			echo "<p>No changes made.</p>";
		} else {
			echo "<p>$counter reference(s) fixed!</p>";
		}
		echo $diff->render( $diffrenderer ); // show diff
		echo "<div id='wdiff'></div>";
		if ( count( $log['skipped'] ) ) {
			echo "<p>The following reference(s) could not be filled:<ul id='skipped-refs'>";
			foreach( $log['skipped'] as $skipped ) {
				$sref = htmlspecialchars( $skipped['ref'] );
				$reason = getSkippedReason( $skipped['reason'] );
				echo "<li><code class='url'>$sref</code> <span class='reason'>$reason ({$skipped['status']})</span></li>";
			}
			echo "</ul></p>";
		}
		echo "<h3>New wikitext</h3>";
		echo "<textarea id='wikitext-new' name='wpTextbox1' rows='10' cols='100'>$sresult</textarea>";
		echo "<input type='hidden' name='wpSummary' value='$summary'/>";
		echo "<input type='hidden' name='wpAutoSummary' value='y'/>";
		echo "<input type='hidden' name='wpStarttime' value='$timestamp'/>";
		echo "<input type='hidden' name='wpEdittime' value='$edittimestamp'/>";
		if ( !isset( $options['nowatch'] ) ) { // Let's watch this!
			echo "<input type='hidden' name='wpWatchthis' value='y'/>";
		}
		if ( !empty( $title ) && count ( $log['fixed'] ) ) {
			echo "<input type='submit' name='wpDiff' value='Preview / Save on wiki'/>";
		}
		echo "</form>";
	?>
	<a href='index.php' class='back'>Fix another page...</a>
	<?php
		include __DIR__ . "/includes/footer.php";
	?>
	<script src="scripts/result.js"></script>
</body>
</html>
		
