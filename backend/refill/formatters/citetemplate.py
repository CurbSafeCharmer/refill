from mwparserfromhell.nodes.template import Template
from .formatter import Formatter
from ..models import Context, Citation
from ..utils import Utils


class CiteTemplate(Formatter):
    ORDER = [
        'url',
        'archiveurl',
        'url-status',
        'title',
        'authors',
        'editors',
        'date',
        'year',
        'archivedate',
        'publisher',
        'website',
        'journal',
        'volume',
        'issue',
        'pages',
        'accessdate',
        'via',
        'doi',
        'pmid',
        'pmc',
        'arxiv',
    ]

    """
    Fallback citation templates to use, in case
    MediaWiki:Citoid-template-type-map.json does not
    exist on the wiki.

    Keep a minimal list of common citation templates
    that are almost available on all Wikipedias (we are
    not considering other WMF projects here).
    """
    TEMPLATE_FALLBACK = {
        'webpage': 'Cite web',
        'bookSection': 'Cite book',
        'default': 'Cite web',
        'journalArticle': 'Cite journal',
    }

    def __init__(self, ctx: Context=None):
        super().__init__(ctx)

    def format(self, citation: Citation):
        tname = CiteTemplate.TEMPLATE_FALLBACK.get(
            citation.type,
            CiteTemplate.TEMPLATE_FALLBACK['default']
        )
        template = Template(tname)

        for fragment in CiteTemplate.ORDER:
            func = getattr(self, '_fragment_' + fragment, None)
            if func:
                func(template, citation)
            elif fragment in citation:
                template.add(fragment, str(citation[fragment]))

        return str(template)

    def _fragment_date(self, template, citation, field='date'):
        fmt = self._ctx.getDateFormat()
        if field in citation:
            page = self._ctx.getPage()
            lang = 'en' if not page else page.site.lang
            template.add(field, Utils.formatDate(
                citation[field], lang, fmt
            ))

    def _fragment_archivedate(self, template, citation):
        self._fragment_date(template, citation, field='archivedate')

    def _fragment_accessdate(self, template, citation):
        self._fragment_date(template, citation, field='accessdate')

    def _fragment_url-status(self, template, citation):
        if 'archiveurl' in citation:
            template.add('url-status', 'dead')

    def _fragment_authors(self, template, citation, field='authors'):
        para = 'editor' if field == 'editors' else 'author'
        multiprefix = 'editor-' if field == 'editors' else ''

        if field not in citation:
            return ''
        elif len(citation[field]) > 1:
            for index, name in enumerate(citation[field]):
                self._generateAuthor(template, name, para, multiprefix, index + 1)
        else:
            self._generateAuthor(template, citation[field][0], para, multiprefix)

    def _fragment_editors(self, template, citation):
        return self._fragment_authors(template, citation, field='editors')

    def _generateAuthor(self, template, name, para='author', multiprefix='', index=None):
        istr = str(index) if index else ''

        if type(name) is list:
            template.add(multiprefix + 'first' + istr, name[0])
            template.add(multiprefix + 'last' + istr, name[1])
        else:
            template.add(para + istr, name)
