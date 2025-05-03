<?php
/*
	Copyright (c) 2015, Zhaofeng Li
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
require_once __DIR__ . "/../src/bootstrap.php";

$app = new Reflinks();
$regularForm = generateForm( $app, false );
$advancedForm = generateForm( $app, true );
$wikis = $app->wikiProvider->listWikis();
$wikinames = array();
if ( $app->wikiProvider->supportsNaming() ) {
	foreach ( $wikis as $wiki ) {
		if ( false !== $name = $app->wikiProvider->getWikiName( $wiki ) ) {
			$wikinames[$wiki] = $name;
		}
	}
}

echo $twig->render( "main.html", array(
	"options_regular" => $regularForm,
	"options_advanced" => $advancedForm,
	"wikis" => $wikis,
	"wikinames" => $wikinames,
	"pagename" => $app->options->get( 'page' ),
	"wikiname" => $app->options->get( 'wiki' ),
) );

function generateForm( Reflinks &$app, $advanced = false ) {
	$options = $app->optionsProvider->generateFormStructure( $advanced );
	$result = "";
	foreach ( $options as $option ) {
		switch ( $option['type'] ) {
			case "checkbox":
				$result .= "<div class='checkbox'><label><input type='checkbox' name='{$option['name']}'";
				if ( $option['checked'] ) {
					$result .= " checked ";
				}
				$result .= ">" . $option['humanname'] . "</label></div>";
				break;
			case "hidden":
				$result .= "<input type='hidden' name='{$option['name']}' value='{$option['value']}>";
				break;
		}
	}
	return $result;
}
