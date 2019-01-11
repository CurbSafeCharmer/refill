from ..models import Citation
from furl import furl

class ParseURL:
    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if 'url' in citation:
            citation.raw['parsedUrl'] = furl(citation.url)

        return citation
