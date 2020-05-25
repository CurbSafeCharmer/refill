import dateparser
from datetime import date


class Citation:
    FIELDS = {
        'type': str,
        'url': str,
        'title': str,
        'date': date,
        'accessdate': date,
        'year': int,
        'authors': list,
        'editors': list,
        'publisher': str,
        'work': str,
        'website': str,
        'archiveurl': str,
        'archivedate': date,
        'url-status': str,
        'via': str,
        'journal': str,
        'volume': str,
        'issue': str,
        'pages': str, # auto-generated from pagefrom/pageto, if they exist
        'pagefrom': int,
        'pageto': int,
        'pmid': str,
        'pmc': str,
        'doi': str,
        'arxiv': str,
        'raw': dict,
    }
    MAGIC_FIELDS = [
        'pages',
    ]
    # Machine-accessible locators
    LOCATORS = [
        'url',
        'doi',
        'pmc',
        'pmid',
        'arxiv',
    ]

    def __init__(self, **kwargs):
        self.__dict__['_data'] = {}

        for field in Citation.FIELDS:
            self.__resetField(field)

        self.__dict__['_originalFrozen'] = False
        self.__dict__['_originalFields'] = set()
        self._data['type'] = 'webpage' # implicit/derived

        for field, value in kwargs.items():
            self[field] = value

    def __setattr__(self, field: str, value: str):
        if field.startswith('_'):
            self.__dict__[field] = value
            return

        self._data[field] = self.__cleanValue(field, value)
        if not self._originalFrozen:
            self._originalFields.add(field)

        if field == 'pages':
            if 'pagefrom' in self: del self.pagefrom
            if 'pageto' in self: del self.pageto

    def __getattr__(self, field: str):
        self.__assertValidField(field)

        if field == 'pages':
            if 'pagefrom' in self and 'pageto' in self and self.pagefrom != self.pageto:
                self._data['pages'] = '{}-{}'.format(self.pagefrom, self.pageto)
            elif 'pagefrom' in self:
                self._data['pages'] = self.pagefrom
            elif 'pageto' in self:
                self._data['pages'] = self.pageto

        return self._data[field]

    def __setitem__(self, field: str, value: str):
        self.__setattr__(field, value)

    def __getitem__(self, field: str):
        return self.__getattr__(field)

    def __delattr__(self, field: str):
        self.__assertValidField(field)
        self.__resetField(field)

    def __delitem__(self, field: str):
        return self.__delattr__(field)

    def __contains__(self, field: str):
        if field in Citation.MAGIC_FIELDS:
            return bool(getattr(self, field))

        return field in self._data and bool(getattr(self, field))

    def __iter__(self):
        for field in Citation.FIELDS:
            if field in self:
                yield (field, getattr(self, field))

    def __eq__(self, operand):
        if not isinstance(operand, self.__class__):
            return False

        return self._data == operand._data

    def addAuthor(self, author: str):
        self.authors.append(author)

    def removeAuthor(self, author: str):
        self.authors.remove(author)

    def merge(self, citation: 'Citation'):
        for key, value in citation._data.items():
            if value:
                self._data[key] = value

    def freezeOriginal(self):
        self._originalFrozen = True

    def isDerived(self, field: str) -> bool:
        return not self.isOriginal(field)

    def isOriginal(self, field: str) -> bool:
        self.__assertValidField(field)
        return field in self._originalFields

    def isLocatable(self) -> bool:
        return bool([field for field in Citation.LOCATORS if field in self])

    # Private

    def __assertValidField(self, field):
        if field not in Citation.FIELDS:
            raise NameError('Invalid field: {}'.format(field))

        return True

    def __cleanValue(self, field, value):
        self.__assertValidField(field)

        ftype = Citation.FIELDS[field]

        if ftype is date and type(value) is str:
            d = dateparser.parse(value)
            if not d:
                raise ValueError('Invalid date {}'.format(value))
            return d.date()
        elif ftype is int and type(value) is str:
            if not value.isdigit():
                raise ValueError('Invalid str of int {}'.format(value))
            return int(value)
        elif type(ftype) is list and value not in ftype:
            raise ValueError('Invalid value {} - Valid values are {}'.format(value, ftype))
        elif not type(value) is ftype:
            raise ValueError('Invalid value {} for field {}'.format(value, field))

        if type(value) is str:
            value = value.strip()

        return value

    def __resetField(self, field):
        ftype = Citation.FIELDS[field]

        if ftype is date:
            self._data[field] = None
        else:
            self._data[field] = ftype()
