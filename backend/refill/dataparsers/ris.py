from ..models import Citation

class RIS:
    # https://en.wikipedia.org/wiki/RIS_(file_format)
    MAPPING = {
        'UR': 'url',
        'TI': 'title',
        'T1': 'title',
        'T2': 'journal',
        'DO': 'doi',
        'AU': 'authors',
        'ED': 'editors',
        'SP': 'pagefrom',
        'EP': 'pageto',
        'VL': 'volume',
        'IS': 'issue',
        'DB': 'via',
        'PY': 'year',
    }

    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if 'ris' not in citation.raw:
            return citation

        for l in citation.raw['ris'].split('\n'):
            l = l.strip().split('-', 1)
            if len(l) < 2:
                continue

            tag = l[0].strip()
            value = l[1].strip()
            if tag == 'ER':
                break

            if tag in RIS.MAPPING:
                field = RIS.MAPPING[tag]
                if field == 'editors':
                    citation.editors.append(value)
                elif field == 'authors':
                    citation.authors.append(value)
                else:
                    citation[RIS.MAPPING[tag]] = value

        return citation

