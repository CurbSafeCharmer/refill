<?php
require __DIR__ . "/includes/core.php";
if ( !isset( $_GET['page'] ) ) {
	echo "No page specified.";
	exit;
}
?>
<!doctype html>
<html>
<head>
	<title>Reflinks</title>
	<meta charset="utf-8"/>
	<link rel="stylesheet" href="style/core.css"/>
</head>
<body>
	<h1>Reflinks</h1>
	<?php
		$page = "";
		$fixed = fixRef( fetchWiki( $_GET['page'], $page ) );
		$sfixed = htmlspecialchars( $fixed );
		$spage = urlencode( $page );
		echo "<form id='editform' name='editform' method='post' action='{$config['indexphp']}?title=$spage&action=submit' enctype='multipart/form-data'>";
		echo "<textarea name='wpTextbox1' rows='10' cols='100'>$sfixed</textarea>";
	?>
	<input type="submit" name="wpPreview" value="Preview your changes on wiki"/></form>
</body>
</html>
		
