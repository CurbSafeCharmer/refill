<?php
/*
	This is the default configuation of Reflinks.

	Please don't edit this file, unless you want to change the 
	default configuations and submit it to the code repository.

	To override these configuations, create a file named "config.php"
*/

// The useragent used when fetching web pages and accessing MediaWiki API
$config['useragent'] = "Reflinks/0.1 (by Zhaofeng Li: https://en.wikipedia.org/wiki/User:Zhaofeng_Li)";

// The name of the wiki
$config['wiki']['name'] = "English Wikipedia";

// The URL to the MediaWiki API
$config['wiki']['api'] = "https://en.wikipedia.org/w/api.php";

// The URL to the index.php of the wiki, used to generate a link to the submit page
$config['wiki']['indexphp'] = "https://en.wikipedia.org/w/index.php";

// Stuff to insert into the end of a cite template
$config['citeextra'] = "";

// Default edit summary for the generated edit
// Use %numfixed% to show how many references are fixed, and %numskipped% for skipped.
$config['summary'] = "Filled in %numfixed% bare reference(s) with [[User:Zhaofeng Li/Reflinks]]";

// Host blacklist, they are regex fragments which have /^ and $/ added to them at runtime
$config['hostblacklist'] = array(
	"localhost",
	"\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}",
	".+\.local",
);

// Enable spam blacklist
$config['spam']['enable'] = false;

// A path to the /local/ blacklist file, used by Extension:SpamBlacklist on MediaWiki.
$config['spam']['file'] = __DIR__ . "/blacklist";

// The actual array for blacklist regexes, for reference only. You should not set this one manually.
$config['spam']['blacklist'] = array();

// Path to Cacycle's wDiff script. For easy offline development, download a copy to scripts/diff.js which is ignored by git.
$config['wdiff'] = "https://en.wikipedia.org/w/index.php?title=User:Cacycle/diff.js&action=raw&ctype=text/javascript";

// Whether to fall back to file_get_contents() when cURL has failed
$config['curlfallback'] = false;

date_default_timezone_set( "UTC" );

@include __DIR__ . "/config.php";
