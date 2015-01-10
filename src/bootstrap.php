<?php
/*
	Reflinks bootstrap script
*/
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/config.default.php";
require_once $config['i18n']['intuition'];

$I18Nopts = array(
	'domain' => $config['i18n']['domain'],
);
$I18N = new Intuition( $I18Nopts );

$twigLoader = new Twig_Loader_Filesystem( __DIR__ . "/../style/templates" );
$twig = new Twig_Environment( $twigLoader );
$twigBanner = new Twig_SimpleFunction( "banner", function() {
	if ( function_exists( "rlBanner" ) ) {
		return rlBanner();
	}
} );
$twigFooter = new Twig_SimpleFunction( "footer", function() {
	if ( function_exists( "rlFooter" ) ) {
		return rlFooter();
	}
} );
$twigI18n = new Twig_SimpleFunction( "msg", function( $key /* $vars ,... */ ) {
	global $I18N;
	$vars = func_get_args();
	array_shift( $vars );
	return $I18N->msg( $key, array(
		"variables" => $vars,
	) );
} );
$twig->addFunction( $twigBanner );
$twig->addFunction( $twigFooter );
$twig->addFunction( $twigI18n );


