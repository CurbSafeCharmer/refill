from concurrent.futures import as_completed

from ..dataparsers import DefaultChain
from ..models import Citation
from .transform import Transform


class FillExternal(Transform):
    def __init__(self, ctx=None):
        super().__init__(ctx)

    def apply(self, wikicode):
        futures = set()

        linkCount = 0
        completeCount = 0
        errors = []

        self._ctx.reportProgress("SCANNING", 0, {})
        for tag in wikicode.ifilter_external_links(recursive=False):
            if tag.title:
                continue

            linkCount += 1

            url = str(tag.url)
            futures.add(self._ctx.executor.submit(self._fulfill, url, tag))

        for future in as_completed(futures):
            try:
                future.result()
            except Exception as e:
                raise
                errors.append(str(e))
            else:
                completeCount += 1

            self._ctx.reportProgress(
                "FETCHING", completeCount / linkCount, {"errors": errors}
            )

        return wikicode

    def _fulfill(self, url, tag):
        citation = Citation()
        citation.url = url
        citation.freezeOriginal()

        for p in DefaultChain:
            p.apply(citation)

        tag.title = citation.title
        tag.brackets = True
