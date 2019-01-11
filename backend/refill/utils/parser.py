import mwparserfromhell
from mwparserfromhell.wikicode import Wikicode
from mwparserfromhell.nodes.external_link import ExternalLink
from mwparserfromhell.nodes.template import Template
from ..models import Citation
from . import Utils


class Parser:
    TEMPLATE_TYPE_MAPPING = {
        'Cite web': 'webpage',
        'Cite journal': 'journalArticle',
        'Cite news': 'magazineArticle',
        'Cite book': 'manuscript',
    }
    TEMPLATE_FIELD_MAPPING = {
        'title': 'title',
        'url': 'url',
        'doi': 'doi',
        'pmid': 'pmid',
        'pmc': 'pmc',
    }

    def parse(content: Wikicode):
        if type(content) is str:
            content = mwparserfromhell.parse(content)

        citation = Citation()
        for node in content.ifilter(forcetype=(ExternalLink, Template), recursive=False):
            if type(node) is ExternalLink:
                citation.url = str(node.url)
                if node.title:
                    citation.title = str(node.title)
            elif type(node) is Template:
                tname = Utils.homogenizeTemplateName(str(node.name))
                if tname.startswith('Cite'):
                    if tname in Parser.TEMPLATE_TYPE_MAPPING:
                        citation.type = Parser.TEMPLATE_TYPE_MAPPING[tname]

                    for field in Parser.TEMPLATE_FIELD_MAPPING:
                        if node.has(field):
                            citation[Parser.TEMPLATE_FIELD_MAPPING[field]] = str(node.get(field).value)
                elif tname == 'Webarchive':
                    if node.has('url'):
                        citation.archiveurl = str(node.get('url').value)
                    if node.has('date'):
                        citation.archivedate = str(node.get('date').value)
                    if node.has('title'):
                        citation.title = str(node.get('title').value)

        if citation.isLocatable():
            return citation
        return False
