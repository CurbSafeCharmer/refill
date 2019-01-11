# Data parsers

In reFill, data parsers function in a chain, with individual parsers uncovering more information based on what's found by previous ones. They typically work with the `raw` field in `Citation`, which contains different formats of raw data related to the webpage (HTML, RIS, etc.).
