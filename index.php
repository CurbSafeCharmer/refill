<?php
	require __DIR__ . "/includes/core.php";
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
	<h2>Enter wikitext</h2>
	<form method="post" action="index.php">
		<textarea name="text" rows="10" cols="100">
Here is a bare reference: <ref>https://en.wikipedia.org</ref>

Here is another one: <ref name="named" group="nb">http://example.com</ref>

Here is one that doesn't work: <ref name="404">http://example.com/nonexistant</ref>

Not a bare reference: <ref>[http://example.com not a bear ref]</ref></textarea>
		<input name="method-wikitext" type="submit" value="Fix wikitext"/>
	</form>
	<h2>Fetch content from wiki</h2>
	<form method="post" action="index.php">
		<input type="text" name="page" placeholder="Page name"/>
		<input name="method-wiki" type="submit" value="Fix page"/>
	</form>
	<?php
		if ( isset( $_POST['method-wiki'] ) ) {
			$content = fetchWiki( $_POST['page'] );
			$text = fixRef( $content );
		} elseif ( isset( $_POST['method-wikitext'] ) ) {
			$text = fixRef( $_POST['text'] );
		}
		$stext = htmlentities( $text );
		echo "<h2>Result</h2><pre id='result'>$stext</pre>";
	?>
	<small><a href="https://github.com/zhaofengli/reflinks">Source</a> ♦ <a href="https://en.wikipedia.org/wiki/User:Zhaofeng_Li">Zhaofeng Li</a> ♦ Original Reflinks by Dispenser</small>
</body>
</html>

