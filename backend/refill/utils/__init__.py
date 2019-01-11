from .utils import Utils
from .parser import Parser
from .errors import NotFoundError, NoTitleError, ErrorPageError, HomepageRedirectError, UnknownError

import requests as _requests
session = _requests.Session()
session.headers['User-Agent'] = 'reFill/2 (https://en.wikipedia.org/wiki/User:Zhaofeng_Li/reFill)'
