from ..models import Citation
from ..utils import session


class NCBI:
    ENDPOINT = "https://api.ncbi.nlm.nih.gov/lit/ctxp/v1"

    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if "pmid" in citation and citation.isOriginal("pmid"):
            idType = "pmid"
            identifier = citation.pmid
        elif "pmc" in citation and citation.isOriginal("pmc"):
            idType = "pmc"
            identifier = citation.pmc
        else:
            return citation

        response = session.get(
            NCBI.ENDPOINT + "/" + idType,
            params={
                "format": "ris",
                "id": identifier,
                "download": "true",
            },
        )

        if response.status_code != 200:
            return citation

        citation.raw["ris"] = response.text

        return citation
