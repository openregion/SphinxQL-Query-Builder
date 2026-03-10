Contribute
==========

Thanks for improving SphinxQL Query Builder.

Pull Requests
-------------

1. Fork `SphinxQL Query Builder <https://github.com/openregion/SphinxQL-Query-Builder>`_.
2. Create a branch for your change.
3. Open a pull request against the default branch.

Please keep each pull request focused on one logical change.

Development Guidelines
----------------------

- Follow PSR-12 style.
- Add/update tests for behavior changes.
- Update docs when public API behavior changes.

Testing
-------

Run the Docker-based matrix used in CI:

.. code-block:: bash

    ./scripts/run-tests-docker.sh

This runs PHPUnit for both mysqli and PDO configurations.

Build Docs Locally
------------------

.. code-block:: bash

    python3 -m pip install -r docs/requirements.txt
    sphinx-build --fail-on-warning --keep-going -b html docs docs/_build/html

Issue Tracker
-------------

Use the GitHub issue tracker:
`github.com/openregion/SphinxQL-Query-Builder/issues <https://github.com/openregion/SphinxQL-Query-Builder/issues>`_.
