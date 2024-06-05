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

/*global WikEdDiff */

function initDiff() {
	$( "#wikitext-new" ).keyup( function() {
		updateDiff();
	} );
	$( "#wdiff" ).show();
	updateDiff();
}

function updateDiff() {
	var wikEdDiff = new WikEdDiff();
	var oldText = $( "#wikitext-old" ).val();
	var newText = $( "#wikitext-new" ).val();
	var diff = wikEdDiff.diff( oldText, newText );
	$( "#diffcontent" ).html( diff );
}

function saveAndReturn( e ) {
	$( "#form-wikitext" ).attr( "target", "_blank" );
	$( "#form-wikitext" ).submit( function() {
		setTimeout( function() {
			window.location.href = "index.php";
		}, 1 );
	} );
}

$( document ).ready( function() {
	initDiff()
	$( "#btn-saveandreturn" ).click( saveAndReturn );
} );
