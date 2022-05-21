from ..models import Citation
from ..utils import session


class JSTOR:
    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if "parsedUrl" not in citation.raw:
            return citation

        parsedUrl = citation.raw["parsedUrl"]

        if parsedUrl.netloc not in ["jstor.org", "www.jstor.org"]:
            return citation

        if len(parsedUrl.path.segments) < 2 or parsedUrl.path.segments[0] != "stable":
            return citation

        if citation.isDerived("type"):
            citation.type = "journalArticle"

        risUrl = "https://www.jstor.org/citation/ris/" + parsedUrl.path.segments[1]
        response = session.get(risUrl)
        if response.status_code != 200:
            return citation

        response.encoding = "utf-8"
        citation.raw["ris"] = response.text

        return citation
