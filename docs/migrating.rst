Migrating to 4.0
================

This page summarizes migration from the 3.x line to 4.0.

Baseline Requirements
---------------------

- PHP 8.2+
- ``mysqli`` or ``pdo_mysql`` extension

Key Behavioral Changes
----------------------

4.0 introduces stricter runtime validation. Invalid query-shape input now throws
``OpenRegion\\SphinxQL\\Exception\\SphinxQLException`` instead of being coerced.

Builder validation highlights:

- unknown ``setType()`` values
- ``compile()`` without selecting a query type
- invalid ``from()`` input
- invalid ``facet()`` payload type
- invalid ``orderBy()`` / ``withinGroupOrderBy()`` direction
- invalid ``limit()`` / ``offset()`` / ``groupNBy()`` values
- invalid ``IN``/``NOT IN``/``BETWEEN`` value shapes
- missing ``into($index)`` for ``update()`` before compile/execute

Facet validation highlights:

- empty ``facet()``
- empty function/params in ``facetFunction()`` and ``orderByFunction()``
- invalid direction
- invalid ``limit()`` / ``offset()`` values

Helper validation highlights:

- required identifiers must be non-empty strings
- stricter ``setVariable()`` and CALL option validation
- helper feature-gated methods can raise ``UnsupportedFeatureException``

Percolate validation highlights:

- stricter payload checks for ``documents()`` and options
- earlier failure for unsupported/invalid document shapes

Exception Message Prefixes
--------------------------

Driver exceptions now include explicit source prefixes, for example:

- ``[mysqli][connect]...``
- ``[mysqli][query]...``
- ``[pdo][connect]...``
- ``[pdo][query]...``

Migration Checklist
-------------------

1. Validate user input before passing values to builder/helper methods.
2. Replace implicit coercion assumptions with explicit casting in your app layer.
3. Prefer exception class checks over full-message string equality checks.
4. Run integration tests against your target backend (Sphinx 2, Sphinx 3, or Manticore).
5. Add capability checks (``supports()``) for backend-specific helper calls.

Repository Source
-----------------

The canonical migration checklist also exists in ``MIGRATING-4.0.md`` at repository root.
