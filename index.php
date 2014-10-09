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
			<li>
				<input name="noaccessdate" id="checkbox-noaccessdate-wiki" type="checkbox" checked=""/>
				<label for="checkbox-noaccessdate-wiki">Do not add access dates in the result</label>
			</li>
		</ul>
	</form>
	
	<div id="advanced">
		<p id="or">or</p>
		
		<h2>Enter wikitext</h2>
		<form id="form-wikitext" method="post" action="result.php">
			<textarea name="text" class="wikitext"></textarea>
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
					<input name="addblankmetadata" id="checkbox-addblankmetadata-wikitext" type="checkbox"/>
					<label for="checkbox-addblankmetadata-wikitext">Add blank <code>|author=</code> and <code>|date=</code> fields if the information is unavailable</label>
				</li>
				<li>
					<input name="noaccessdate" id="checkbox-noaccessdate-wikitext" type="checkbox" checked=""/>
					<label for="checkbox-noaccessdate-wikitext">Do not add access dates in the result</label>
				</li>
			</ul>
		</form>
	</div>
	<?php
		include __DIR__ . "/includes/footer.php";
	?>
</body>
</html>

