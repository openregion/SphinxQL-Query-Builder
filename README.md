# SphinxQL Query Builder

[![CI](https://github.com/FoolCode/SphinxQL-Query-Builder/actions/workflows/ci.yml/badge.svg)](https://github.com/FoolCode/SphinxQL-Query-Builder/actions/workflows/ci.yml)
[![Documentation](https://github.com/FoolCode/SphinxQL-Query-Builder/actions/workflows/docs.yml/badge.svg)](https://github.com/FoolCode/SphinxQL-Query-Builder/actions/workflows/docs.yml)
[![Latest Stable Version](https://poser.pugx.org/foolz/sphinxql-query-builder/v/stable)](https://packagist.org/packages/foolz/sphinxql-query-builder)
[![Total Downloads](https://poser.pugx.org/foolz/sphinxql-query-builder/downloads)](https://packagist.org/packages/foolz/sphinxql-query-builder)

A fluent PHP query builder for SphinxQL and ManticoreQL.

It supports:

- `SELECT`, `INSERT`, `REPLACE`, `UPDATE`, `DELETE`
- `MATCH()` building (including `MatchBuilder`)
- `FACET` queries
- batched/multi-queries
- helper commands (`SHOW`, `CALL`, maintenance operations)
- percolate workflows for Manticore
- both `mysqli` and `PDO` drivers

## Installation

```bash
composer require foolz/sphinxql-query-builder
```

Requirements:

- PHP 8.2+
- `mysqli` or `pdo_mysql`
- Running Sphinx Search or Manticore Search server

## Quick Start

```php
<?php

use Foolz\SphinxQL\Drivers\Mysqli\Connection;
use Foolz\SphinxQL\SphinxQL;

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
```

## Connection Setup

### `mysqli` driver

```php
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
```

### `PDO` driver

```php
<?php

use Foolz\SphinxQL\Drivers\Pdo\Connection;

$conn = new Connection();
$conn->setParams([
    'host' => '127.0.0.1',
    'port' => 9306,
    'charset' => 'utf8',
]);
```

## Query Builder Examples

### Compile SQL before executing

```php
<?php

use Foolz\SphinxQL\SphinxQL;

$sql = (new SphinxQL($conn))
    ->select('a.id')
    ->from('rt a')
    ->leftJoin('rt b', 'a.id', '=', 'b.id')
    ->where('a.id', '>', 1)
    ->compile()
    ->getCompiled();

// SELECT a.id FROM rt a LEFT JOIN rt b ON a.id = b.id WHERE a.id > 1
```

### Insert rows

```php
<?php

(new SphinxQL($conn))
    ->insert()
    ->into('rt')
    ->columns('id', 'gid', 'title', 'content')
    ->values(10, 9003, 'modifying the same line again', 'because i am that lazy')
    ->values(11, 201, 'replacing value by value', 'i have no idea who would use this directly')
    ->execute();
```

### Replace rows

```php
<?php

(new SphinxQL($conn))
    ->replace()
    ->into('rt')
    ->set([
        'id' => 10,
        'gid' => 9002,
        'title' => 'modified',
        'content' => 'this field was modified with replace',
    ])
    ->execute();
```

### Update rows (including MVA)

```php
<?php

(new SphinxQL($conn))
    ->update('rt')
    ->where('id', '=', 15)
    ->value('tags', [111, 222])
    ->execute();
```

### Delete rows

```php
<?php

$affected = (new SphinxQL($conn))
    ->delete()
    ->from('rt')
    ->where('id', 'IN', [11, 12, 13])
    ->match('content', 'content')
    ->execute()
    ->getStored();
```

### Grouped boolean filters

```php
<?php

$sql = (new SphinxQL($conn))
    ->select()
    ->from('rt')
    ->where('gid', 200)
    ->orWhereOpen()
    ->where('gid', 304)
    ->where('id', '>', 12)
    ->whereClose()
    ->compile()
    ->getCompiled();

// SELECT * FROM rt WHERE gid = 200 OR ( gid = 304 AND id > 12 )
```

### MATCH with builder callback

```php
<?php

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
```

### ORDER BY KNN

```php
<?php

$sql = (new SphinxQL($conn))
    ->select('id')
    ->from('rt')
    ->orderByKnn('embeddings', 5, [0.1, 0.2, 0.3])
    ->compile()
    ->getCompiled();

// SELECT id FROM rt ORDER BY KNN(embeddings, 5, [0.1,0.2,0.3]) ASC
```

### Subqueries

```php
<?php

$subquery = (new SphinxQL($conn))
    ->select('id')
    ->from('rt')
    ->orderBy('id', 'DESC');

$sql = (new SphinxQL($conn))
    ->select()
    ->from($subquery)
    ->orderBy('id', 'ASC')
    ->compile()
    ->getCompiled();

// SELECT * FROM (SELECT id FROM rt ORDER BY id DESC) ORDER BY id ASC
```

## Helper API Example

```php
<?php

use Foolz\SphinxQL\Helper;

$helper = new Helper($conn);

$tables = $helper->showTables()->execute()->getStored();
$variables = Helper::pairsToAssoc($helper->showVariables()->execute()->getStored());
$keywords = $helper->callKeywords('test case', 'rt', 1)->execute()->getStored();
```

Compile examples from tests:

- `$helper->showTables()->compile()->getCompiled()` -> `SHOW TABLES`
- `$helper->showTables('rt')->compile()->getCompiled()` -> `SHOW TABLES LIKE 'rt'`
- `$helper->showTableStatus()->compile()->getCompiled()` -> `SHOW TABLE STATUS`
- `$helper->showTableStatus('rt')->compile()->getCompiled()` -> `SHOW TABLE rt STATUS`
- `$helper->callSuggest('teh', 'rt', ['limit' => 5])->compile()->getCompiled()` -> `CALL SUGGEST('teh', 'rt', 5 AS limit)`

## FACET Example

```php
<?php

use Foolz\SphinxQL\Facet;
use Foolz\SphinxQL\SphinxQL;

$facet = (new Facet($conn))
    ->facet(['gid'])
    ->orderBy('gid', 'ASC');

$batchRows = (new SphinxQL($conn))
    ->select()
    ->from('rt')
    ->facet($facet)
    ->executeBatch()
    ->getStored();

// $batchRows[0] is SELECT data
// $batchRows[1] is FACET aggregation data
```

## Multi Query / Batch Example

```php
<?php

use Foolz\SphinxQL\Helper;
use Foolz\SphinxQL\SphinxQL;

$batch = (new SphinxQL($conn))
    ->select()
    ->from('rt')
    ->where('gid', 9003)
    ->enqueue()
    ->select()
    ->from('rt')
    ->where('gid', 201)
    ->enqueue((new Helper($conn))->showMeta())
    ->executeBatch();

$all = $batch->getStored();
```

## Percolate Example (Manticore)

```php
<?php

use Foolz\SphinxQL\Percolate;

(new Percolate($conn))
    ->insert('@subject orange')
    ->into('pq')
    ->tags(['tag2', 'tag3'])
    ->filter('price>3')
    ->execute();

$matches = (new Percolate($conn))
    ->callPQ()
    ->from('pq')
    ->documents(['{"subject":"document about orange"}'])
    ->options([
        Percolate::OPTION_QUERY => 1,
        Percolate::OPTION_DOCS => 1,
    ])
    ->execute()
    ->fetchAllAssoc();
```

## Capability Checks

```php
<?php

use Foolz\SphinxQL\Helper;

$helper = new Helper($conn);
$caps = $helper->getCapabilities();

if ($helper->supports('call_autocomplete')) {
    $rows = $helper->callAutocomplete('te', 'rt', ['fuzzy' => 1])->execute()->getStored();
}
```

## Result Objects

`execute()` returns `ResultSetInterface`:

- `getStored()`
- `fetchAllAssoc()`
- `fetchAllNum()`
- `fetchAssoc()`
- `fetchNum()`
- `getAffectedRows()`

`executeBatch()` returns `MultiResultSetInterface`:

- `getStored()`
- `getNext()`

## Documentation Map

- Main docs index: [`docs/index.rst`](docs/index.rst)
- Builder guide: [`docs/query-builder.rst`](docs/query-builder.rst)
- Helper guide: [`docs/helper.rst`](docs/helper.rst)
- Facets: [`docs/features/facet.rst`](docs/features/facet.rst)
- Multi-query: [`docs/features/multi-query-builder.rst`](docs/features/multi-query-builder.rst)
- Migration guide: [`docs/migrating.rst`](docs/migrating.rst)

## Running Tests

```bash
./scripts/run-tests-docker.sh
```

This runs the repository test matrix (mysqli + pdo) in Docker.

## Contributing

Pull requests are welcome. Please include tests for behavior changes and keep docs in sync with API updates.
