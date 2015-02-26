# reFill [![Build Status](https://travis-ci.org/zhaofengli/reflinks.svg?branch=master)](https://travis-ci.org/zhaofengli/reflinks) [![Code Climate](https://codeclimate.com/github/zhaofengli/reflinks/badges/gpa.svg)](https://codeclimate.com/github/zhaofengli/reflinks) [![Coverage Status](https://img.shields.io/coveralls/zhaofengli/reflinks.svg)](https://coveralls.io/r/zhaofengli/reflinks?branch=master)
*[reFill](https://en.wikipedia.org/wiki/User:Zhaofeng_Li/reFill)* (formerly *Reflinks*) is a tool for Wikipedia that adds information (page title, work/website, author and publication date, etc.) to [bare references](https://en.wikipedia.org/wiki/WP:BURL) semi-automatically. This is a rewrite of the closed-source original tool by [Dispenser](https://en.wikipedia.org/wiki/User:Dispenser).
A live version is running on [WMF Labs](https://tools.wmflabs.org/fengtools/reflinks/), and there is also an [experimental version](https://tools.wmflabs.org/fengtools/reflinkstest/), automatically pulled from the latest commit.

For backward compatibility, the source code of the tool still refers to the tool as "Reflinks". This is intentional.

## Setting up
Setting up your reFill instance is fairly simple. You will need [Composer](http://getcomposer.org) and [Bower](http://bower.io) to install the dependencies. First, clone this repo with:
```sh
git clone --recursive https://github.com/zhaofengli/refill.git
```

The complete source code of reFill is now cloned in the `reflinks` directory. `cd` into it, and:
```sh
php composer.phar install
bower install
```
This will fetch the required libraries for you. That's it, you now have a working copy of reFill!

### Configuring
After setting up reFill, you may wish to add your local wiki for testing. To do so, create `config/config.php` and insert:
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
$config['summary'] = "Filled in %numfixed% bare reference(s) with reFill";
```

You can also add a banner to notify users about updates, as well as extend the default footer. Create `rlBanner()` and `rlFooter()` and return the content in them.

There are more configuations available, please check out `src/config.default.php` for details.

### Making it local
By default, the tool uses the [wDiff library](https://en.wikipedia.org/w/index.php?title=User:Cacycle/diff.js&action=raw&ctype=text/javascript) from Cacycle's user page on English Wikipedia.
For easy offline development, you may want to download a copy of the script from the above link and store it in `scripts/diff.js`. This file is ignored by git.
Beware though, you won't get updates of wDiff automatically in this way.
To enable the local version, insert this into `config/config.php`:
```php
$config['wdiff'] = "scripts/diff.js";
```
## Unit tests
The program includes an incomplete set of unit tests under `tests`, which can be ran with PHPUnit. Please help cover untested code by adding new tests.

## Reporting bugs
If you have found a bug, please [report it on Wikipedia](https://en.wikipedia.org/wiki/User_talk:Zhaofeng_Li) or [create an issue on GitHub](https://github.com/zhaofengli/reflinks/issues).

## Contributing
Patches are always welcome! To contribute, simply create a fork of the repo, make your changes and submit a pull request. Thank you for your contributions!

## Licensing
reFill is licensed under the BSD 2-Clause License. See `LICENSE` for details.

### External libraries
This program uses [wDiff](https://en.wikipedia.org/wiki/User:Cacycle/diff) by [Cacycle](https://en.wikipedia.org/wiki/User:Cacycle), released into public domain.

The Bootstrap theme used is [Yeti](http://bootswatch.com/yeti/).

[Open Sans](http://www.google.com/fonts/specimen/Open+Sans) is a font by [Steve Matteson](https://profiles.google.com/107777320916704234605/about).

Composer dependencies of this program:
- [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser/)
- [HTML5-PHP](https://github.com/Masterminds/html5-php)
- [Twig](http://twig.sensiolabs.org/)

Bower dependencies of this program:
- [jQuery](http://jquery.com)
- [Chosen](http://harvesthq.github.io/chosen/)
- [Bootstrap](http://getbootstrap.com/)
