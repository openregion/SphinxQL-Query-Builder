SphinxQL Query Builder
======================

Creating a Builder
------------------

.. code-block:: php

    <?php

    use Foolz\SphinxQL\Drivers\Mysqli\Connection;
    use Foolz\SphinxQL\SphinxQL;

    $conn = new Connection();
    $conn->setParams(array('host' => '127.0.0.1', 'port' => 9306));

    $queryBuilder = new SphinxQL($conn);

Supported Query Types
---------------------

- ``SELECT``
- ``INSERT``
- ``REPLACE``
- ``UPDATE``
- ``DELETE``
- raw query via ``query($sql)``

Compilation and Execution
-------------------------

.. code-block:: php

    $sql = $queryBuilder
        ->select('id')
        ->from('rt')
        ->compile()
        ->getCompiled();

.. code-block:: php

    $result = $queryBuilder
        ->select('id')
        ->from('rt')
        ->execute();

Escaping
--------

- ``SphinxQL::expr()`` bypasses escaping for trusted SQL fragments.
- ``quote()`` and ``quoteArr()`` are provided by the connection.
- ``escapeMatch()`` and ``halfEscapeMatch()`` are available on ``SphinxQL``.

Strict Validation in 4.0
------------------------

The builder now validates critical query-shape input and throws
``SphinxQLException`` on invalid values:

- invalid ``setType()`` values
- invalid order direction values
- negative ``limit()`` / ``offset()``
- invalid shapes for ``IN`` and ``BETWEEN`` filters
- invalid ``facet()`` object type

Boolean Grouping and OR Filters
-------------------------------

The builder supports grouped boolean filters for ``WHERE`` and ``HAVING``:

- ``orWhere()``
- ``whereOpen()`` / ``orWhereOpen()`` / ``whereClose()``
- ``orHaving()``
- ``havingOpen()`` / ``orHavingOpen()`` / ``havingClose()``

Repeated ``having()`` calls are additive and compile as ``AND`` conditions unless
you explicitly use ``orHaving()`` / grouped clauses.

JOIN and KNN Ordering
---------------------

``SELECT`` queries support fluent joins:

- ``join()``, ``innerJoin()``, ``leftJoin()``, ``rightJoin()``, ``crossJoin()``

Vector-oriented ordering is available through:

- ``orderByKnn($field, $k, array $vector, $direction = 'ASC')``

Capability Introspection
------------------------

``SphinxQL`` exposes runtime capability helpers for connection-aware behavior:

- ``getCapabilities()``
- ``supports($feature)``
- ``requireSupport($feature, $context = '')``

Helper Capability-Aware Calls
-----------------------------

The same capability model is used by ``Helper`` wrappers:

- filtered ``SHOW`` wrappers:

  - ``showTables($index = null)`` => ``SHOW TABLES`` (when ``null`` or empty) or ``SHOW TABLES LIKE <quoted index>``
  - ``showTableStatus($table = null)`` => ``SHOW TABLE STATUS`` or ``SHOW TABLE <table> STATUS``

- suggest-family option contract (``callSuggest()``, ``callQSuggest()``,
  ``callAutocomplete()``):

  - options must be an associative array
  - option keys must be non-empty strings
  - values are quoted by the active connection (``quote()``/``quoteArr()``)
  - tested keys in this repository are ``limit`` (numeric) and ``fuzzy`` (numeric, autocomplete)

- capability behavior:

  - ``callQSuggest()`` and ``callAutocomplete()`` are feature-gated and may throw
    ``UnsupportedFeatureException`` when unsupported
  - ``callSuggest()`` is runtime-conditional; use ``supports('call_suggest')`` for
    portable code paths
