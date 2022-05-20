from ..models import Citation


class ParseSpecial:
    """
    How to write a special rule

    name: Name of the rule
    match: Domain(s) this rule will match
        This can be a string, a list of strings, or a callable which
        will be passed the Citation to do complex matching
    fields:
        element: Element matcher
            A callable that will be passed the current element, and
            should return a boolean indicating if it's a match
        value: Value extractor
            A callable that will be passed the matched element, and
            should return a string
    """

    RULES = [
        {
            "name": "bbc_genome",
            "match": "genome.ch.bbc.co.uk",
            "fields": {
                "date": {
                    "element": lambda e: e.name == "a"
                    and e["href"].startswith("/schedules")
                    and e.findChildren("span", {"class", "time"}),
                    "value": lambda e: str(e.contents[0]).split(",", 1)[-1].strip(),
                },
                "type": {
                    "element": lambda e: e.name == "aside"
                    and "block" in e.get("class", [])
                    and e.findChildren("img"),
                    "value": lambda e: "radioBroadcast"
                    if "radio" in e.text
                    else "tvBroadcast",
                },
            },
        },
    ]

    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if "soup" not in citation.raw:
            return citation

        matchedRules = [
            rule
            for rule in ParseSpecial.RULES
            if self._matchCitation(citation, rule["match"])
        ]

        for rule in matchedRules:
            for field in rule["fields"]:
                f = rule["fields"][field]
                el = citation.raw["soup"].find(f["element"])
                if not el:
                    continue

                if callable(f["value"]):
                    try:
                        citation[field] = f["value"](el)
                    except Exception as e:
                        # Webpages change frequently, so extractor errors shouldn't fail the entire task
                        print(e)
                elif isinstance(f["value"], (str,)):
                    citation[field] = f["value"]
                else:
                    raise ValueError("Invalid value extractor")

        return citation

    def _matchCitation(self, citation: Citation, match):
        if isinstance(match, (str,)):
            if "parsedUrl" not in citation.raw:
                return False
            return citation.raw["parsedUrl"].netloc == match

        if isinstance(match, (list,)):
            if "parsedUrl" not in citation.raw:
                return False
            return citation.raw["parsedUrl"].netloc in match

        if callable(match):
            return match(citation)

        raise ValueError("Invalid citation matcher")
