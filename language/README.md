# Message files
This directory holds the message files of the tool. All files are in JSON key-value format, and named in the format of `<language code>.json`. Message files other than `en.json` are periodically updated to include new translations on Transifex.

## Contributing
Localisation of the tool is powered by [Intuition](https://github.com/Krinkle/intuition) but handled on [Transifex](https://www.transifex.com/projects/p/refill/).

To start translating the tool, please register at Transifex and request to join the project. You can also submit your translations manually via GitHub pull requests or even [on-wiki](https://en.wikipedia.org/wiki/User_talk:Zhaofeng_Li/reFill).

Thank you for your contributions!

## Metadata
reFill uses a special metadata key, `refill-authors`, to hold the translator list. It holds an array of objects which have the following properties:
- `name`: String containing the name of the translator
- `url`: String containing an optional URL pointing to the profile/homepage of the translator

An example is provided below:
```json
{
	"@metadata": {
		"refill-authors": [
			{
				"name": "Zhaofeng Li",
				"url": "http://zhaofeng.li"
			}
		]
	},
	"(The rest of the message file)"
}
```

If `acknowledgements.php` can't find the `refill-authors` key, it will try to use the standard `authors` list present in standard Intuition message files, which is an array containing translators' names. That being said, `author` won't be looked at at all if `refill-authors` is present.

