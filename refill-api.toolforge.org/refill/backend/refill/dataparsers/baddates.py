from datetime import date, timedelta

from ..models import Citation


class BadDates:
    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if "date" in citation:
            today = date.today()
            day = timedelta(days=1)
            if citation.date > today + day:
                del citation.date

        return citation
