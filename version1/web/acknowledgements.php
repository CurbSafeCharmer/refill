<?php
require_once __DIR__ . "/../src/bootstrap.php";
$metadata = $I18N->rawMsg(
		$I18N->getDomain(),
		$I18N->getLang(),
		"@metadata"
	);
$translators = array();
if ( isset( $metadata['refill-authors'] ) ) {
	// reFill-specific list which includes the authors' names and
	// custom URLs to their profiles
	$translators = $metadata['refill-authors'];
} else if ( isset( $metadata['authors'] ) ) {
	// standard Intuition author list
	foreach ( $metadata['authors'] as $author ) {
		$translators[] = array(
			"name" => $author
		);
	}
}
echo $twig->render( "acknowledgements.html", array(
	"translators" => $translators
) );

