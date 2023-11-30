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

// HACK
$options = array_merge($_POST, $_GET);

if ( !$options['wiki'] || $options['wiki'] == 'en' ) {
       header( 'Location: /ng/result.php?' . http_build_query($options) );
       exit;
}

require_once __DIR__ . "/../src/bootstrap.php";

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
$vars['unfinished'] = $result['unfinished'];

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
if ( $app->options->get( "nowatch" ) ) {
	$vars['nowatch'] = true;
}
if ( $app->options->get( "noaccessdate" ) ) {
	$vars['noaccessdate'] = true;
}

foreach ( $result['log']['skipped'] as &$citation ) {
	$citation['humanreason'] = $app->getSkippedReason( $citation['reason'] );
	if ( isset( $citation['description'] ) ) {
		$citation['humanreason'] .= " (" . $citation['description'] . ")";
	}
}
$vars['skipped'] = $result['log']['skipped'];

echo $twig->render( "result.html", $vars );
