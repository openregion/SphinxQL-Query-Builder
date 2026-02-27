Helper API
==========

The ``Helper`` class exposes convenience wrappers for SphinxQL statements that
do not need fluent query composition.

Usage
-----

.. code-block:: php

    <?php

    use Foolz\SphinxQL\Helper;

    $helper = new Helper($conn);
    $rows = $helper->showVariables()->execute()->getStored();

Available Methods
-----------------

- ``showMeta()``
- ``showWarnings()``
- ``showStatus()``
- ``showProfile()``
- ``showPlan()``
- ``showThreads()``
- ``showVersion()``
- ``showPlugins()``
- ``showAgentStatus()``
- ``showScroll()``
- ``showDatabases()``
- ``showCharacterSet()``
- ``showCollation()``
- ``showTables($index)``
- ``showVariables()``
- ``showCreateTable($table)``
- ``showTableStatus($table = null)``
- ``showTableSettings($table)``
- ``showTableIndexes($table)``
- ``showQueries()``
- ``setVariable($name, $value, $global = false)``
- ``callSnippets($data, $index, $query, array $options = array())``
- ``callKeywords($text, $index, $hits = null)``
- ``callSuggest($text, $index, array $options = array())``
- ``callQSuggest($text, $index, array $options = array())``
- ``callAutocomplete($text, $index, array $options = array())``
- ``describe($index)``
- ``createFunction($udfName, $returns, $soName)``
- ``dropFunction($udfName)``
- ``attachIndex($diskIndex, $rtIndex)``
- ``flushRtIndex($index)``
- ``truncateRtIndex($index)``
- ``optimizeIndex($index)``
- ``showIndexStatus($index)``
- ``flushRamchunk($index)``
- ``flushAttributes()``
- ``flushHostnames()``
- ``flushLogs()``
- ``reloadPlugins()``
- ``kill($queryId)``
- ``getCapabilities()``
- ``supports($feature)``
- ``requireSupport($feature, $context = '')``

Filtered SHOW Wrappers
----------------------

- ``showTables($index)`` compiles to ``SHOW TABLES LIKE <quoted index>``.
- ``showTableStatus($table = null)`` compiles to:

  - ``SHOW TABLE STATUS`` when ``$table`` is ``null``
  - ``SHOW TABLE <table> STATUS`` when ``$table`` is a non-empty string

Suggest-Family Option Contract
------------------------------

For ``callSuggest()``, ``callQSuggest()``, and ``callAutocomplete()``:

- ``$options`` must be an associative array.
- Option keys must be non-empty strings; each option is compiled as
  ``<quoted_value> AS <key>``.
- Option values are quoted via the active connection driver
  (``quote()``/``quoteArr()``), which supports scalar values, ``null``,
  ``Expression``, and arrays.
- Repository-tested option keys are ``limit`` (numeric) and ``fuzzy``
  (numeric, autocomplete).

Validation Notes
----------------

In 4.0, helper methods validate required identifiers and input shapes and throw
``SphinxQLException`` on invalid arguments.

``callQSuggest()`` and ``callAutocomplete()`` are feature-gated and may throw
``UnsupportedFeatureException`` when unsupported. ``callSuggest()`` is not
pre-gated; use ``supports('call_suggest')`` when runtime portability is needed.
