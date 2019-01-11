from .formatter import Formatter
from ..models import Context, Citation


class Plainlink(Formatter):
    def __init__(self, ctx: Context=None):
        super().__init__(ctx)

    def format(self, citation: Citation):
        return '[{} {}]'.format(citation.url, citation.title)
