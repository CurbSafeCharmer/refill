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
	
	Set rlServer to the URL of the tool, and rlWiki to the
	wiki identifier.
*/

/*global rlServer, rlWiki, mw, $ */
/*jshint multistr: true */

// ==UserScript==
// @name        Reflinks gadget
// @description Adds a toolbox link to the Reflinks tool
// @namespace   https://en.wikipedia.org/wiki/User:Zhaofeng_Li
// @include     *://en.wikipedia.org/*
// @version     10
// @grant       none
// ==/UserScript==

function ReflinksGadget() {
	this.server = typeof rlServer !== 'undefined' ? rlServer
	            : "https://tools.wmflabs.org/fengtools/reflinks";
	this.wiki = typeof rlWiki !== 'undefined' ? rlWiki
	          : null;

	this.portletLink = "";
	this.optionsLink = "";

	this.isWatching = function() {
		// Let's use a little hack to determine whether the current page is watched or not
		if ( $( "#ca-unwatch" ).length !== 0 ) {
			return true;
		} else {
			return false;
		}
	}
	
	this.getSubmitUrl = function( defaults ) {
		var pagename = mw.config.get( "wgPageName" );
		var url = this.server + "/result.php?page=" + encodeURIComponent( pagename );
		if ( defaults ) {
			url += "&defaults=y";
		}
		if ( !this.isWatching() ) {
			url += "&nowatch=y";
		}
		if ( this.wiki !== null ) {
			url += "&wiki=" + encodeURIComponent( this.wiki );
		}
		return url;
	}
	
	this.setUpForm = function() {
		this.tearDownForm();
		$( "#mw-content-text" ).prepend( "\
	<div id='reflinks' style='border: 1px solid #ccc; border-radius: 2px; margin: 5px; padding: 0 10px 10px 10px;'>\
		<h2>Options</h1>\
		<form id='reflinks-form' method='post'>\
			<div id='reflinks-options'>Loading options...</div>\
			<input name='method-wiki' type='submit' value='Fix page'/>\
			<a href='" + this.server + "' style='color: #555;'>Tool homepage</a>\
		</form>\
	</div>" );
		$( "#reflinks-form" ).attr( "action", this.getSubmitUrl( false ) );
		if ( !this.isWatching() ) {
			var nowatch = $( "<input>" ).attr( "name", "nowatch" ).attr( "type", "hidden" ).val( "y" );
			$( "#reflinks-form" ).append( nowatch );
		}
		$( "html, body" ).animate( {
			scrollTop: $( "#reflinks" ).offset().top - 10
		}, 250 );
	}
	
	this.loadRemoteOptions = function() {
		$.getJSON( this.server + "/api.php?action=optionsform&callback=?", function ( json ) {
			$( "#reflinks-options" ).html( json.form );
		} );
	}
	
	this.tearDownForm = function() {
		$( "#reflinks" ).remove();
	}
	
	this.init = function() {
		this.portletLink = mw.util.addPortletLink( "p-tb", this.getSubmitUrl( true ), "Reflinks" );
		var obj = this;
		this.optionsLink = $( "<a>" ).attr( "href", "#" ).text( "(options)" ).click( function() {
			obj.setUpForm();
			obj.loadRemoteOptions();
		} );
		$( this.portletLink ).append( $( "<sup>").html( this.optionsLink ) );
	}
}

$( document ).ready( function() {
	var rlGadget = new ReflinksGadget();
	rlGadget.init();
} );
