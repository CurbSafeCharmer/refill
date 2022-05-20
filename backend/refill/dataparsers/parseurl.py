from furl import furl

from ..models import Citation


class ParseURL:
    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if "url" in citation:
            citation.raw["parsedUrl"] = furl(citation.url)

        return citation
