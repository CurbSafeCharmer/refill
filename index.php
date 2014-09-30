<?php
require_once __DIR__ . "/includes/config.php";
?>
<!doctype html>
<html>
<head>
	<title>Reflinks</title>
	<meta charset="utf-8"/>
	<link rel="stylesheet" href="style/core.css"/>
	<script src="scripts/jquery-1.11.1.min.js"></script>
	<script src="scripts/index.js"></script>
</head>
<body>
	<h1>Reflinks</h1>

	<?php
		if ( file_exists( __DIR__ . "/includes/banner.php" ) ) {
			include __DIR__ . "/includes/banner.php";
		}
	?>
	
	<h2>Fetch content from <?php echo $config['wiki']['name']; ?></h2>
	<form id="form-wiki" method="post" action="result.php">
		<input name="page" type="text" placeholder="Page name"/>
		<input name="method-wiki" type="submit" value="Fix page"/>
		<h3>Options</h3>
		<ul id="form-wiki-options" class="optionul">
			<li>
				<input name="plainlink" id="checkbox-plainlink-wiki" type="checkbox"/>
				<label for="checkbox-plainlink-wiki">Use plain formatting instead of <code>{{cite web}}</code></label>
			</li>
			<li>
				<input name="noremovetag" id="checkbox-noremovetag-wiki" type="checkbox"/>
				<label for="checkbox-noremovetag-wiki">Do not remove link rot tags</label>
			</li>
			<!--
			<li>
				<input name="nofixuplain" id="checkbox-nofixuplain-wiki" type="checkbox"/>
				<label for="checkbox-nofixuplain-wiki">Do not expand uncaptioned plain links (surrounded with [ ])</label>
			</li>
			-->
			<li>
				<input name="nofixcplain" id="checkbox-nofixcplain-wiki" type="checkbox"/>
				<label for="checkbox-nofixcplain-wiki">Do not expand references with a captioned external link only</label>
			</li>
			<li>
				<input name="nouseoldcaption" id="checkbox-nouseoldcaption-wiki" type="checkbox"/>
				<label for="checkbox-nouseoldcaption-wiki">Do not use old captions</label>
			</li>
			<li>
				<input name="nofixutemplate" id="checkbox-nofixutemplate-wiki" type="checkbox"/>
				<label for="checkbox-nofixutemplate-wiki">Do not expand <code>{{cite web}}</code> templates with a URL only</label>
			</li>
			<li>
				<input name="addblankmetadata" id="checkbox-addblankmetadata-wiki" type="checkbox"/>
				<label for="checkbox-addblankmetadata-wiki">Add blank <code>|author=</code> and <code>|date=</code> fields if the information is unavailable</label>
			</li>
		</ul>
	</form>
	
	<div id="advanced">
		<p id="or">or</p>
		
		<h2>Enter wikitext</h2>
		<form id="form-wikitext" method="post" action="result.php">
			<textarea name="text" rows="10" cols="100"></textarea>
			<input name="method-wikitext" type="submit" value="Fix wikitext"/>
			<h3>Options</h3>
			<ul id="form-wikitext-options" class="optionul">
				<li>
					<input name="plainlink" id="checkbox-plainlink-wikitext" type="checkbox"/>
					<label for="checkbox-plainlink-wikitext">Use plain formatting instead of <code>{{cite web}}</code></label>
				</li>
				<li>
					<input name="noremovetag" id="checkbox-noremovetag-wikitext" type="checkbox"/>
					<label for="checkbox-noremovetag-wikitext">Do not remove link rot tags</label>
				</li>
				<li>
					<input name="nofixuplain" id="checkbox-nofixuplain-wikitext" type="checkbox"/>
					<label for="checkbox-nofixuplain-wikitext">Do not expand uncaptioned plain links (surrounded with [ ])</label>
				</li>
				<li>
					<input name="nofixcplain" id="checkbox-nofixcplain-wikitext" type="checkbox"/>
					<label for="checkbox-nofixcplain-wikitext">Do not expand references with a captioned external link only</label>
				</li>
				<li>
					<input name="nouseoldcaption" id="checkbox-nouseoldcaption-wikitext" type="checkbox"/>
					<label for="checkbox-nouseoldcaption-wikitext">Do not use old captions</label>
				</li>
				<li>
					<input name="nofixutemplate" id="checkbox-nofixutemplate-wikitext" type="checkbox"/>
					<label for="checkbox-nofixutemplate-wikitext">Do not expand <code>{{cite web}}</code> templates with a URL only</label>
				</li>
				<li>
					<input name="addblankmetadata" id="checkbox-addblankmetadata-wiki" type="checkbox"/>
					<label for="checkbox-addblankmetadata-wiki">Add blank <code>|author=</code> and <code>|date=</code> fields if the information is unavailable</label>
				</li>
			</ul>
		</form>
	</div>
	<?php
		include __DIR__ . "/includes/footer.php";
	?>
</body>
</html>

