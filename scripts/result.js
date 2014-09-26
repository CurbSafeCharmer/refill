function initDiff() {
	$( ".Differences" ).hide();
	$( "#wdiff" ).html( "<div id='diffcontent'></div>" );
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
