import logging
import re
from concurrent.futures import ThreadPoolExecutor
from uuid import uuid1

import celery.utils.log
import mwparserfromhell

from ..utils import Utils


class Context:
    def __init__(self):
        """Initialize the context

        Note:
            This does not depend on Celery. If no Celery task is attached,
            Celery-related methods are noop.
        """
        self._task = None
        self._page = None

        self.preferences = {}
        self.changes = []
        self.errors = []
        self.transforms = []
        self.transformMetadata = {}
        self.currentTransform = None
        self.currentTransformIndex = 0
        self.wikicode = None
        self.origWikicode = ""

        self.uuid = str(uuid1())

        self.executor = ThreadPoolExecutor(max_workers=10)
        self.getLogger = logging.getLogger
        self.logging = self.getLogger("refill")

    def attachTask(self, task):
        """Attach a Celery Task object"""
        self._task = task
        self.getLogger = celery.utils.log.get_logger
        self.logging = self.getLogger("refill")

    def attachPage(self, page):
        """Attach a pywikibot page"""
        self._page = page

    def setPreferences(self, preferences):
        """Set user preferences"""
        self.preferences = preferences

    def getPreference(self, preference: str, default: str = None):
        """Get user preference"""
        return self.preferences.get(preference, default)

    def applyTransforms(self, wikicode: str):
        """Apply scheduled transforms on the wikicode"""
        self.wikicode = mwparserfromhell.parse(
            Utils.protectMarkers(wikicode, self.uuid)
        )
        self.origWikicode = wikicode
        for index, transform in enumerate(self.transforms):
            self.currentTransform = transform
            self.currentTransformIndex = index
            self._updateState()
            transform.apply(self.wikicode)

    def getResult(self):
        """Get the final result as Celery metadata"""
        return self._generateTaskMetadata()

    def getPage(self):
        """Get the associated pywikibot Page object"""
        if self._page:
            return self._page
        return False

    def getDateFormat(self):
        """Get the preferred date format of the page"""

        page = self.getPage()
        if not page:
            return False

        lang = page.site.lang
        userPreference = self.getPreference("dateFormat", {}).get(lang, False)

        if not self.wikicode:
            return userPreference

        if lang == "en":
            try:
                hint = next(
                    self.wikicode.ifilter_templates(
                        recursive=False,
                        matches=lambda e: re.match(
                            r"^(U|u)se (mdy|dmy) dates$", str(e.name)
                        ),
                    )
                )
            except StopIteration:
                return userPreference

            return "mdy" if "mdy" in str(hint.name) else "dmy"

        return userPreference

    def reportProgress(self, state: str, percentage: float, metadata: dict):
        """Report progress of the current transform"""
        name = self.currentTransform.__class__.__name__
        self.transformMetadata[name] = {
            "state": state,
            "percentage": percentage,
            "metadata": metadata,
        }
        self._updateState()

    def reportChange(self, change: dict):
        """Report a change to the wikicode by the current transform"""
        change["transform"] = self.currentTransform.__class__.__name__
        self.changes.append(change)
        return len(self.changes) - 1

    def reportError(self, error: dict):
        """Report an error encountered during the current transform"""
        error["transform"] = self.currentTransform.__class__.__name__
        self.errors.append(error)
        return len(self.errors) - 1

    def _updateState(self):
        """Actually send our state to Celery"""
        if self._task:
            self._task.update_state(state="PROGRESS", meta=self._generateTaskMetadata())

    def _generateTaskMetadata(self):
        """Generate task metadata for Celery"""
        # Generate percentage
        name = self.currentTransform.__class__.__name__
        ind = self.currentTransformIndex
        if (
            name in self.transformMetadata
            and "percentage" in self.transformMetadata[name]
        ):
            ind += self.transformMetadata[name]["percentage"]
        percentage = ind / len(self.transforms)

        # Generate partial wikicode
        wikicode = str(self.wikicode) if self.wikicode else ""

        # Generate wiki page information
        if self._page:
            site = self._page.site
            family = site.family
            wikipage = {
                "fam": family.name,
                "code": site.code,
                "lang": site.lang,
                "page": self._page.title(),
                "upage": self._page.title(underscore=True),
                "domain": site.hostname(),
                "path": site.path(),
                "protocol": site.protocol(),
                "editTime": self._page.editTime().totimestampformat(),
                "startTime": site.getcurrenttimestamp(),
            }
        else:
            wikipage = {}

        cleanWikicode = Utils.unprotectMarkers(
            Utils.unmarkWikicode(wikicode), self.uuid
        )
        markedWikicode = Utils.unprotectMarkers(wikicode, self.uuid)
        return {
            "overall": {
                "percentage": percentage,
                "currentTransform": self.currentTransform.__class__.__name__,
                "currentTransformIndex": self.currentTransformIndex,
                "totalTransforms": len(self.transforms),
            },
            "transforms": self.transformMetadata,
            "changes": self.changes,
            "errors": self.errors,
            "wikicode": cleanWikicode,
            "markedWikicode": markedWikicode,
            "origWikicode": self.origWikicode,
            "wikipage": wikipage,
        }
