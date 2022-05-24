from urllib.parse import quote_plus, unquote

from ..models import Citation
from ..utils import session


class Citoid:
    ENDPOINT = "https://en.wikipedia.org/api/rest_v1/data/citation"
    MAPPING = {
        "default": {
            "url": "url",
            "title": "title",
            "author": "authors",
            "editor": "editors",
            "publisher": "publisher",
            "date": "date",
            "volume": "volume",
            "issue": "issue",
            "pages": "pages",
            "PMID": "pmid",
            "PMCID": "pmc",
            "DOI": "doi",
            "libraryCatalog": "via",
            "websiteTitle": "website",
        },
        "bookSection": {
            "bookTitle": "title",
        },
        "journalArticle": {
            "publicationTitle": "journal",
        },
    }
    UNSUPPORTED_DOMAINS = [
        "jstor.org",
        "www.jstor.org",
    ]

    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if "url" not in citation or "parsedUrl" not in citation.raw:
            return citation

        if citation.raw["parsedUrl"].netloc in Citoid.UNSUPPORTED_DOMAINS:
            return citation

        action = Citoid.ENDPOINT + "/mediawiki/"
        action += quote_plus(unquote(citation.url))

        response = session.get(action)
        if response.status_code != 200:
            return citation

        data = response.json()[0]
        citation.type = data["itemType"]

        mapping = Citoid.MAPPING["default"].copy()
        if citation.type in Citoid.MAPPING:
            mapping.update(Citoid.MAPPING[citation.type])

        for cfield, value in data.items():
            if cfield in mapping:

                def flatten(v):
                    return (
                        "".join([flatten(e) for e in v])
                        if isinstance(v, (list,))
                        else v
                    )

                if "\ufffd" in flatten(value):
                    # UTF-8 replacement character - Citoid's codec has removed some information
                    continue

                field = mapping[cfield]
                citation[field] = value

        if citation.url == citation.title:
            citation.title = ""

        return citation
