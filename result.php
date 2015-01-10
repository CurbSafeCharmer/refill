<?php
namespace Reflinks;
require_once __DIR__ . "/src/bootstrap.php";

set_time_limit( $config['maxtime'] );

$app = new Reflinks();
$result = $app->getResult();

if ( $result['status'] !== Reflinks::STATUS_SUCCESS ) {
        switch ( $result['failure'] ) {
                case Reflinks::FAILURE_NOSOURCE:
                        echo $twig->render( "error.html", array( "error" => "No source is specified!" ) );
                        die;
                case Reflinks::FAILURE_PAGENOTFOUND:
                        echo $twig->render( "error.html", array( "error" => "We couldn't retrieve the page! Either it doesn't exist, or there were some network hiccups. Please try again." ) );
                        die;
        }
}

$vars = array();
$vars['wdiff'] = $config['wdiff'];
$vars['counter'] = count( $result['log']['fixed'] );
$vars['wikitext_old'] = htmlspecialchars( $result['old'], ENT_QUOTES );
$vars['wikitext_new'] = htmlspecialchars( $result['new'] );

if ( $result['source'] == Reflinks::SOURCE_WIKI ) {
	if ( $result['old'] != $result['new'] ) {
		$vars['saveable'] = true;
	}
	$vars['pagename'] = $result['actualname'];
	$vars['indexphp'] = $result['indexphp'];
	$vars['summary'] = $result['summary'];
	$vars['timestamp'] = $result['timestamp'];
	$vars['edittimestamp'] = $result['edittimestamp'];
	$vars['title'] = $result['actualname'];
} else {
	$vars['title'] = "Raw Wiki markup";
}
if ( !$app->options->get( "nowatch" ) ) {
	$vars['nowatch'] = true;
}

foreach ( $result['log']['skipped'] as &$citation ) {
	$citation['humanreason'] = $app->getSkippedReason( $citation['reason'] );
	if ( isset( $citation['description'] ) ) {
		$citation['humanreason'] .= " (" . $citation['description'] . ")";
	}
}
$vars['skipped'] = $result['log']['skipped'];

echo $twig->render( "result.html", $vars );
