from ..models import Citation
from furl import furl
import re

class ArchiveIs:
    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if 'parsedUrl' not in citation.raw or 'soup' not in citation.raw:
            return citation

        supportedDomains = [
            'archive.fo',
            'archive.is',
            'archive.li',
            'archive.md',
            'archive.ph',
            'archive.today',
            'archive.vn',
        ]

        if citation.raw['parsedUrl'].netloc not in supportedDomains:
            return citation

        node = citation.raw['soup'].find(id='SHARE_LONGLINK')
        if node:
            archiveurl = furl(node['value'])
            archiveurl.protocol = 'https'
            archiveurl.path.segments[0] = re.sub(
                r'^(\d{4})\.(\d{2})\.(\d{2})\-(\d{6})$', '\\1\\2\\3\\4',
                archiveurl.path.segments[0])
            origOffset = str(archiveurl.path).find('/', 1) + 1
            origUrl = str(archiveurl.path)[origOffset:]
            citation.archiveurl = archiveurl.url
            citation.url = origUrl

        return citation
