<?php
// Common configuations

$config['wikiprovider'] = "WikimediaWikiProvider";

// Supported wikis
$config['wm-defaultproject'] = "wikipedia";
$config['wm-projects'] = array(
	'wikipedia' => array(
		'default' => 'en',
		'api' => 'https://%id%.wikipedia.org/w/api.php',
		'indexphp' => 'https://%id%.wikipedia.org/w/index.php',
		'wikis' => array(
			'en', 'sv', 'nl', 'de', 'fr', 'war', 'ceb', 'ru', 'it', 'es', 'vi', 'pl', 'simple', 'zh', 'ja', 'ro', 'bn', 'pt', 'af', 'el', 'ta', 'id', 'sco', 'ar', 'cs',
			'simple' => array( // override language code
				'language' => 'en'
			)
		)
	),
	'wikimedia' => array(
		'default' => 'meta',
		'language' => 'en', // the default language for the project
		'api' => 'https://%id%.wikimedia.org/w/api.php',
		'indexphp' => 'https://%id%.wikimedia.org/w/index.php',
		'wikis' => array( 'meta', 'commons' )
	)
);

// Link handlers
$config['linkhandlers'] = array(
	array(
		"regex" => "/^https?\:\/\/archive\.(is|fo|li|today)/",
		"handler" => "StandaloneLinkHandler"
	),
	array(
		"regex" => "/^http\:\/\/www\.nytimes\.com/",
		"handler" => "NewYorkTimesLinkHandler"
	),
	"CitoidLinkHandler",
);
$config['parserchain'] = array(
	"CitoidMetadataParser",
	"ArchiveIsUrlFixerMetadataParser",
	"NewYorkTimesMetadataParser",
);

// Maximum execution time, in seconds
$config['maxtime'] = 600;

// Insert the domain name as |work= by default
// (Disabled per suggestion at https://github.com/zhaofengli/refill/issues/12 - Let's be more conservative, alright?)
// $config['options']['usedomainaswork']['default'] = true;

// Blacklist WebCitation
$config['blacklist'][] = "\bwebcitation.org\b";

// Blacklist ProQuest
$config['blacklist'][] = "\bproquest.com\b";

// Banners
$banners = array();

// Footer link to Tool Labs
function rlFooter() {
	global $I18N;
	return '<li><a href="https://wikitech.wikimedia.org/wiki/Portal:Toolforge"><img style="height: 20px; margin-right: 5px;" src="https://refill.toolforge.org/favicon.ico"/>' . $I18N->msg( "wmflabs-poweredby" ) . '</a></li>';
}

function rlBanner() {
	global $banners, $bannerCallbacks;
	$banner = implode( "", $banners );
	//foreach ( $bannerCallbacks as $callback ) {
	//	$banner .= call_user_func( $callback );
	//}
	return $banner;
	return implode( "", $banners );
}

function rlBannerCallback( $callback ) {
	global $bannerCallbacks;
	$bannerCallbacks[] = $callback;
}

require_once __DIR__ . "/banners.php";
