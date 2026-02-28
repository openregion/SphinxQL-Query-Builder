.. _config:

Configuration
=============

Driver Setup
------------

MySQLi driver:

.. code-block:: php

    <?php

    use Foolz\SphinxQL\Drivers\Mysqli\Connection;

    $conn = new Connection();
    $conn->setParams([
        'host' => '127.0.0.1',
        'port' => 9306,
        'options' => [
            MYSQLI_OPT_CONNECT_TIMEOUT => 2,
        ],
    ]);

PDO driver:

.. code-block:: php

    <?php

    use Foolz\SphinxQL\Drivers\Pdo\Connection;

    $conn = new Connection();
    $conn->setParams([
        'host' => '127.0.0.1',
        'port' => 9306,
        'charset' => 'utf8',
    ]);

Connection Parameters
---------------------

``setParams()`` and ``setParam()`` support:

- ``host`` (string, default ``127.0.0.1``)
- ``port`` (int, default ``9306``)
- ``socket`` (string|null)
- ``username`` (string|null)
- ``password`` (string|null)
- ``charset`` (PDO DSN option)
- ``options`` (array, driver-specific options)

Notes:

- Setting ``host`` to ``localhost`` is normalized to ``127.0.0.1``.
- Socket notation like ``unix:/path/to/socket`` is converted to ``socket``.

Escaping and Quoting
--------------------

Value escaping and quoting are connection-driven.

.. code-block:: php

    <?php

    $quotedText = $conn->quote('hello');      // 'hello'
    $quotedInt = $conn->quote(42);            // 42
    $quotedNull = $conn->quote(null);         // null
    $quotedList = $conn->quote([1, 2, 3]);    // (1,2,3)

For raw SQL fragments, use ``SphinxQL::expr()``.

.. code-block:: php

    <?php

    use Foolz\SphinxQL\SphinxQL;

    $sql = (new SphinxQL($conn))
        ->select()
        ->from('rt')
        ->option('field_weights', SphinxQL::expr('(title=80, content=35)'))
        ->compile()
        ->getCompiled();

    // SELECT * FROM rt OPTION field_weights = (title=80, content=35)
