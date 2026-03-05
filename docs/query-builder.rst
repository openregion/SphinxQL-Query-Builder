SphinxQL Query Builder
======================

Creating a Builder
------------------

.. code-block:: php

    <?php

    use OpenRegion\SphinxQL\Drivers\Mysqli\Connection;
    use OpenRegion\SphinxQL\SphinxQL;

    $conn = new Connection();
    $conn->setParams(['host' => '127.0.0.1', 'port' => 9306]);

    $sq = new SphinxQL($conn);

Supported Query Types
---------------------

- ``select()``
- ``insert()``
- ``replace()``
- ``update()``
- ``delete()``
- ``query($sql)`` for raw statements

SELECT
------

The `OpenRegion\\SphinxQL\\SphinxQL` class supports building the following queries: `SELECT`, `INSERT`, `UPDATE`, and `DELETE`. Which sort of query being generated depends on the methods called.
Basic select:

.. code-block:: php

    $rows = (new SphinxQL($conn))
        ->select('id', 'gid')
        ->from('rt')
        ->execute()
        ->getStored();

No explicit columns defaults to ``*``.

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->compile()
        ->getCompiled();

    // SELECT * FROM rt

FROM Variants
-------------

Multiple indexes:

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->select('id')
        ->from('rt_main', 'rt_delta')
        ->compile()
        ->getCompiled();

Array input:

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->select('id')
        ->from(['rt_main', 'rt_delta'])
        ->compile()
        ->getCompiled();

Subquery as closure:

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->select()
        ->from(function ($q) {
            $q->select('id')
              ->from('rt')
              ->orderBy('id', 'DESC');
        })
        ->orderBy('id', 'ASC')
        ->compile()
        ->getCompiled();

    // SELECT * FROM (SELECT id FROM rt ORDER BY id DESC) ORDER BY id ASC

MATCH
-----

Simple full-text match:

.. code-block:: php

    $rows = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->match('content', 'content')
        ->execute()
        ->getStored();

Multiple ``match()`` calls are combined:

.. code-block:: php

    $rows = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->match('title', 'value')
        ->match('content', 'directly')
        ->execute()
        ->getStored();

Array field list:

.. code-block:: php

    $rows = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->match(['title', 'content'], 'to')
        ->execute()
        ->getStored();

Half-escape mode (lets operators like ``|`` pass through):

.. code-block:: php

    $rows = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->match('content', 'directly | lazy', true)
        ->execute()
        ->getStored();

Use ``MatchBuilder`` callback for advanced expressions:

.. code-block:: php

    $rows = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->match(function ($m) {
            $m->field('content')
              ->match('directly')
              ->orMatch('lazy');
        })
        ->execute()
        ->getStored();

WHERE
-----

Supported styles:

.. code-block:: php

    $sq->where('gid', 304);                        // gid = 304
    $sq->where('gid', '>', 300);                   // gid > 300
    $sq->where('id', 'IN', [11, 12, 13]);          // id IN (...)
    $sq->where('gid', 'BETWEEN', [300, 400]);      // gid BETWEEN ...

Grouped boolean clauses:

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->where('gid', 200)
        ->orWhereOpen()
        ->where('gid', 304)
        ->where('id', '>', 12)
        ->whereClose()
        ->compile()
        ->getCompiled();

    // SELECT * FROM rt WHERE gid = 200 OR ( gid = 304 AND id > 12 )

HAVING
------

``HAVING`` mirrors the ``WHERE`` API, including grouping.

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->select('gid')
        ->from('rt')
        ->groupBy('gid')
        ->having('gid', '>', 100)
        ->orHavingOpen()
        ->having('gid', '<', 10)
        ->having('gid', '>', 9000)
        ->havingClose()
        ->compile()
        ->getCompiled();

    // SELECT gid FROM rt GROUP BY gid HAVING gid > 100 OR ( gid < 10 AND gid > 9000 )

JOIN
----

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->select('a.id')
        ->from('rt a')
        ->leftJoin('rt b', 'a.id', '=', 'b.id')
        ->where('a.id', '>', 1)
        ->compile()
        ->getCompiled();

    // SELECT a.id FROM rt a LEFT JOIN rt b ON a.id = b.id WHERE a.id > 1

Cross join:

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->select('a.id')
        ->from('rt a')
        ->crossJoin('rt b')
        ->compile()
        ->getCompiled();

    // SELECT a.id FROM rt a CROSS JOIN rt b

GROUP / ORDER / LIMIT / OPTION
------------------------------

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->groupBy('gid')
        ->groupNBy(3)
        ->withinGroupOrderBy('id', 'DESC')
        ->orderBy('id', 'ASC')
        ->limit(0, 20)
        ->compile()
        ->getCompiled();

``orderByKnn()``:

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->select('id')
        ->from('rt')
        ->orderByKnn('embeddings', 5, [0.1, 0.2, 0.3])
        ->compile()
        ->getCompiled();

    // SELECT id FROM rt ORDER BY KNN(embeddings, 5, [0.1,0.2,0.3]) ASC

Options:

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->option('comment', 'this should be quoted')
        ->compile()
        ->getCompiled();

    // SELECT * FROM rt OPTION comment = 'this should be quoted'

Array option values are compiled as ``(key=value, ...)``:

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->option('field_weights', [
            'title' => 80,
            'content' => 35,
            'tags' => 92,
        ])
        ->compile()
        ->getCompiled();

    // SELECT * FROM rt OPTION field_weights = (title=80, content=35, tags=92)

INSERT / REPLACE
----------------

``set()`` style:

.. code-block:: php

    (new SphinxQL($conn))
        ->insert()
        ->into('rt')
        ->set([
            'id' => 10,
            'gid' => 9001,
            'title' => 'the story of a long test unit',
            'content' => 'once upon a time there was a foo in the bar',
        ])
        ->execute();

``columns()`` + ``values()`` style:

.. code-block:: php

    (new SphinxQL($conn))
        ->replace()
        ->into('rt')
        ->columns('id', 'title', 'content', 'gid')
        ->values(10, 'modifying the same line again', 'because i am that lazy', 9003)
        ->values(11, 'i am getting really creative with these strings', "i'll need them to test MATCH!", 300)
        ->execute();

UPDATE
------

Standard update:

.. code-block:: php

    $affected = (new SphinxQL($conn))
        ->update('rt')
        ->where('id', '=', 11)
        ->value('gid', 201)
        ->execute()
        ->getStored();

Late ``into()`` (from tests):

.. code-block:: php

    $sql = (new SphinxQL($conn))
        ->update()
        ->into('rt')
        ->set(['gid' => 777])
        ->where('id', '=', 11)
        ->compile()
        ->getCompiled();

    // UPDATE rt SET gid = 777 WHERE id = 11

MVA update:

.. code-block:: php

    (new SphinxQL($conn))
        ->update('rt')
        ->where('id', '=', 15)
        ->value('tags', [111, 222])
        ->execute();

DELETE
------

.. code-block:: php

    $affected = (new SphinxQL($conn))
        ->delete()
        ->from('rt')
        ->where('id', 'IN', [11, 12, 13])
        ->match('content', 'content')
        ->execute()
        ->getStored();

Raw Query
---------

.. code-block:: php

    $rows = (new SphinxQL($conn))
        ->query('DESCRIBE rt')
        ->execute()
        ->getStored();

Transactions
------------

.. code-block:: php

    $sq = new SphinxQL($conn);

    $sq->transactionBegin();
    // write operations
    $sq->transactionCommit();

    // or
    $sq->transactionRollback();

Reset Methods
-------------

You can reuse a builder and selectively clear parts of the query:

- ``resetWhere()``
- ``resetJoins()``
- ``resetMatch()``
- ``resetGroupBy()``
- ``resetWithinGroupOrderBy()``
- ``resetHaving()``
- ``resetOrderBy()``
- ``resetOptions()``
- ``resetFacets()``

Result Objects
--------------

``execute()`` returns ``ResultSetInterface`` with methods such as:

- ``getStored()``
- ``fetchAllAssoc()``
- ``fetchAllNum()``
- ``fetchAssoc()``
- ``fetchNum()``
- ``getAffectedRows()``

For batch execution see :doc:`features/multi-query-builder`.
