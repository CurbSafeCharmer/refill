from abc import ABCMeta, abstractmethod

from mwparserfromhell.wikicode import Wikicode

from ..models import Context


class Transform(metaclass=ABCMeta):
    @abstractmethod
    def __init__(self, ctx: Context = None):
        if ctx is None:
            self._ctx = Context()
        else:
            self._ctx = ctx

    @abstractmethod
    def apply(self, wikicode: Wikicode):
        pass
