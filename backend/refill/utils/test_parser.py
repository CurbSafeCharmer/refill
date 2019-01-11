import unittest
from datetime import date
from . import Parser
from ..models import Citation


class TestParser(unittest.TestCase):
    def test_url(self):
        data = [
            ('https://example.com', Citation(url='https://example.com')),
            ('http://example.com', Citation(url='http://example.com')),
            ('//invalid.com', False),
        ]
        self._testData(data)

    def test_captioned(self):
        data = [
            ('[https://example.com caption]', Citation(url='https://example.com', title='caption')),
            ('[http://example.com caption]', Citation(url='http://example.com', title='caption')),
            ('[//example.com caption]', Citation(url='//example.com', title='caption')),
            ('[example.com invalid]', False),
        ]
        self._testData(data)

    def test_template(self):
        data = [
            ('{{cite web|url=https://example.com}}', Citation(url='https://example.com')),
            ('{{Cite web|url=https://example.com}}', Citation(url='https://example.com')),
            ('{{Cite web|url=http://example.com}}', Citation(url='http://example.com')),
            ('{{Cite web|url=//example.com}}', Citation(url='//example.com')),
        ]
        self._testData(data)

    def test_webarchive(self):
        data = [
            (
                '[http://example.com]{{webarchive|url=http://archive.tld}}',
                Citation(url='http://example.com', archiveurl='http://archive.tld')
            ),
            (
                '[http://example.com]{{webarchive|url=http://archive.tld|date=1 January 2000}}',
                Citation(url='http://example.com', archiveurl='http://archive.tld', archivedate=date(2000, 1, 1))
            ),
            (
                '[http://example.com]{{webarchive|url=http://archive.tld|title=Archived Stuff}}',
                Citation(url='http://example.com', title='Archived Stuff', archiveurl='http://archive.tld')
            ),
        ]
        self._testData(data)

    def _testData(self, matrix):
        for text, expected in matrix:
            self.assertEqual(Parser.parse(text), expected)
