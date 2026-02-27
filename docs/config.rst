.. _config:

Configuration
=============

Creating a Connection
---------------------

Use one of the supported drivers:

.. code-block:: php

    <?php

    use Foolz\SphinxQL\Drivers\Mysqli\Connection;

    $conn = new Connection();
    $conn->setParams(array(
        'host' => '127.0.0.1',
        'port' => 9306,
    ));

You can also use the PDO driver:

.. code-block:: php

    <?php

    use Foolz\SphinxQL\Drivers\Pdo\Connection;

    $conn = new Connection();
    $conn->setParams(array(
        'host' => '127.0.0.1',
        'port' => 9306,
    ));

Connection Parameters
---------------------

``setParams()`` and ``setParam()`` accept:

- ``host`` (string, default ``127.0.0.1``)
- ``port`` (int, default ``9306``)
- ``socket`` (string|null, default ``null``)
- ``options`` (array, driver-specific client options)

Strict Validation Notes
-----------------------

The query builder validates critical inputs at runtime in 4.0.
Prefer explicit values over implicit coercion.
