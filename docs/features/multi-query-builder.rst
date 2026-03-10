Multi-Query Builder
===================

Use ``enqueue()`` + ``executeBatch()`` to send multiple statements in one roundtrip.

Basic Batch
-----------

.. code-block:: php

    <?php

    use OpenRegion\SphinxQL\SphinxQL;

    $batch = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->where('gid', 9003)
        ->enqueue()
        ->select()
        ->from('rt')
        ->where('gid', 201)
        ->executeBatch();

    $sets = $batch->getStored();
    // $sets[0] => first SELECT rows
    // $sets[1] => second SELECT rows

Mixing Helper Calls in Batch
----------------------------

.. code-block:: php

    <?php

    use OpenRegion\SphinxQL\Helper;
    use OpenRegion\SphinxQL\SphinxQL;

    $result = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->where('gid', 9003)
        ->enqueue()
        ->select()
        ->from('rt')
        ->where('gid', 201)
        ->enqueue((new Helper($conn))->showMeta())
        ->executeBatch()
        ->getStored();

    // Tests assert:
    // $result[0][0]['id'] == '10'
    // $result[1][0]['id'] == '11'
    // $result[2][0]['Value'] == '1'

Queue Behavior
--------------

- ``enqueue()`` with no argument returns a new ``SphinxQL`` linked to the current query.
- ``enqueue($next)`` links the current query to the provided ``SphinxQL`` instance.
- ``getQueue()`` returns ordered queued query objects.

If no query was queued, ``executeBatch()`` throws ``SphinxQLException``.

Consuming Results with Cursors
------------------------------

``MultiResultSetInterface`` supports ``getNext()`` for sequential processing.

.. code-block:: php

    $multi = $query->executeBatch();

    while ($set = $multi->getNext()) {
        $rows = $set->getStored();
        // process each result set
    }
