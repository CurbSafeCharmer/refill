# reFill 2

**reFill** fixes [bare URLs](https://en.wikipedia.org/wiki/Wikipedia:Bare_URLs) on Wikipedia articles, semi-automatically. It extracts bibliographical information from web pages referenced by bare URL citations, generates complete references and finally inserts them back onto the original page.

This README gives you all the details needed to set up a reFill instance for testing and development. If you only intend to use reFill, [the manual](https://en.wikipedia.org/wiki/WP:reFill) may be more helpful.

## Quick start

You will need to install:
- Python 3.8
- [Pipenv](https://github.com/pypa/pipenv)
- Node.js 14
- Redis (or any other broker [supported by Celery](http://docs.celeryproject.org/en/latest/getting-started/brokers/))

1. `git clone https://github.com/CurbSafeCharmer/refill`
1. `cd refill`
1. `make setup`
1. `make start`
1. Voila! reFill is now running on your machine.

## Overview

The tool consists of three parts:

- APIs: A set of APIs that allow the user to submit tasks and retrieve their results. Tasks may be initiated by a human user or a script.
- Workers: Long-running processes that complete tasks received through the broker, orchestrated by Celery.
- Web UI: A single-page web app powered by Vue.js. It's the reference implementation of an API consumer.

## Navigating the source

Most interesting stuff happens in `backend/refill`, where you can find the individual parsers that extract information from webpages.

- `backend`: APIs and worker
    - `app.py`: Flask-based APIs
    - `refill`
        - `dataparsers`: Metadata parsers
        - `formatters`: Wikicode generators
        - `transforms`: Wikicode transformations
            - `fillref.py`: Complete bare references
            - `fillexternal.py`: Complete bare external links
            - `mergeref.py`: Merge duplicate citations
        - `models`: Models
            - `citation.py`: Citation
            - `context.py`: Task context
        - `utils`: Utilities
- `web`: Web UI
    - `libs`: External libraries
        - `wdiff.js`: A modified version of wDiff by [User:Cacycle](https://en.wikipedia.org/wiki/User:Cacycle), with additional code to display reFill markers
    - `src`: Source code

## Hints

### Result expiration

By default, tasks on Celery [expire in a day](http://docs.celeryproject.org/en/latest/userguide/configuration.html#std:setting-result_expires). If you are using a database backend, be sure to have `celery beat` running in order to clear the old results. This is especially important if you are running a public instance.

## Contributing

Patches are always welcome! To contribute, simply create a fork of the repo, make your changes and submit a pull request. Your contributions are appreciated. It would be great to have some new maintainers!

Localization of the tool is powered by [Intuition](https://github.com/Krinkle/intuition) and handled on [translatewiki.net](https://translatewiki.net/wiki/Special:Translate?group=int-refill). To start translating the tool, please register at translatewiki.net and request to become a Translator. You can also submit your translations manually via GitHub pull requests or even [on-wiki](https://en.wikipedia.org/wiki/User_talk:Zhaofeng_Li/reFill).

## Licensing

reFill is licensed under the BSD 2-Clause License. See `LICENSE` for details.

### External libraries

This program uses [wDiff](https://en.wikipedia.org/wiki/User:Cacycle/diff) by [Cacycle](https://en.wikipedia.org/wiki/User:Cacycle), released into public domain.

Licenses of NPM and PyPI dependencies may be viewed using third-party tools including [license-checker](https://github.com/davglass/license-checker) (for NPM) and [python-license-check](https://github.com/dhatim/python-license-check) (for PyPI).
