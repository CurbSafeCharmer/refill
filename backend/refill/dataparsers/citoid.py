from urllib.parse import quote_plus, unquote

from ..models import Citation
from ..utils import session


class Citoid:
    BADDATA = (
        "browse publications",
        "central authentication service",
        "zbmath - the first resource for mathematics",
        "mr: matches for:",
        "log in",
        "sign in",
        "bookmarkable url intermediate page",
        "shibboleth authentication request",
        "domain for sale",
        "website for sale",
        "domain is for sale",
        "website is for sale",
        "lease this domain",
        "domain available",
        "metatags",
        "an error occurred",
        "user cookie",
        "cookies disabled",
        "page not found",
        "411 error",
        "url not found",
        "limit exceeded",
        "error page",
        "eu login",
        "bad gateway",
        "captcha",
        "view pdf",
        "wayback machine",
        "does not exist",
        "subscribe to read",
        "wiley online library",
        "pagina is niet gevonden",
        "zoeken in over na",
        "na een 404",
        "404 error",
        "account suspended",
        "error 404",
        "ezproxy",
        "ebscohost login",
        "404 - not found",
        "404!",
        "temporarily unavailable",
        "has expired",
        "not longer available",
        "article expired",
        "openid transaction in progress",
        "download limit exceeded",
        "internet archive wayback machine",
        "url（アドレス）が変わりました",
        "404エラ",
        "お探しのページは見つかりませんでした",
        "privacy settings",
        "cookie settings",
        "webcite query",
        "ой!",
        "untitled-1",
        "untitled-2",
        "untitled-3",
        "untitled-4",
        "untitled-5",
        "untitled-6",
        "untitled-7",
        "untitled-8",
        "untitled-9",
        "are you a robot",
        "aanmelden of registreren om te bekijken",
        "register to view",
        "being redirected",
        "aanmelden bij facebook",
        "einloggen",
        "the times & the sunday times",
        "login • instagram",
        "subscriber to read",
        "has now officially closed",
        "an error has occured",
        "an error has occurred",
        "youtube, a google company",
        "seite nicht gefunden",
        "página no encontrada",
        "الصفحة غير موجودة",
        "找不到网页",
        "страница не найдена",
        "page non trouvée",
        "an error occured",
        "compare payday loans",
        "find the best loan deal",
        "..::.. error",
        "pagina inicia",
        "help center - the arizona republic",
        "404 error",
        "404 - url invalid",
        "404. that's an error",
        "404 - page not found",
        "página não existe",
        "this is not the page you requested",
        "page not found",
        "404 - -",
        "sex cams",
        "404 &#124;",
        "missing page",
        "404 - file or directory not found",
        "错误页面",
        "404 page -",
        "404: page not found",
        "404: page not found",
        "404 error",
        "404 |",
        "页面不存在",
        "de pagina is niet gevonden",
        "404 -",
        "stranica nije pronađena",
        "404 page",
        "404. the page",
        "wasn't found on this server",
        "404. the url",
        "shieldsquare",
        "404 not found",
        "404页面",
        "sign up | linkedin",
        "the-star.co.kr",
        "connecting to the itunes store",
        "500 internal server error",
        "domainmarket.com",
        "bluehost.com",
        "unknown",
        "missing",
        "arxiv e-prints",
        "arxiv mathematics e-prints",
        "ssrn electronic journal",
        "dissertations available from proquest",
        "ebscohost login",
        "library login",
        "google groups",
        "sciencedirect",
        "cur_title",
        "wordpress › error",
        "ssrn temporarily unavailable",
        "log in - proquest",
        "shibboleth authentication request",
        "nookmarkable url intermediate page",
        "google books",
        "rte.ie",
        "loading",
        "google book",
        "the article you have been looking for has expired and is not longer available on our system. this is due to newswire licensing terms.",
        "openid transaction in progress",
        "download limit exceeded",
        "privacy settings",
        "untitled-1",
        "untitled-2",
        "professional paper",
        "zbmath",
        "theses and dissertations available from proquest",
        "proquest ebook central",
        "report",
        "bloomberg - are you a robot?",
        "page not found",
        "free live sex cams",
        "breaking news, analysis, politics, blogs, news photos, video, tech reviews",
        "breaking news, analysis, politics, blogs, news photos, video, tech reviews - time.com",
        "redirect notice",
        "oxford music online",
        "trove - archived webpage",
        "pagina inicia",
        "404 not found",
        "404页面",
        "sign up",
        "index of /home",
        "usa today - today's breaking news, us & world news",
        "403 unauthorized",
        "404错误",
        "internal server error",
        "error",
        "404",
        "error - lexisnexis® publisher",
        "optica publishing group",
        "validate user"
    )
    ENDPOINT = "https://en.wikipedia.org/api/rest_v1/data/citation"
    MAPPING = {
        "default": {
            "url": "url",
            "title": "title",
            "author": "authors",
            "editor": "editors",
            "publisher": "publisher",
            "date": "date",
            "volume": "volume",
            "issue": "issue",
            "pages": "pages",
            "PMID": "pmid",
            "PMCID": "pmc",
            "DOI": "doi",
            "libraryCatalog": "via",
            "websiteTitle": "website",
        },
        "bookSection": {
            "bookTitle": "title",
        },
        "journalArticle": {
            "publicationTitle": "journal",
        },
    }
    UNSUPPORTED_DOMAINS = [
        "jstor.org",
        "www.jstor.org",
    ]

    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if "url" not in citation or "parsedUrl" not in citation.raw:
            return citation

        if citation.raw["parsedUrl"].netloc in Citoid.UNSUPPORTED_DOMAINS:
            return citation

        action = Citoid.ENDPOINT + "/mediawiki/"
        action += quote_plus(unquote(citation.url))

        response = session.get(action)
        if response.status_code != 200:
            return citation

        data = response.json()[0]
        citation.type = data["itemType"]

        mapping = Citoid.MAPPING["default"].copy()
        if citation.type in Citoid.MAPPING:
            mapping.update(Citoid.MAPPING[citation.type])

        for cfield, value in data.items():
            if cfield in mapping:

                def flatten(v):
                    return (
                        "".join([flatten(e) for e in v])
                        if isinstance(v, (list,))
                        else v
                    )

                if "\ufffd" in flatten(value):
                    # UTF-8 replacement character - Citoid's codec has removed some information
                    continue
                if "{{" in flatten(value):
                    # Almost never good
                    continue
                if "}}" in flatten(value):
                    continue

                field = mapping[cfield]
                citation[field] = value

        if citation.url == citation.title:
            citation.title = ""
        if citation.title.lower() in Citoid.BADDATA:
            citation.title = ""

        return citation
