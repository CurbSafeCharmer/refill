<?php
/*
	Reflinks API

	This is a simple (and dirty)  RESTful API for Reflinks. It'll
	handle the following requests:
	* Generate options form for on-wiki gadget
	  (replacing scripts/toolboxform.php)
	* Run Reflinks against a page or POST-submitted wikitext
	* Generate citations for a single link
*/

namespace Reflinks;

use Reflinks\LinkHandlers\CitoidLinkHandler;
use Reflinks\Exceptions\LinkHandlerException;

require_once __DIR__ . "/../src/bootstrap.php";

$app = new Reflinks();

if ( empty( $_GET['action'] ) ) {
	$action = "help";
} else {
	$action = $_GET['action'];
}

switch ( $action ) {
	default:
	case "help":
		$doc = <<<EOF
# Reflinks API

`?action=` can be one of:
- help: Show this response.
- optionsform*: Get a HTML form with available options
- citegen*: Generate a citation from a URL
- i18n*: Lists all messages
- reflinks*: Run Reflinks

Actions tagged with * give JSONP outputs. Remember to set `callback=` in your script.

## `optionsform`
This is intended to be used by the on-wiki gadget.
It returns a JSON object with the "form" property filled with
the HTML.

## `citegen`
Set `url=` to the URL and `format=` to `CiteTemplateGenerator` or `PlainCs1Generator`,
and the resulting JSON object will contain:
- `success`: Whether the action has succeeded or not
- `error`: The error code from the LinkHandler (Unstable - Don't depend on this)
- `description`: Human-readable description of the error (Unstable - Don't parse this)
- `citation`: The resulting citation

## `i18n`
Lists all messages.

## `reflinks`
Not implemented yet.
EOF;
		echo $doc;
		break;
	case "optionsform":
		$result = array(
			'form' => $app->optionsProvider->generateForm( "toolbox", false )
		);
		$json = json_encode( $result );
		echo $_GET['callback'] . "(" . $json . ")";
		break;
	case "citegen":
		$url = $_GET['url'];
		$result = array();
		if ( !filter_var( $url, FILTER_VALIDATE_URL ) ) {
			$result['success'] = false;
			$result['description'] = "Invalid URL supplied.";
			// TODO: Implement machine-friendly handling
			break;
		} else {
			$handler = new CitoidLinkHandler( $app->spider );
			try {
				$metadata = $handler->getMetadata( $url );
			} catch ( LinkHandlerException $e ) {
				$result['success'] = false;
				$result['error'] = $e->getCode();
				$message = $e->getMessage();
				$result['description'] = empty( $message ) ? $handler->explainErrorCode() : $message;
				$unsuccessful = true;
			}
			if ( !isset( $unsuccessful ) ) {
				if ( !$metadata->exists( "title" ) ) {
					$result['success'] = false;
					$result['description'] = "No title found.";
				} else {
					$citegen = "Reflinks\\CitationGenerators\\" . $_GET['format'];
					if ( class_exists( $citegen ) ) {
						$result['success'] = true;
						$generator = new $citegen( new UserOptions(), new DateFormat() );
						$result['citation'] = $generator->getCitation( $metadata, new DateFormat() );
					} else {
						$result['success'] = false;
						$result['description'] = "No such citation generator.";
					}
				}
			}
		}
		$json = json_encode( $result );
		echo $_GET['callback'] . "(" . $json . ")";
		break;
	case "i18n":
		global $I18N;
		$result = array();
		$domain = $I18N->getDomain();
		$lang = $I18N->getLang();
		$keys = $I18N->listMsgs( $domain );
		foreach ( $keys as $key ) {
			$result[$key] = $I18N->rawMsg( $domain, $lang, $key );
		}
		$json = json_encode( $result );
		echo $_GET['callback'] . "(" . $json . ")";
                break;
}
