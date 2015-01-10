<?php
namespace Reflinks;
require_once __DIR__ . "/src/bootstrap.php";

$app = new Reflinks();
$regularForm = generateForm( $app, false );
$advancedForm = generateForm( $app, true );
$wikis = $app->wikiProvider->listWikis();

echo $twig->render( "main.html", array(
	"options_regular" => $regularForm,
	"options_advanced" => $advancedForm,
	"wikis" => $wikis,
) );

function generateForm( &$app, $advanced = false ) {
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
