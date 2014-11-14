# Reflinks [![Build Status](https://travis-ci.org/zhaofengli/reflinks.svg?branch=master)](https://travis-ci.org/zhaofengli/reflinks) [![Code Climate](https://codeclimate.com/github/zhaofengli/reflinks/badges/gpa.svg)](https://codeclimate.com/github/zhaofengli/reflinks)
[Reflinks](https://en.wikipedia.org/wiki/User:Zhaofeng_Li/Reflinks) is a tool for Wikipedia that adds information (page title, work/website, author and publication date, etc.) to [bare references](https://en.wikipedia.org/wiki/WP:BURL) semi-automatically. This is a rewrite of the closed-source original tool by [Dispenser](https://en.wikipedia.org/wiki/User:Dispenser).
A live version is running on [WMF Labs](https://tools.wmflabs.org/fengtools/reflinks/), and there is also an [experimental version](https://tools.wmflabs.org/fengtools/reflinkstest/), automatically pulled from the latest commit.

## NOTICE
The program is now revamped to adapt an object-oriented structure.
Old configuations may not work. See the `Configuring` secton for details.

## Setting up
Setting up your Reflinks instance is fairly simple. You will need [Composer](http://getcomposer.org) to install the dependencies. First, clone this repo with:
```sh
git clone https://github.com/zhaofengli/reflinks.git
```

The complete source code of Reflinks is now cloned in the `reflinks` directory. `cd` into it, and:
```sh
php composer.phar install
```
This will fetch the required libraries for you. That's it, you now have a working copy of Reflinks!

### Configuring
After setting up Reflinks, you may wish to add your local wiki for testing. To do so, create `config/config.php` and insert:
```php
<?php
$config['wikis']['mywiki'] = array(
	"identifiers" => array( // A list of wikis of this type
		"wmflike", "baremw",
	),
	"api" => "http://localhost/%id%/api.php",
	"indexphp" => "http://localhost/%id%/index.php",
);
```
Now `wmflike` and `baremw` should appear on the main page, pointing at `http://localhost/wmflike/` and `http://localhost/baremw` respectively.

For some simple customizations, use:
```php
// Default edit summary for the generated edit
// Use %numfixed% to show how many references are fixed, and %numskipped% for skipped.
$config['summary'] = "Filled in %numfixed% bare reference(s) with Reflinks";
```

You can also add a banner to notify users about updates. Create `config/banner.php` and add the banner code. This file is ignored by git.
There's a CSS class named `banner` for banner styling.

There are more configuations available, please check out `src/config.default.php` for details.

### Making it local
By default, the tool uses the [wDiff library](https://en.wikipedia.org/w/index.php?title=User:Cacycle/diff.js&action=raw&ctype=text/javascript) from Cacycle's user page on English Wikipedia.
For easy offline development, you may want to download a copy of the script from the above link and store it in `scripts/diff.js`. This file is ignored by git.
Beware though, you won't get updates of wDiff automatically in this way.
To enable the local version, insert this into `config/config.php`:
```php
$config['wdiff'] = "scripts/diff.js";
```

## Reporting bugs
If you have found a bug, please [report it on Wikipedia](https://en.wikipedia.org/wiki/User_talk:Zhaofeng_Li) or [create an issue on GitHub](https://github.com/zhaofengli/reflinks/issues).

## Contributing
Patches are always welcome! To contribute, simply create a fork of the repo, make your changes and submit a pull request. Thank you for your contributions!

## Licensing
Reflinks is licensed under the BSD 2-Clause License. See `LICENSE` for details.

### External libraries
This repository includes a copy of [jQuery](http://jquery.com), licensed under the [MIT License](http://jquery.org/license/).

This program uses [wDiff](https://en.wikipedia.org/wiki/User:Cacycle/diff) by [Cacycle](https://en.wikipedia.org/wiki/User:Cacycle), released into public domain.

Composer dependencies of this program:
- [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser/)
- [HTML5-PHP](https://github.com/Masterminds/html5-php)
