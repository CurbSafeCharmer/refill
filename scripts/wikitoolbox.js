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

/*
	This script is intended to be included on-wiki!
	
	This script depends on jQuery and MediaWiki environment.
	Please change the metadata and configuations below to suit your needs.
*/

/*global wgPageName, rlServer, rlWiki, mw */
/*jshint multistr: true */

// ==UserScript==
// @name        Reflinks
// @description Adds a toolbox link to the Reflinks tool
// @namespace   https://en.wikipedia.org/wiki/User:Zhaofeng_Li
// @include     *://en.wikipedia.org/*
// @version     4
// @grant       none
// ==/UserScript==

if ( typeof rlServer === "undefined" ) {
	var rlServer = "https://tools.wmflabs.org/fengtools/reflinks";
}

var rlOptionLink = "", rlPortlet = "";

function rlIsWatching() {
	// Let's use a little hack to determine whether the current page is watched or not
	if ( $( "#ca-unwatch" ).length !== 0 ) {
		return true;
	} else {
		return false;
	}
}

function rlGetSubmitUrl( defaults ) {
	var url = rlServer + "/result.php?page=" + encodeURIComponent( wgPageName );
	if ( defaults ) {
		url += "&defaults=y";
	}
	if ( !rlIsWatching() ) {
		url += "&nowatch=y";
	}
	if ( typeof rlWiki !== "undefined" ) {
		url += "&wiki=" + encodeURIComponent( rlWiki );
	}
	return url;
}

function rlSetUpForm() {
	rlTearDownForm();
	$( "#mw-content-text" ).prepend( "\
<div id='reflinks' style='border: 1px solid #ccc; border-radius: 2px; margin: 5px; padding: 0 10px 10px 10px;'>\
	<h2>Options</h1>\
	<form id='reflinks-form' method='post' action='" + rlGetSubmitUrl( false ) + "'>\
		<div id='reflinks-options'>Loading options...</div>\
		<input name='method-wiki' type='submit' value='Fix page'/>\
		<a href='" + rlServer + "' style='color: #555;'>Tool homepage</a>\
	</form>\
</div>" );
	if ( !rlIsWatching() ) {
		var nowatch = $( "<input>" ).name( "nowatch" ).type( "hidden" ).value( "y" );
		$( "#reflinks-form" ).append( nowatch );
	}
	$( "html, body" ).animate( {
		scrollTop: $( "#reflinks" ).offset().top - 10
	}, 250 );
}

function rlLoadRemoteOptions() {
	$.getJSON( rlServer + "/scripts/toolboxform.php?callback=?", function ( json ) {
		$( "#reflinks-options" ).html( json.form );
	} );
}

function rlTearDownForm() {
	$( "#reflinks" ).remove();
}

$( document ).ready( function() {
	rlPortlet = mw.util.addPortletLink( "p-tb", rlGetSubmitUrl( true ), "Reflinks" );
	rlOptionLink = $( "<a>" ).attr( "href", "#" ).html( "(options)" ).click( function() {
		rlSetUpForm();
		rlLoadRemoteOptions();
	} );
	$( rlPortlet ).append( $( "<sup>").html( rlOptionLink ) );
} );
