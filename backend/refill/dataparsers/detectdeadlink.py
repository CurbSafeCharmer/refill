from furl import furl

from ..models import Citation
from ..utils import HomepageRedirectError


class DetectDeadLink:
    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if (
            "url" not in citation
            or "parsedUrl" not in citation.raw
            or "downloaded" not in citation.raw
        ):
            return citation

        origUrl = citation.raw["parsedUrl"]
        finalUrl = furl(citation.raw["downloaded"].url.strip("/"))

        if finalUrl.path.segments and finalUrl.path.segments[-1] == "":
            finalUrl.path.segments.pop()

        if len(origUrl.path.segments) > 3 and len(finalUrl.path.segments) < 2:
            raise HomepageRedirectError(citation.url)

        return citation
