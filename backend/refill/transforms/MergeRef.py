from ..utils import Utils
from .Transform import Transform


class MergeRef(Transform):
    def __init__(self, ctx=None):
        super().__init__(ctx)
        self.suffix = 0

    def apply(self, wikicode):
        collection = []

        self._ctx.reportProgress("SCANNING", 0, {})
        for tag in wikicode.ifilter_tags():
            if tag.tag != "ref":  # or tag.self_closing:
                continue

            name = self._getName(tag)
            contents = Utils.unmarkWikicode(str(tag.contents))
            collection.append((name, contents, tag))

        self._ctx.reportProgress("MERGING", 0, {})
        uniqueNames = set([e[0] for e in collection if e[0]])
        uniqueContents = set([e[1] for e in collection if e[1]])
        allNames = uniqueNames.copy()

        for name in uniqueNames:
            # Maybe some distinct references are sharing the same name
            # Let's pick a Rightful Owner (tm) for the name
            chosenContents = next(
                iter(
                    [
                        e[1]
                        for e in sorted(collection, key=lambda e: len(e[1]))
                        if e[0] == name and e[1]
                    ]
                )
            )
            otherTags = [
                e[2]
                for e in collection
                if e[1] != chosenContents and e[0] == name and e[1]
            ]
            for tag in otherTags:
                self._removeName(tag)

        for contents in uniqueContents:
            # Maybe some identical references have different names
            # Let's pick a name
            tags = [e[2] for e in collection if e[1] == contents]

            if len(tags) == 1:
                continue

            names = sorted(self._getNames(tags), key=len)

            if not names:
                name = self._generateName(allNames)
                allNames.add(name)
            else:
                name = names[0]

            changed = []
            first = True
            for tag in tags:
                if first:
                    first = False
                else:
                    tag.self_closing = True

                old_name = self._getName(tag)
                self._setName(tag, name)
                if old_name != name and old_name is not False:
                    changed.append(old_name)

            all_tags = [e[2] for e in collection]
            for tag in all_tags:
                if self._getName(tag) in changed:
                    self._setName(tag, name)

        self._ctx.reportProgress("SUCCESS", 1, {})
        return wikicode

    def _getName(self, tag):
        if tag.has("name"):
            return str(tag.get("name").value)

        return False

    def _getNames(self, tags):
        result = set()

        for tag in tags:
            name = self._getName(tag)
            if name:
                result.add(name)

        return result

    def _removeName(self, tag):
        if tag.has("name"):
            tag.remove("name")

    def _setName(self, tag, name):
        self._removeName(tag)
        tag.add("name", name)

    def _generateName(self, existing, prefix="auto"):
        name = prefix

        while name in existing:
            self.suffix += 1
            name = prefix + str(self.suffix)

        return name
