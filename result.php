<?php
require_once __DIR__ . "/includes/core.php";
require_once __DIR__ . "/includes/php-diff/lib/Diff.php";
require_once __DIR__ . "/includes/php-diff/lib/Diff/Renderer/Html/SideBySide.php";

$options = getOptions();
$title = "";
if ( !empty( $options['text'] ) ) { // Manual wikitext input
	$source = $_POST['text'];
} elseif ( !empty( $options['page'] ) ) { // Fetch from wiki (API)
	$source = fetchWiki( $options['page'], $title );
} else {
	echo "Error: No source is specified!";
	die;
}

$log = array();
$result = fixRef( $source, $log, $options );
$timestamp = generateWikiTimestamp();

// remove link rot tags
if ( !count( $log['skipped'] ) && !isset( $options['noremovetag'] ) ) { // Hurray! All fixed!
	$result = removeBareUrlTags( $result );
}

// initialize diff class
$a = explode( "\n", $source );
$b = explode( "\n", $result );
$diff = new Diff( $a, $b, $config['diffconfig'] );
$diffrenderer = new Diff_Renderer_Html_SideBySide;

// santize for displaying
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
</head>
<body>
	<?php
		If ( !empty( $title ) ) {
			echo "<h1>Reflinks - $stitle</h1>";
		} else {
			echo "<h1>Reflinks</h1>";
		}
		echo "<form id='form-wikitext' name='editform' method='post' action='{$config['indexphp']}?title=$utitle&action=submit' enctype='multipart/form-data'>";
		echo "<h2>Result</h2>";
		echo "<p class='notice'>You are responsible for every edit you make. Please double check the edit before saving!</p>";
		if ( !$counter = count( $log['fixed'] ) ) {
			echo "<p>No changes made.</p>";
		} else {
			echo "<p>$counter reference(s) fixed!</p>";
		}
		echo $diff->render( $diffrenderer ); // show diff
		if ( count( $log['skipped'] ) ) {
			echo "<p>The following references are skipped:<ul id='skipped-refs'>";
			foreach( $log['skipped'] as $skipped ) {
				$sref = htmlspecialchars( $skipped['ref'] );
				$reason = getSkippedReason( $skipped['reason'] );
				echo "<li><code class='url'>$sref</code> <span class='reason'>$reason ({$skipped['status']})</span></li>";
			}
			echo "</ul></p>";
		}
		echo "<textarea name='wpTextbox1' rows='10' cols='100'>$sresult</textarea>";
		echo "<input type='hidden' name='wpSummary' value='{$config['summary']}'/>";
		echo "<input type='hidden' name='wpAutoSummary' value='y'/>";
		echo "<input type='hidden' name='wpStarttime' value='$timestamp'/>";
		echo "<input type='hidden' name='wpWatchthis' value='y'/>";
		if ( !empty( $title ) && count ( $log['fixed'] ) ) {
			echo "<input type='submit' name='wpPreview' value='Preview / Save on wiki'/>";
		}
		echo "</form>";
	?>
	<a href='index.html' class='back'>Fix another page...</a>
	<footer>
		<a href="https://github.com/zhaofengli/reflinks">Source</a> ♦ <a href="https://en.wikipedia.org/wiki/User:Zhaofeng_Li/Reflinks">Info</a> ♦ by <a href="https://en.wikipedia.org/wiki/User:Zhaofeng_Li">Zhaofeng Li</a> ♦ Original Reflinks by <a href="https://en.wikipedia.org/wiki/User:Dispenser">Dispenser</a>
	</footer>
</body>
</html>
		
