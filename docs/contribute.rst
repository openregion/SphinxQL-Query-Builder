Contribute
==========

Pull Requests
-------------

1. Fork `SphinxQL Query Builder <https://github.com/FoolCode/SphinxQL-Query-Builder>`_
2. Create a new branch for each feature or improvement
3. Submit a pull request with your branch against the default branch

It is very important that you create a new branch for each feature, improvement, or fix so that may review the changes and merge the pull requests in a timely manner.

Coding Style
------------

All pull requests should follow modern PHP style conventions (PSR-12 compatible formatting).

Testing
-------

All pull requests must be accompanied with passing tests and code coverage. The SphinxQL Query Builder uses `PHPUnit <https://github.com/sebastianbergmann/phpunit/>`_ for testing.

Documentation
-------------

Documentation is built with Sphinx and the Furo theme.

Build locally:

.. code-block:: bash

    python3 -m pip install -r docs/requirements.txt
    sphinx-build --fail-on-warning --keep-going -b html docs docs/_build/html

Issue Tracker
-------------

You can find our issue tracker at our `SphinxQL Query Builder <https://github.com/FoolCode/SphinxQL-Query-Builder>`_ repository.
