import mwparserfromhell
from mwparserfromhell.nodes.external_link import ExternalLink
from mwparserfromhell.nodes.template import Template
from mwparserfromhell.nodes.text import Text
from mwparserfromhell.wikicode import Wikicode

from ..models import Citation
from . import Utils


class Parser:
    TEMPLATE_TYPE_MAPPING = {
        "Cite web": "webpage",
        "Cite journal": "journalArticle",
        "Cite news": "magazineArticle",
        "Cite book": "manuscript",
    }
    TEMPLATE_FIELD_MAPPING = {
        "title": "title",
        "url": "url",
        "doi": "doi",
        "pmid": "pmid",
        "pmc": "pmc",
    }

    def parse(content: Wikicode):
        if type(content) is str:
            content = mwparserfromhell.parse(content)

        citation = Citation()
        for node in content.ifilter(recursive=False):
            if type(node) is ExternalLink:
                citation.url = str(node.url)
                if node.title:
                    citation.title = str(node.title)
            elif type(node) is Template:
                tname = Utils.homogenizeTemplateName(str(node.name))
                if tname.startswith("Cite"):
                    if tname in Parser.TEMPLATE_TYPE_MAPPING:
                        citation.type = Parser.TEMPLATE_TYPE_MAPPING[tname]

                    for param in node.params:
                        pname = str(param.name)
                        if pname in Parser.TEMPLATE_FIELD_MAPPING:
                            citation[Parser.TEMPLATE_FIELD_MAPPING[pname]] = str(
                                node.get(pname).value
                            )
                        else:
                            # Do not touch citations with unparsable data
                            return False
                elif tname == "Webarchive":
                    if node.has("url"):
                        citation.archiveurl = str(node.get("url").value)
                    if node.has("date"):
                        citation.archivedate = str(node.get("date").value)
                    if node.has("title"):
                        citation.title = str(node.get("title").value)
            elif type(node) is Text:
                if str(node).strip():
                    # Non-empty text node
                    return False
            else:
                # Do not touch citations with unparsable data
                return False

        if citation.isLocatable():
            return citation
        return False
