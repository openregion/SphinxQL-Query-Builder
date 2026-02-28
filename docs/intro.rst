.. _intro:

Introduction
============

SphinxQL Query Builder is a lightweight query builder for SphinxQL and ManticoreQL.
It supports both ``mysqli`` and ``PDO`` connection drivers and focuses on
predictable SQL generation with explicit runtime validation.

Compatibility
-------------

The 4.0 line targets:

- PHP 8.2+
- Sphinx 2.x
- Sphinx 3.x
- Manticore Search

Driver support:

- ``Foolz\\SphinxQL\\Drivers\\Mysqli\\Connection``
- ``Foolz\\SphinxQL\\Drivers\\Pdo\\Connection``

Runtime Contract
----------------

Starting with 4.0 pre-release hardening, invalid builder input fails fast with
``SphinxQLException`` rather than being silently coerced.

Examples:

- invalid query type in ``setType()``
- invalid order direction (must be ``ASC`` or ``DESC``)
- negative ``limit()`` / ``offset()``
- invalid ``WHERE/HAVING`` payload shapes for ``IN`` / ``BETWEEN``

See the migration guide for complete details.
