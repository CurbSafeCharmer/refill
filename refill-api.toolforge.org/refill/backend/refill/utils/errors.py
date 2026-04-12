class NotFoundError(Exception):
    def __init__(self, url):
        super().__init__(url)
        self.type = "notfound"


class NoTitleError(Exception):
    def __init__(self, url):
        super().__init__(url)
        self.type = "notitle"


class ErrorPageError(Exception):
    def __init__(self, url):
        super().__init__(url)
        self.type = "errorpage"


class HomepageRedirectError(Exception):
    def __init__(self, url):
        super().__init__(url)
        self.type = "homepageredir"


class FetchError(Exception):
    def __init__(self, url, info={}):
        super().__init__(url)
        self.type = "fetcherror"
        self.info = info


class UnknownError(Exception):
    def __init__(self, url):
        super().__init__(url)
        self.type = "unknown"
