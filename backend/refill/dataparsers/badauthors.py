from ..models import Citation
import string

class BadAuthors:
    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if self._checkList(citation.authors): del citation.authors
        if self._checkList(citation.editors): del citation.editors

    def _checkList(self, l):
        for name in l:
            if len([c for c in name if c in '|:' + string.digits]):
                return False
        return True
