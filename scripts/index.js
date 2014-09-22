/*
*/
function checkForm( element, event ) {
	if ( $( element ).val().length == 0 ) {
		$( element ).addClass( "inputerror" );
		event.preventDefault();
	} else {
		$( element ).removeClass( "inputerror" );
	}
}

$( document ).ready( function() {
	$( "#form-wiki" ).submit( function( event ) {
		checkForm( $( "input[name=page]" ), event );
	} );
	$( "#form-wikitext" ).submit( function( event ) {
		checkForm( $( "textarea[name=text]" ), event );
	} );
} );
