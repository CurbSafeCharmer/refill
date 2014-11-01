<?php
/*
	This is the default configuation of Reflinks.

	Please don't edit this file, unless you want to change the 
	default configuations and submit it to the code repository.

	To override these configuations, create a file named "config.php" under "/config"
*/

use Reflinks\UserOptionsProvider;

// The useragent used when fetching web pages and accessing MediaWiki API
$config['useragent'] = "Reflinks/1.0 (by Zhaofeng Li: https://en.wikipedia.org/wiki/User:Zhaofeng_Li/Reflinks )";

// Wiki map
$config['wikis'] = array(
	"wikipedia" => array(
		"identifiers" => array(
			"en", "simple", "zh",
		),
		"api" => "https://%id%.wikipedia.org/w/api.php",
		"indexphp" => "https://%id%.wikipedia.org/w/index.php",
	),
);

// Metadata parser chain
$config['parserchain'] = array(
	"TitleMetadataParser",
	"OpenGraphMetadataParser",
	"SchemaOrgMetadataParser",
	"MetaTagMetadataParser",
	"FixerMetadataParser",
);

// Stuff to insert into the end of a cite template
$config['citeextra'] = "";

// Default edit summary for the generated edit
// Use %numfixed% to show how many references are fixed, and %numskipped% for skipped.
$config['summary'] = "Filled in %numfixed% bare reference(s) with [[User:Zhaofeng Li/Reflinks]]";

// Whether to enable the filter or not
$config['spam']['enable'] = false;

// A path to the /local/ blacklist file, used by Extension:SpamBlacklist on MediaWiki.
$config['spam']['file'] = __DIR__ . "/blacklist";

// The actual array for blacklist regexes, for reference only. You should not set this one manually.
$config['spam']['blacklist'] = array();

// Path to Cacycle's wDiff script. For easy offline development, download a copy to scripts/diff.js which is ignored by git.
$config['wdiff'] = "https://en.wikipedia.org/w/index.php?title=User:Cacycle/diff.js&action=raw&ctype=text/javascript";

// User-settable options
$config['options'] = array(
	"page" => array(
		"type" => UserOptionsProvider::TYPE_SPECIAL,
		"name" => "Page name",
	),
	"text" => array(
		"type" => UserOptionsProvider::TYPE_SPECIAL,
		"name" => "Raw wikitext",
	),
	// TODO: Implement this correctly
	"wiki" => array(
		"type" => UserOptionsProvider::TYPE_SPECIAL,
		"name" => "Wiki",
	),
	"plainlink" => array(
		"type" => UserOptionsProvider::TYPE_CHECKBOX,
		"name" => "Use plain formatting instead of <code>{{cite web}}</code>",
		"description" => "If selected, bare references will be expanded without using <code>{{cite web}}</code>. This is discouraged since cite templates enable easy parsing by programs.",
		"advanced" => false,
		"default" => false,
	),
	"nofixuplain" => array(
		"type" => UserOptionsProvider::TYPE_CHECKBOX,
		"name" => "Do not expand uncaptioned plain links (surrounded with [ ])",
		"description" => "If selected, references with a URL surrounded by square brackets only will be skipped. This is for debugging only and should not be used.",
		"advanced" => true,
		"default" => false,
	),
	"nofixcplain" => array(
		"type" => UserOptionsProvider::TYPE_CHECKBOX,
		"name" => "Do not expand references with a captioned external link only",
		"description" => "If selected, references consisting of a captioned external link only will be expanded.",
		"advanced" => false,
		"default" => false,
	),
	"nouseoldcaption" => array(
		"type" => UserOptionsProvider::TYPE_CHECKBOX,
		"name" => "Do not reuse existing captions from original references",
		"If selected, the tool will use the page title fetched from the server as the caption, overwriting the existing ones.",
		"advanced" => false,
		"default" => false,
	),
	"noremovetag" => array(
		"type" => UserOptionsProvider::TYPE_CHECKBOX,
		"name" => "Do not remove link rot tags",
		"description" => "If selected, link rot tags will be kept even if no reference is skipped unexpectedly during the process.",
		"advanced" => false,
		"default" => false,
	),
	"nofixutemplate" => array(
		"type" => UserOptionsProvider::TYPE_CHECKBOX,
		"name" => "Do not fix <code>{{cite web}}</code> templates with a URL only",
		"description" => "If selected, Reflinks will skip any reference consisting of a URL only. This is for debugging only and should not be used.",
		"advanced" => true,
		"default" => false,
	),
	"nowatch" => array(
		"type" => UserOptionsProvider::TYPE_CHECKBOX, // This is automatically set by wikitoolbox.js
		"name" => "Do not watch the page",
		"description" => "If selected, the 'Watch this page' checkbox on the on-wiki editing interface will be unticked by default.",
		"advanced" => true,
		"default" => false,
	),
	"addblankmetadata" => array(
		"type" => UserOptionsProvider::TYPE_CHECKBOX,
		"name" => "Add blank metadata fields when the information is unavailable",
		"advanced" => false,
		"default" => false,
	),
	"noaccessdate" => array(
		"type" => UserOptionsProvider::TYPE_CHECKBOX,
		"name" => "Do not add access dates",
		"description" => "If selected, dates of access will be omitted in the result.",
		"advanced" => false,
		"default" => true,
	),
	"usedomainaswork" => array(
		"type" => UserOptionsProvider::TYPE_CHECKBOX,
		"name" => "Use the base domain name as work when this information cannot be parsed",
		"advanced" => false,
		"default" => false,
	),
);

date_default_timezone_set( "UTC" );

@include __DIR__ . "/../config/config.php";
