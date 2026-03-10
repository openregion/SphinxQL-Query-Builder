Percolate (Manticore)
=====================

``Percolate`` supports storing queries and matching incoming documents via ``CALL PQ``.

Store Queries in a Percolate Index
----------------------------------

.. code-block:: php

    <?php

    use OpenRegion\SphinxQL\Percolate;

    $pq = (new Percolate($conn))
        ->insert('@subject orange')
        ->into('pq')
        ->tags(['tag2', 'tag3'])
        ->filter('price>3')
        ->execute();

Compiled SQL pattern (from tests):

- ``INSERT INTO pq (query) VALUES ('full text query terms')``
- ``INSERT INTO pq (query, tags) VALUES ('@subject orange', 'tag2,tag3')``
- ``INSERT INTO pq (query, filters) VALUES ('catch me', 'price>3')``
- ``INSERT INTO pq (query, tags, filters) VALUES ('@subject match by field', 'tag2,tag3', 'price>3')``

Run ``CALL PQ``
---------------

.. code-block:: php

    <?php

    use OpenRegion\SphinxQL\Percolate;

    $rows = (new Percolate($conn))
        ->callPQ()
        ->from('pq')
        ->documents(['{"subject":"document about orange"}'])
        ->options([
            Percolate::OPTION_QUERY => 1,
            Percolate::OPTION_DOCS => 1,
        ])
        ->execute()
        ->fetchAllAssoc();

Document Input Shapes
---------------------

Supported ``documents()`` inputs include:

- plain string
- array of plain strings
- JSON object string
- JSON array of objects string
- array of JSON strings
- associative PHP array (converted to JSON)
- array of associative PHP arrays

Percolate Options
-----------------

Constants:

- ``Percolate::OPTION_DOCS_JSON``
- ``Percolate::OPTION_DOCS``
- ``Percolate::OPTION_VERBOSE``
- ``Percolate::OPTION_QUERY``

Each option value must be ``0`` or ``1``.

Validation Notes
----------------

``Percolate`` throws ``SphinxQLException`` for invalid payloads, for example:

- empty index
- empty query/document
- invalid document shape when JSON mode is enforced
- unknown option names
- option values outside ``0``/``1``
