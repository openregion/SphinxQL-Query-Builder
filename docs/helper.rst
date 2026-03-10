Helper API
==========

``Helper`` wraps common statements that are awkward as fluent builders.

.. code-block:: php

    <?php

    use Foolz\SphinxQL\Helper;

    $helper = new Helper($conn);
    $rows = $helper->showVariables()->execute()->getStored();

Core Patterns
-------------

Every helper method returns a ``SphinxQL`` instance, so you can:

- inspect SQL with ``compile()->getCompiled()``
- run immediately with ``execute()``
- enqueue into a batch with ``enqueue()``

Example:

.. code-block:: php

    $sql = $helper->showTables('rt')->compile()->getCompiled();
    // SHOW TABLES LIKE 'rt'

SHOW Commands
-------------

Frequently used:

.. code-block:: php

    $helper->showMeta();
    $helper->showWarnings();
    $helper->showStatus();
    $helper->showVariables();
    $helper->showTables();
    $helper->showCreateTable('rt');

Table introspection:

.. code-block:: php

    $helper->showTableStatus();
    $helper->showTableStatus('rt');
    $helper->showTableSettings('rt');
    $helper->showTableIndexes('rt');

Compile outputs from tests:

- ``showMeta()`` -> ``SHOW META``
- ``showWarnings()`` -> ``SHOW WARNINGS``
- ``showStatus()`` -> ``SHOW STATUS``
- ``showTableStatus()`` -> ``SHOW TABLE STATUS``
- ``showTableStatus('rt')`` -> ``SHOW TABLE rt STATUS``
- ``showTableSettings('rt')`` -> ``SHOW TABLE rt SETTINGS``
- ``showTableIndexes('rt')`` -> ``SHOW TABLE rt INDEXES``

Maintenance and Runtime Commands
--------------------------------

.. code-block:: php

    $helper->attachIndex('disk', 'rt');
    $helper->flushRtIndex('rt');
    $helper->truncateRtIndex('rt');
    $helper->optimizeIndex('rt');
    $helper->showIndexStatus('rt');
    $helper->flushRamchunk('rt');
    $helper->flushAttributes();
    $helper->flushHostnames();
    $helper->flushLogs();
    $helper->reloadPlugins();
    $helper->kill(123);

Selected compiled SQL from tests:

- ``attachIndex('disk', 'rt')`` -> ``ATTACH INDEX disk TO RTINDEX rt``
- ``flushRtIndex('rt')`` -> ``FLUSH RTINDEX rt``
- ``optimizeIndex('rt')`` -> ``OPTIMIZE INDEX rt``
- ``showIndexStatus('rt')`` -> ``SHOW INDEX rt STATUS``
- ``flushRamchunk('rt')`` -> ``FLUSH RAMCHUNK rt``
- ``kill(123)`` -> ``KILL 123``

CALL Helpers
------------

``CALL SNIPPETS``:

.. code-block:: php

    $snippets = $helper->callSnippets(
        'this is my document text',
        'rt',
        'is',
        ['before_match' => '<em>', 'after_match' => '</em>']
    )->execute()->getStored();

``CALL KEYWORDS``:

.. code-block:: php

    $keywords = $helper->callKeywords('test case', 'rt', 1)
        ->execute()
        ->getStored();

Suggest-family methods:

- ``callSuggest($text, $index, $options = [])``
- ``callQSuggest($text, $index, $options = [])``
- ``callAutocomplete($text, $index, $options = [])``

Compiled outputs from tests:

.. code-block:: php

    $helper->callSuggest('teh', 'rt', [
        'limit' => 5,
        'result_stats' => true,
        'search_mode' => 'WORDS',
    ])->compile()->getCompiled();

    // CALL SUGGEST('teh', 'rt', 5 AS limit, 1 AS result_stats, 'words' AS search_mode)

.. code-block:: php

    $helper->callQSuggest('teh', 'rt', [
        'limit' => 3,
        'result_line' => false,
    ])->compile()->getCompiled();

    // CALL QSUGGEST('teh', 'rt', 3 AS limit, 0 AS result_line)

.. code-block:: php

    $helper->callAutocomplete('te', 'rt', [
        'fuzzy' => 1,
        'append' => true,
        'preserve' => false,
    ])->compile()->getCompiled();

    // CALL AUTOCOMPLETE('te', 'rt', 1 AS fuzzy, 1 AS append, 0 AS preserve)

Suggest Option Schemas
----------------------

``callSuggest()`` and ``callQSuggest()`` allowed options:

- ``limit`` (int >= 0)
- ``max_edits`` (int >= 0)
- ``result_stats`` (bool)
- ``delta_len`` (int >= 0)
- ``max_matches`` (int >= 0)
- ``reject`` (bool)
- ``result_line`` (bool)
- ``non_char`` (bool)
- ``sentence`` (bool)
- ``force_bigrams`` (bool)
- ``search_mode`` (``phrase`` or ``words``)

``callAutocomplete()`` allowed options:

- ``layouts`` (string)
- ``fuzzy`` (int 0..2)
- ``fuzziness`` (int 0..2)
- ``prepend`` (bool)
- ``append`` (bool)
- ``preserve`` (bool)
- ``expansion_len`` (int >= 0)
- ``force_bigrams`` (bool)

Capability-Aware Usage
----------------------

Use capability checks before engine-dependent calls.

.. code-block:: php

    $caps = $helper->getCapabilities();

    if ($helper->supports('call_autocomplete')) {
        $rows = $helper->callAutocomplete('te', 'rt', ['fuzzy' => 1])
            ->execute()
            ->getStored();
    }

    // throws UnsupportedFeatureException when unavailable
    $helper->requireSupport('call_qsuggest', 'search suggestions');

``getCapabilities()`` reports:

- engine (``MANTICORE``, ``SPHINX2``, ``SPHINX3``, ``UNKNOWN``)
- version string
- feature map

Pairs Utility
-------------

Convert key-value rows from SHOW commands into associative arrays.

.. code-block:: php

    $pairs = Helper::pairsToAssoc($helper->showVariables()->execute()->getStored());
    $autocommit = (int) ($pairs['autocommit'] ?? 1);

Validation Behavior
-------------------

Helper methods validate required identifiers and option shapes.
Examples that throw ``SphinxQLException``:

- empty/invalid index names
- unknown suggest option keys
- invalid option types/ranges
- non-positive ``kill()`` query ID
