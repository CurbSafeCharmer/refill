from ..models import Citation


class Title:
    def __init__(self):
        pass

    def apply(self, citation: Citation) -> Citation:
        if "soup" not in citation.raw:
            return citation

        node = citation.raw["soup"].title
        if node:
            citation.title = str(node.string)

        return citation
