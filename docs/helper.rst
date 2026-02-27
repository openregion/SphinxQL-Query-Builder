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
- ``showTables($index)``
- ``showVariables()``
- ``setVariable($name, $value, $global = false)``
- ``callSnippets($data, $index, $query, array $options = array())``
- ``callKeywords($text, $index, $hits = null)``
- ``describe($index)``
- ``createFunction($udfName, $returns, $soName)``
- ``dropFunction($udfName)``
- ``attachIndex($diskIndex, $rtIndex)``
- ``flushRtIndex($index)``
- ``truncateRtIndex($index)``
- ``optimizeIndex($index)``
- ``showIndexStatus($index)``
- ``flushRamchunk($index)``

Validation Notes
----------------

In 4.0, helper methods validate required identifiers and input shapes and throw
``SphinxQLException`` on invalid arguments.
