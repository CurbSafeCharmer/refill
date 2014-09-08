<?php
// The useragent used when fetching web pages and accessing MediaWiki API
$config['useragent'] = "Reflinks/0.1 (by Zhaofeng Li: https://en.wikipedia.org/wiki/User:Zhaofeng_Li)";

// The URL to the MediaWiki API
$config['api'] = "https://en.wikipedia.org/w/api.php";

// The URL to the index.php of the wiki, used to generate a link to the submit page
$config['indexphp'] = "https://en.wikipedia.org/w/index.php";

// Stuff to insert into the end of a cite template
$config['citeextra'] = "";

// Default edit summary for the generated edit
$config['summary'] = "Filled in bare references with [[User:Zhaofeng Li/Reflinks]]";

// Options for php-diff, see its documentations for more info
$config['diffconfig'] = array();

date_default_timezone_set( "UTC" );
