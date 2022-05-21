from ..models import Citation
from ..utils import session


class DOI:
    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if "doi" not in citation or citation.isDerived("doi"):
            # Do not proceed if DOI is parsed by another dataparser
            return citation

        # https://crosscite.org/docs.html
        doiUrl = "https://doi.org/" + citation.doi
        response = session.get(
            doiUrl,
            headers={
                "Accept": "application/x-research-info-systems",
            },
        )

        if response.status_code != 200:
            return citation

        citation.raw["ris"] = response.text

        return citation
