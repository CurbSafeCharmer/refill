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

$result = fixRef( $source );

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
		echo "<form id='editform' name='editform' method='post' action='{$config['indexphp']}?title=$stitle&action=submit' enctype='multipart/form-data'>";
		echo "<textarea name='wpTextbox1' rows='10' cols='100'>$sresult</textarea>";
		if ( !empty( $title ) ) {
			echo "<input type='submit' name='wpPreview' value='Preview your changes on wiki'/>";
		}
		echo "</form>";
	?>
</body>
</html>
		
