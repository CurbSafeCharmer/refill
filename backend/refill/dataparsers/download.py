from bs4 import BeautifulSoup
from requests.exceptions import SSLError

from ..models import Citation
from ..utils import session
from ..utils.errors import FetchError


class Download:
    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        """Apply the data parser on the Citation
        Keep in mind that data parsers transform the Citation
        in place.
        """

        if "url" not in citation:
            return citation

        try:
            response = session.get(citation.url)
        except SSLError:
            raise FetchError(
                citation.url,
                {
                    "type": "SSLError",
                },
            )

        if response.status_code != 200:
            return citation

        citation.raw["downloaded"] = response
        citation.raw["soup"] = BeautifulSoup(response.text, "html.parser")

        return citation
