<?php
/*
	Copyright (c) 2014-2015, Zhaofeng Li
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

/*
	Reflinks bootstrap script
*/
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/config.default.php";
// require_once $config['i18n']['intuition'];

// Intuition I18N
$I18N = new Intuition( $config['i18n']['domain'] );
$I18N->registerDomain( $config['i18n']['domain'], __DIR__ . "/../messages" );

// Twig templating engine
$twigLoader = new Twig_Loader_Filesystem( __DIR__ . "/../templates" );
$twig = new Twig_Environment( $twigLoader );
$twig->addFunction( new Twig_SimpleFunction( "banner", function() {
	if ( function_exists( "rlBanner" ) ) {
		return rlBanner();
	}
} ) );
$twig->addFunction( new Twig_SimpleFunction( "footer", function() {
	if ( function_exists( "rlFooter" ) ) {
		return rlFooter();
	}
} ) );
$twig->addFunction( new Twig_SimpleFunction( "msg", function( $key /* $vars ,... */ ) {
	global $I18N;
	$vars = func_get_args();
	array_shift( $vars );
	return $I18N->msg( $key, array(
		"variables" => $vars,
	) );
} ) );
$twig->addFunction( new Twig_SimpleFunction( "getI18nDashboard", function() {
	global $I18N;
	return $I18N->getDashboardReturnToUrl();
} ) );
