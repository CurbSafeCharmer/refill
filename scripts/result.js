function initDiff() {
	$( ".Differences" ).hide();
	$( "#wdiff" ).html( "<small>Colours: <span class='wDiffInsert'>Blue</span> = Added; <span class='wDiffDelete'>Orange</span> = Removed</small><div id='diffcontent'></div>" );
	$( "#wikitext-new" ).keyup( function() {
		updateDiff();
	} );
	updateDiff();
}

function updateDiff() {
	var oldText = $( "#wikitext-old" ).val();
	var newText = $( "#wikitext-new" ).val();
	var diff = wDiff.Diff( oldText, newText );
	$( "#diffcontent" ).html( diff );
}
$( document ).ready( initDiff() );
