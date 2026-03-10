.. _intro:

Introduction
============

SphinxQL Query Builder provides a fluent PHP API for SphinxQL and ManticoreQL.

It is designed for teams that want:

- readable query composition
- safe value quoting/escaping via connection drivers
- testable SQL compilation (`compile()` + `getCompiled()`)
- helper wrappers for common engine commands

Supported drivers:

- ``OpenRegion\\SphinxQL\\Drivers\\Mysqli\\Connection``
- ``OpenRegion\\SphinxQL\\Drivers\\Pdo\\Connection``

Quick Example
-------------

.. code-block:: php

    <?php

    use OpenRegion\SphinxQL\Drivers\Mysqli\Connection;
    use OpenRegion\SphinxQL\SphinxQL;

    $conn = new Connection();
    $conn->setParams([
        'host' => '127.0.0.1',
        'port' => 9306,
    ]);

    $rows = (new SphinxQL($conn))
        ->select('id', 'gid', 'title')
        ->from('rt')
        ->match('title', 'vacation')
        ->where('gid', '>', 300)
        ->orderBy('id', 'DESC')
        ->limit(5)
        ->execute()
        ->getStored();

Compile-First Workflow
----------------------

Compiling queries before execution is useful for debugging and tests.

.. code-block:: php

    <?php

    use OpenRegion\SphinxQL\SphinxQL;

    $sql = (new SphinxQL($conn))
        ->select('a.id')
        ->from('rt a')
        ->leftJoin('rt b', 'a.id', '=', 'b.id')
        ->where('a.id', '>', 1)
        ->compile()
        ->getCompiled();

    // SELECT a.id FROM rt a LEFT JOIN rt b ON a.id = b.id WHERE a.id > 1

Where To Next
-------------

- :doc:`config` for connection parameters and driver notes
- :doc:`query-builder` for full query API with examples
- :doc:`helper` for SHOW/CALL/maintenance wrappers
- :doc:`features/percolate` for Manticore percolate workflows
