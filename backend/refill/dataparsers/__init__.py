from .archiveis import ArchiveIs
from .arxiv import ArXiv
from .badauthors import BadAuthors
from .baddates import BadDates
from .citoid import Citoid
from .detectdeadlink import DetectDeadLink
from .doi import DOI
from .download import Download
from .jstor import JSTOR
from .ncbi import NCBI
from .parsespecial import ParseSpecial
from .parseurl import ParseURL
from .ris import RIS
from .title import Title

DefaultChain = [
    ParseURL(),
    Download(),
    DetectDeadLink(),
    Title(),
    # Archives
    ArchiveIs(),  # archive.is and friends
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
