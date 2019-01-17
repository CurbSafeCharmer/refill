from .parseurl import ParseURL
from .download import Download
from .detectdeadlink import DetectDeadLink

from .title import Title
from .archiveis import ArchiveIs
from .citoid import Citoid

from .badauthors import BadAuthors
from .baddates import BadDates
from .parsespecial import ParseSpecial

from .doi import DOI
from .ncbi import NCBI
from .jstor import JSTOR
from .ris import RIS

from .arxiv import ArXiv

DefaultChain = [
    ParseURL(),
    Download(),
    DetectDeadLink(),

    Title(),

    # Archives
    ArchiveIs(), # archive.is and friends

    # Citoid
    Citoid(),

    # Data cleaning
    BadAuthors(),
    BadDates(),
    ParseSpecial(),

    # RIS providers
    DOI(),
    NCBI(),
    JSTOR(),
    RIS(),

    ArXiv(),
]
