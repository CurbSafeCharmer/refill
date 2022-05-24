import re
from datetime import date

from babel.dates import format_date
from mwparserfromhell.wikicode import Wikicode


class Utils:
    @staticmethod
    def homogenizeTemplateName(name: str):
        name = name.strip()
        if not name:
            return name
        return name[0].upper() + name[1:]

    @staticmethod
    def protectMarkers(wikicode: str or Wikicode, unique: str) -> str:
        """Protect markers
        This method protects existing markers present in the
        text so that reFill does not try to modify them.
        """
        return re.sub(r"RFL(\w\d+)LFR", r"RPFL%s=\1LFPR" % unique, str(wikicode))

    @staticmethod
    def unprotectMarkers(wikicode: str or Wikicode, unique: str) -> str:
        """Unprotect markers
        This method restores existing reFill markers present
        in the original wikicode that are not supposed to be
        modified/interpreted by the tool.
        """
        return re.sub(r"RPFL%s=(\w\d+)LFPR" % unique, r"RFL\1LFR", str(wikicode))

    @staticmethod
    def unmarkWikicode(wikicode: str or Wikicode) -> str:
        """Unmark wikicode

        This method removes markers from supplied wikicode.
        """
        return re.sub(r"RFL(\w\d+)LFR", "", str(wikicode))

    @staticmethod
    def formatDate(date: date, lang: str, format: str = "") -> str:
        """Format date
        This method generates a human-readable representation
        of a date object.
        """

        # reFill provides a platform-independent implementation of a
        # non-zero-padded day of the month directive, %=d
        SPECIAL_FORMAT = {
            "en": {
                "mdy": "%B %=d, %Y",
                "dmy": "%=d %B %Y",
                "numeric": "%Y-%m-%d",
            },
        }

        if lang in SPECIAL_FORMAT and format in SPECIAL_FORMAT[lang]:
            f = SPECIAL_FORMAT[lang][format]

            if callable(f):
                return f(date)
            else:
                return date.strftime(f).replace("%=d", str(date.day))
        else:
            return format_date(date, locale=lang)
