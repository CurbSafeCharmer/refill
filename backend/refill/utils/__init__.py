from .utils import Utils
from .parser import Parser
from .errors import NotFoundError, NoTitleError, ErrorPageError, HomepageRedirectError, UnknownError

import requests as _requests
import functools
session = _requests.Session()
session.headers['User-Agent'] = 'reFill/2 (https://en.wikipedia.org/wiki/User:Zhaofeng_Li/reFill)'

# Ugly hack to set default timeouts
# https://stackoverflow.com/a/55841818
for method in ('get', 'options', 'head', 'post', 'put', 'patch', 'delete'):
    setattr(session, method, functools.partial(getattr(session, method), timeout=(5, 10)))
