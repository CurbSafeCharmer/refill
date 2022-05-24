from ..models import Citation, Context
from .formatter import Formatter


class Plainlink(Formatter):
    def __init__(self, ctx: Context = None):
        super().__init__(ctx)

    def format(self, citation: Citation):
        return "[{} {}]".format(citation.url, citation.title)
