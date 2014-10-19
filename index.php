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

$app = new Reflinks();
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
		if ( file_exists( __DIR__ . "/config/banner.php" ) ) {
			include __DIR__ . "/config/banner.php";
		}
	?>
	
	<h2>Fetch content from wiki</h2>
	<form id="form-wiki" method="post" action="result.php">
		<input name="page" type="text" placeholder="Page name"/>
		<select name="wiki">
			<?php
				foreach ( $app->wikiProvider->listWikis() as $wiki ) {
					echo "<option value='$wiki'>$wiki</option>";
				}
			?>
		</select>
		<input name="method-wiki" type="submit" value="Fix page"/>
		<h3>Options</h3>
		<?php
			echo $app->optionsProvider->generateForm( "wiki", false );
		?>
	
	</form>
	
	<div id="advanced">
		<p id="or">or</p>
		
		<h2>Enter wikitext</h2>
		<form id="form-wikitext" method="post" action="result.php">
			<textarea name="text" class="wikitext"></textarea>
			<input name="method-wikitext" type="submit" value="Fix wikitext"/>
			<h3>Options</h3>
			<?php
				echo $app->optionsProvider->generateForm( "wikitext", true );
			?>
			</form>
	</div>
	<?php
		include __DIR__ . "/src/footer.php";
	?>
</body>
</html>

