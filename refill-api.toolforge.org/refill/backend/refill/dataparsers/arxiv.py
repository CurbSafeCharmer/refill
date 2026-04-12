from ..models import Citation


class ArXiv:
    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if citation.journal.startswith("arXiv"):
            # Should not be in |journal=
            del citation.journal

        if "parsedUrl" not in citation.raw:
            return citation

        parsedUrl = citation.raw["parsedUrl"]
        if parsedUrl.netloc not in ["arxiv.org", "www.arxiv.org"]:
            return citation

        if len(parsedUrl.path.segments) < 2 or parsedUrl.path.segments[0] != "abs":
            return citation

        identifier = parsedUrl.path.segments[1]
        citation.arxiv = identifier

        return citation
