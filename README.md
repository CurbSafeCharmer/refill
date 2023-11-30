# reFill 2

**reFill** fixes [bare URLs](https://en.wikipedia.org/wiki/Wikipedia:Bare_URLs) on Wikipedia articles, semi-automatically. It extracts bibliographical information from web pages referenced by bare URL citations, generates complete references and finally inserts them back onto the original page.

This README gives you all the details needed to set up a reFill instance for testing and development. If you only intend to use reFill, [the manual](https://en.wikipedia.org/wiki/WP:reFill) may be more helpful.

The code is divided into 3 main folders. Please see the readmes in each folder for more details.

- /version1/ - The original jQuery front end of reFill that is deployed at https://refill.toolforge.org/.
- /version2ng/ - The new Vue front end of reFill that is deployed at https://refill.toolforge.org/ng
- /labs-config/ - Files used on ToolForge.

Please report issues on Phabricator (https://phabricator.wikimedia.org/project/board/5013/).
