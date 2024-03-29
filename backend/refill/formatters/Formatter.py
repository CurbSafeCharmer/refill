from abc import ABCMeta, abstractmethod

from ..models import Citation, Context


class Formatter(metaclass=ABCMeta):
    @abstractmethod
    def __init__(self, ctx: Context = None):
        if ctx is None:
            self._ctx = Context()
        else:
            self._ctx = ctx

    @abstractmethod
    def format(self, citation: Citation):
        pass
