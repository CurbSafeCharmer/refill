<?php
// Common banners
// TODO: I18N

// IE warning
if ( false !== strpos( $_SERVER['HTTP_USER_AGENT'], "MSIE" ) || false !== strpos( $_SERVER['HTTP_USER_AGENT'], "Edge/12" ) || !empty( $_GET['forceiewarning'] ) ) {
	$banners[] = "<div class='alert alert-danger'>Use Internet Explorer or Microsoft Edge at your own risk. Due to the XSS filter employed on those browsers, you may experience strange character replacements.</div>";
}

$banners[] = "<div class='alert alert-info'>"
		. "<h4>reFill has new maintainers!</h4>"
		. "<p>Led by <a href='https://en.wikipedia.org/wiki/User:Curb_Safe_Charmer'>User:Curb Safe Charmer</a> and <a href='https://en.wikipedia.org/wiki/User:TheresNoTime'>User:TheresNoTime</a>, a number of new maintainers are now working on reFill.</p>"
		. "<p>Please bear with us as we work to update the tool, and feel free to <a href='https://phabricator.wikimedia.org/tag/tool-refill/'>report any bugs</a> you find.</p>"
		. "</div>";

