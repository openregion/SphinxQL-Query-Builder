from datetime import date

project = "SphinxQL Query Builder"
author = "FoolCode"
copyright = f"2012-{date.today().year}, {author}"

# We track release notes in CHANGELOG.md and do not hardcode package versions here.
version = "current"
release = version

extensions = [
    "sphinx.ext.autodoc",
    "sphinx.ext.viewcode",
    "sphinx.ext.githubpages",
    "sphinx_copybutton",
]

templates_path = ["_templates"]
exclude_patterns = ["_build", "Thumbs.db", ".DS_Store"]

source_suffix = {".rst": "restructuredtext"}
root_doc = "index"
language = "en"

pygments_style = "sphinx"
pygments_dark_style = "monokai"

html_theme = "furo"
html_title = "SphinxQL Query Builder Documentation"
html_static_path = ["_static"]
html_css_files = ["custom.css"]
html_theme_options = {
    "source_repository": "https://github.com/FoolCode/SphinxQL-Query-Builder/",
    "source_branch": "master",
    "source_directory": "docs/",
    "navigation_with_keys": True,
}

copybutton_prompt_text = r">>> |\.\.\. |\$ "
copybutton_prompt_is_regexp = True
