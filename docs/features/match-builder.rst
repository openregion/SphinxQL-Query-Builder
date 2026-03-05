MatchBuilder
============

``MatchBuilder`` helps compose advanced ``MATCH()`` expressions.

Use it directly, or pass a callback to ``SphinxQL::match()``.

Standalone Build
----------------

.. code-block:: php

    <?php

    use OpenRegion\OpenRegionSphinxQL\MatchBuilder;
    use OpenRegion\SphinxQL\SphinxQL;

    $sq = new SphinxQL($conn);

    $match = (new MatchBuilder($sq))
        ->field('content')
        ->match('directly')
        ->orMatch('lazy')
        ->compile()
        ->getCompiled();

    // @content directly | lazy

Inline with Query Builder
-------------------------

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

Real Compiled Examples from Tests
---------------------------------

- ``match('test case')`` -> ``(test case)``
- ``match('test')->orMatch('case')`` -> ``test | case``
- ``phrase('test case')`` -> ``"test case"``
- ``proximity('test case', 5)`` -> ``"test case"~5``
- ``quorum('this is a test case', 3)`` -> ``"this is a test case"/3``
- ``field('body', 50)->match('test')`` -> ``@body[50] test``
- ``ignoreField('title', 'body')->match('test')`` -> ``@!(title,body) test``
- ``zone(['h3', 'h4'])`` -> ``ZONE:(h3,h4)``
- ``zonespan('th', 'test')`` -> ``ZONESPAN:(th) test``

Expressions and Escaping
------------------------

Raw expression bypass:

.. code-block:: php

    use OpenRegion\SphinxQL\SphinxQL;

    $expr = (new MatchBuilder($sq))
        ->match(SphinxQL::expr('test|case'))
        ->compile()
        ->getCompiled();

    // test|case

Without ``Expression``, special characters are escaped.
