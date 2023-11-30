# reFill [![Build Status](https://img.shields.io/travis/zhaofengli/refill.svg)](https://travis-ci.org/zhaofengli/refill) [![Coverage Status](https://img.shields.io/codecov/c/github/zhaofengli/refill.svg)](https://codecov.io/github/zhaofengli/refill/)

**[reFill](https://en.wikipedia.org/wiki/User:Zhaofeng_Li/reFill)** (formerly **Reflinks**) is a tool for Wikipedia that adds information (page title, work/website, author and publication date, etc.) to [bare references](https://en.wikipedia.org/wiki/WP:BURL) semi-automatically. This is a rewrite of the closed-source original tool by [Dispenser](https://en.wikipedia.org/wiki/User:Dispenser).
A live version is running on [WMF Labs](https://tools.wmflabs.org/fengtools/reflinks/), and there is also an [experimental version](https://tools.wmflabs.org/fengtools/reflinkstest/), automatically pulled from the latest commit.

For backwards compatibility, the source code of the tool still refers to itself as "Reflinks". This is intentional.

## Setting up
Setting up your reFill instance is fairly simple. You will need [Composer](http://getcomposer.org) to install the dependencies. First, clone this repo with:
```sh
git clone --recursive https://github.com/zhaofengli/refill.git
```

The complete source code of reFill will be cloned in the `refill` directory. `cd` into it, and:
```sh
php composer.phar install
```
This will fetch the required libraries for you. That's it, you now have a working copy of reFill! Fire up a server in `web` and start hacking!

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

You can also add a banner to notify users about updates, as well as to extend the default footer. Implement global functions `rlBanner()` and/or `rlFooter()` which return the HTML code of the banner/footer.

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
The program includes an incomplete set of unit tests under `tests`, which can be run with PHPUnit. Please help cover untested code by adding new tests.

## Tool Labs-specific configurations
The Tool Labs version uses its own set of configurations, which reside on [zhaofengli/refill-labsconf](https://github.com/zhaofengli/refill-labsconf). You can `require_once()` the `stable.php`, `test.php` or `citoid.php` in the repo from your `config.php` to replicate the Tool Labs configurations on your local instance.

## Reporting bugs
If you have found a bug, please [report it on Wikipedia](https://en.wikipedia.org/wiki/User_talk:Zhaofeng_Li/reFill) or [create an issue on GitHub](https://github.com/zhaofengli/refill/issues).

## Contributing
Patches are always welcome! To contribute, simply create a fork of the repo, make your changes and submit a pull request. Thank you for your contributions!

Localisation of the tool is powered by [Intuition](https://github.com/Krinkle/intuition) and handled on [translatewiki.net](https://translatewiki.net/wiki/Special:Translate?group=int-refill). To start translating the tool, please register at translatewiki.net and request to become a Translator. You can also submit your translations manually via GitHub pull requests or even [on-wiki](https://en.wikipedia.org/wiki/User_talk:Zhaofeng_Li/reFill).

## Licensing
reFill is licensed under the BSD 2-Clause License. See `LICENSE` for details.

### External libraries
This program uses [wDiff](https://en.wikipedia.org/wiki/User:Cacycle/diff) by [Cacycle](https://en.wikipedia.org/wiki/User:Cacycle), released into public domain.

The Bootstrap theme used is [Yeti](http://bootswatch.com/yeti/).

[Open Sans](http://www.google.com/fonts/specimen/Open+Sans) is a font by [Steve Matteson](https://profiles.google.com/107777320916704234605/about).

#### Composer dependencies
- [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser/)
- [HTML5-PHP](https://github.com/Masterminds/html5-php)
- [Twig](http://twig.sensiolabs.org/)
- [Intuition](https://github.com/Krinkle/intuition)
- [Date](https://github.com/jenssegers/date)

You may want to run `composer licenses` to list the licenses of all Composer dependencies.

#### Bower dependencies
- [jQuery](http://jquery.com)
- [Chosen](http://harvesthq.github.io/chosen/)
- [Bootstrap](http://getbootstrap.com/)

## External links
- [Citoid](https://www.mediawiki.org/wiki/Citoid)
- [schema.org](https://schema.org/)
- [The Open Graph protocol](http://ogp.me/)
- [Horizon Meta Tags](http://getstarted.sailthru.com/web/horizon-overview/horizon-meta-tags/)
- [Zhaofeng Li](https://zhaofeng.li)

