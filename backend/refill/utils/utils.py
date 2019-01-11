from mwparserfromhell.wikicode import Wikicode
import re

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
        return re.sub(r'RFL(\w\d+)LFR', r'RPFL%s=\1LFPR' % unique, str(wikicode))

    @staticmethod
    def unprotectMarkers(wikicode: str or Wikicode, unique: str) -> str:
        """Unprotect markers
        This method restores existing reFill markers present
        in the original wikicode that are not supposed to be
        modified/interpreted by the tool.
        """
        return re.sub(r'RPFL%s=(\w\d+)LFPR' % unique, r'RFL\1LFR', str(wikicode))

    @staticmethod
    def unmarkWikicode(wikicode: str or Wikicode) -> str:
        """Unmark wikicode

        This method removes markers from supplied wikicode.
        """
        return re.sub(r'RFL(\w\d+)LFR', '', str(wikicode))
