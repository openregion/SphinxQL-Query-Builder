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

Validation Notes
----------------

In 4.0, helper methods validate required identifiers and input shapes and throw
``SphinxQLException`` on invalid arguments.

Feature-gated helper methods may throw ``UnsupportedFeatureException`` when the
current engine/runtime does not support that command family.
