<?php
// Configuations for the test instance
require_once __DIR__ . "/common.php";

// Report all errors
error_reporting( E_ALL );

// Experimental wikis
$config['wm-projects']['wikipedia']['wikis'] = array_merge(
	$config['wm-projects']['wikipedia']['wikis'],
	array( "bh", "te", "kn", "bn", "hi", "gu", "gom", "ml", "or", "pa", "ta", "tcy", "sr" )
);

// Commit ID in default edit summary
$config['summaryextra'] = " (" . substr( file_get_contents( ".git/refs/heads/master" ), 0, 7 ) . ")";

// Test version banners
rlBannerCallback( function() {
	global $I18N, $app;
	return "<div class='alert alert-info'>"
	        . $I18N->msg( "wmflabs-thankyoutest", array( "variables" => array( $I18N->msg( "appname" ) ) ) ) . "<br>"
	        . $I18N->msg( "wmflabs-latestcommit", array( "variables" => array( htmlspecialchars( `git log -1 --oneline` ) ) ) )
	        . "</div>";
} );
