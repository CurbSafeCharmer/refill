<?php
require __DIR__ . "/includes/core.php";
$title = "";
if ( isset( $_POST['method-wikitext'] ) ) { // Manual wikitext input
	$source = $_POST['text'];
} elseif ( isset( $_POST['method-wiki'] ) ) { // Fetch from wiki (API)
	if ( isset( $_POST['page'] ) ) { // Page name set
		$source = fetchWiki( $_POST['page'], $title );
	} else {
		echo "Error: No page is specified!";
		die;
	}
}

$counter = 0;
$result = fixRef( $source, isset( $_POST['config-plainlink'] ) ? true : false, $counter );

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
		echo "<form id='form-wikitext' name='editform' method='post' action='{$config['indexphp']}?title=$stitle&action=submit' enctype='multipart/form-data'>";
		echo "<h2>Result</h2>";
		if ( !$counter ) {
			echo "<p>No changes made.</p>";
		} else {
			echo "<p>$counter reference(s) fixed!</p>";
		}
		echo "<textarea name='wpTextbox1' rows='10' cols='100'>$sresult</textarea>";
		echo "<input type='hidden' name='wpSummary' value='{$config['summary']}'/>";
		if ( !empty( $title ) ) {
			echo "<input type='submit' name='wpPreview' value='Preview / Save on wiki'/>";
		}
		echo "</form>";
	?>
	<footer>
		<a href="https://github.com/zhaofengli/reflinks">Source</a> ♦ <a href="https://en.wikipedia.org/wiki/User:Zhaofeng_Li/Reflinks">Info</a> ♦ by <a href="https://en.wikipedia.org/wiki/User:Zhaofeng_Li">Zhaofeng Li</a> ♦ Original Reflinks by <a href="https://en.wikipedia.org/wiki/User:Dispenser">Dispenser</a>
	</footer>
</body>
</html>
		
