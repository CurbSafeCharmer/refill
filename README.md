# Reflinks
A quick-and-dirty rewrite of Dispenser's Reflinks. A live version is running on [WMF Labs](https://tools.wmflabs.org/fengtools/reflinks/).
There is also an [experimental version](https://tools.wmflabs.org/fengtools/reflinkstest/), automatically pulled from the latest commit.

## Setting up
Setting up your Reflinks instance is fairly simple. First, clone this repo with:
```sh
git clone https://github.com/zhaofengli/reflinks.git
```

The complete source code of Reflinks is now cloned in the `reflinks` directory. `cd` into it, and:
```sh
git submodule init
git submodule update
```
This will fetch the required libraries for you. That's it, you now have a working copy of Reflinks!

### Configuring
After setting up Reflinks, you may wish to point it at your local wiki for testing. To do so, create `includes/config.php` and insert:
```php
// The name of the wiki
$config['wiki']['name'] = "My Awesome Wiki";

// The URL to the MediaWiki API
$config['wiki']['api'] = "http://localhost/mediawiki/api.php";

// The URL to the index.php of the wiki, used to generate a link to the submit page
$config['wiki']['indexphp'] = "http://localhost/mediawiki/index.php";
```

For some simple customizations, use:
```php
// Default edit summary for the generated edit
// Use %numfixed% to show how many references are fixed, and %numskipped% for skipped.
$config['summary'] = "Filled in %numfixed% bare reference(s) with Reflinks";
```

You can also add a banner to notify users about updates. Create `includes/banner.php` and add the banner code. This file is ignored by git.
There's a CSS class named `banner` for banner styling.

There are more configuations available, please check out `includes/config.default.php` for details.

### Making it local
By default, the tool uses the [wDiff library](https://en.wikipedia.org/w/index.php?title=User:Cacycle/diff.js&action=raw&ctype=text/javascript) from Cacycle's user page on English Wikipedia.
For easy offline development, you may want to download a copy of the script from the above link and store it in `scripts/diff.js`. This file is ignored by git.
Beware though, you won't get updates of wDiff automatically in this way.
To enable the local version, insert this into `includes/config.php`:
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
This program incorporates code from [php-diff](https://github.com/chrisboulton/php-diff) in the form of a git submodule. Please refer to `README.md` in its repo for licensing information.

This repository includes a copy of [jQuery](http://jquery.com), licensed under the [MIT License](http://jquery.org/license/).

This program uses [wDiff](https://en.wikipedia.org/wiki/User:Cacycle/diff) by [Cacycle](https://en.wikipedia.org/wiki/User:Cacycle), released into public domain.
