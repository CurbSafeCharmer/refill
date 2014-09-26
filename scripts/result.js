function initDiff() {
	$( ".Differences" ).hide();
	$( "#wdiff" ).html( "<div id='diffcontent'></div><small>Colours: <span class='wDiffInsert'>Blue</span> = Added; <span class='wDiffDelete'>Orange</span> = Removed</small>" );
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
