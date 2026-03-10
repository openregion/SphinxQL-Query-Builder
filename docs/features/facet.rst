Facets
======

``Facet`` builds ``FACET`` clauses for grouped aggregations.

Building a Facet
----------------

.. code-block:: php

    <?php

    use Foolz\SphinxQL\Facet;

    $facet = (new Facet($conn))
        ->facet(['gid'])
        ->orderBy('gid', 'ASC');

Common compiled outputs from tests:

- ``facet(['gid'])`` -> ``FACET gid``
- ``facet(['gid', 'title', 'content'])`` -> ``FACET gid, title, content``
- ``facet(['alias' => 'gid'])`` -> ``FACET gid AS alias``
- ``facetFunction('COUNT', 'gid')`` -> ``FACET COUNT(gid)``
- ``facetFunction('INTERVAL', ['price', 200, 400, 600, 800])`` -> ``FACET INTERVAL(price,200,400,600,800)``

Using FACET with SELECT
-----------------------

FACET is returned as an extra result set, so use ``executeBatch()``.

.. code-block:: php

    <?php

    use Foolz\SphinxQL\Facet;
    use Foolz\SphinxQL\SphinxQL;

    $batch = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->facet(
            (new Facet($conn))
                ->facet(['gid'])
                ->orderBy('gid', 'ASC')
        )
        ->executeBatch()
        ->getStored();

    // $batch[0] => SELECT rows
    // $batch[1] => FACET rows with gid + count(*)

Advanced Facet Options
----------------------

Add ``BY``:

.. code-block:: php

    $facet = (new Facet($conn))
        ->facet(['gid', 'title', 'content'])
        ->by('gid');

Sort by expression:

.. code-block:: php

    $facet = (new Facet($conn))
        ->facet(['gid', 'title'])
        ->orderByFunction('COUNT', '*', 'DESC');

Paginate facet rows:

.. code-block:: php

    $facet = (new Facet($conn))
        ->facet(['gid', 'title'])
        ->orderByFunction('COUNT', '*', 'DESC')
        ->limit(5, 5);

Validation Notes
----------------

``Facet`` throws ``SphinxQLException`` for invalid inputs such as:

- empty ``facet()`` columns
- invalid order directions
- invalid ``limit()`` / ``offset()``
- ``facetFunction()`` without parameters
