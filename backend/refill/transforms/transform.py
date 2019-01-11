from ..models import Context
from abc import ABCMeta, abstractmethod
from mwparserfromhell.wikicode import Wikicode


class Transform(metaclass=ABCMeta):
    @abstractmethod
    def __init__(self, ctx: Context=None):
        if ctx is None:
            self._ctx = Context()
        else:
            self._ctx = ctx

    @abstractmethod
    def apply(self, wikicode: Wikicode):
        pass
