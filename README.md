# SphinxQL Query Builder

[![CI](https://github.com/FoolCode/SphinxQL-Query-Builder/actions/workflows/ci.yml/badge.svg)](https://github.com/FoolCode/SphinxQL-Query-Builder/actions/workflows/ci.yml)
[![Documentation](https://github.com/FoolCode/SphinxQL-Query-Builder/actions/workflows/docs.yml/badge.svg)](https://github.com/FoolCode/SphinxQL-Query-Builder/actions/workflows/docs.yml)
[![Latest Stable Version](https://poser.pugx.org/foolz/sphinxql-query-builder/v/stable)](https://packagist.org/packages/foolz/sphinxql-query-builder)
[![Total Downloads](https://poser.pugx.org/foolz/sphinxql-query-builder/downloads)](https://packagist.org/packages/foolz/sphinxql-query-builder)
[![Latest Stable Version](https://poser.pugx.org/openregion/sphinxql-query-builder/v/stable)](https://packagist.org/packages/openregion/sphinxql-query-builder)
[![Total Downloads](https://poser.pugx.org/openregion/sphinxql-query-builder/downloads)](https://packagist.org/packages/openregion/sphinxql-query-builder)

## About

This a fork of [FoolCode's SphinxQL Query Builder](https://github.com/FoolCode/SphinxQL-Query-Builder). It seems like original one is no longer maintained.

This is a SphinxQL Query Builder used to work with SphinxQL, a SQL dialect used with the Sphinx search engine and it's fork Manticore. It maps most of the functions listed in the [SphinxQL reference](http://sphinxsearch.com/docs/current.html#SphinxQL-reference) and is generally [faster](http://sphinxsearch.com/blog/2010/04/25/sphinxapi-vs-SphinxQL-benchmark/) than the available Sphinx API.

This Query Builder has no dependencies except PHP 7.4 or later, `\MySQLi` extension, `PDO`, and [Sphinx](http://sphinxsearch.com)/[Manticore](https://manticoresearch.com).

### Missing methods?

SphinxQL evolves very fast.

Most of the new functions are static one liners like `SHOW PLUGINS`. We'll avoid trying to keep up with these methods, as they are easy to just call directly (`(new SphinxQL($conn))->query($sql)->execute()`). You're free to submit pull requests to support these methods.

If any feature is unreachable through this library, open a new issue or send a pull request.

## Code Quality

The majority of the methods in the package have been unit tested.

The only methods that have not been fully tested are the Helpers, which are mostly simple shorthands for SQL strings.

## How to Contribute

### Pull Requests

1. Fork the SphinxQL Query Builder repository
2. Create a new branch for each feature or improvement
3. Submit a pull request from each branch to the **master** branch

It is very important to separate new features or improvements into separate feature branches, and to send a pull
request for each branch. This allows me to review and pull in new features or improvements individually.

### Style Guide

All pull requests must adhere to the [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) standard.
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
composer require openregion/sphinxql-query-builder
```
This is a Composer package. You can install this package with the following command: `composer require openregion/sphinxql-query-builder`

Requirements:

- PHP 8.2+
- `mysqli` or `pdo_mysql`
- Running Sphinx Search or Manticore Search server

## Quick Start

```php
<?php
use OpenRegion\SphinxQL\SphinxQL;
use OpenRegion\SphinxQL\Drivers\Mysqli\Connection;

use OpenRegion\SphinxQL\Drivers\Mysqli\Connection;
use OpenRegion\SphinxQL\SphinxQL;

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

use OpenRegion\SphinxQL\Drivers\Mysqli\Connection;

$conn = new Connection();
$conn->setParams([
    'host' => '127.0.0.1',
    'port' => 9306,
    'options' => [
        MYSQLI_OPT_CONNECT_TIMEOUT => 2,
    ],
]);
```

### Drivers

We support the following database connection drivers:

* OpenRegion\SphinxQL\Drivers\Mysqli\Connection
* OpenRegion\SphinxQL\Drivers\Pdo\Connection

### Connection

* __$conn = new Connection()__

	Create a new Connection instance to be used with the following methods or SphinxQL class.

* __$conn->setParams($params = array('host' => '127.0.0.1', 'port' => 9306))__

	Sets the connection parameters used to establish a connection to the server. Supported parameters: 'host', 'port', 'socket', 'options'.

* __$conn->query($query)__

	Performs the query on the server. Returns a [`ResultSet`](#resultset) object containing the query results.

_More methods are available in the Connection class, but usually not necessary as these are handled automatically._

### SphinxQL

* __new SphinxQL($conn)__

	Creates a SphinxQL instance used for generating queries.

#### Bypass Query Escaping

Often, you would need to call and run SQL functions that shouldn't be escaped in the query. You can bypass the query escape by wrapping the query in an `\Expression`.

* __SphinxQL::expr($string)__

	Returns the string without being escaped.

#### Query Escaping

There are cases when an input __must__ be escaped in the SQL statement. The following functions are used to handle any escaping required for the query.

* __$conn->escape($value)__

	Returns the escaped value. This is processed with the `\MySQLi::real_escape_string()` function.

* __$conn->quoteIdentifier($identifier)__

	Adds backtick quotes to the identifier. For array elements, use `$conn->quoteIdentifierArray($arr)`.

* __$conn->quote($value)__

	Adds quotes to the value and escapes it. For array elements, use `$conn->quoteArr($arr)`.

* __$sq->escapeMatch($value)__

	Escapes the string to be used in `MATCH`.

* __$sq->halfEscapeMatch($value)__

	Escapes the string to be used in `MATCH`. The following characters are allowed: `-`, `|`, and `"`.

	_Refer to `$sq->match()` for more information._

#### SELECT

* __$sq = (new SphinxQL($conn))->select($column1, $column2, ...)->from($index1, $index2, ...)__

	Begins a `SELECT` query statement. If no column is specified, the statement defaults to using `*`. Both `$column1` and `$index1` can be arrays.

#### INSERT, REPLACE

This will return an `INT` with the number of rows affected.

* __$sq = (new SphinxQL($conn))->insert()->into($index)__

	Begins an `INSERT`.

* __$sq = (new SphinxQL($conn))->replace()->into($index)__

	Begins an `REPLACE`.

* __$sq->set($associative_array)__

	Inserts an associative array, with the keys as the columns and values as the value for the respective column.

* __$sq->value($column1, $value1)->value($column2, $value2)->value($column3, $value3)__

	Sets the value of each column individually.

* __$sq->columns($column1, $column2, $column3)->values($value1, $value2, $value3)->values($value11, $value22, $value33)__

	Allows the insertion of multiple arrays of values in the specified columns.

	Both `$column1` and `$index1` can be arrays.

#### UPDATE

This will return an `INT` with the number of rows affected.

* __$sq = (new SphinxQL($conn))->update($index)__

	Begins an `UPDATE`.

* __$sq->value($column1, $value1)->value($column2, $value2)__

	Updates the selected columns with the respective value.

* __$sq->set($associative_array)__

	Inserts the associative array, where the keys are the columns and the respective values are the column values.

#### DELETE

Will return an array with an `INT` as first member, the number of rows deleted.

* __$sq = (new SphinxQL($conn))->delete()->from($index)->where(...)__

	Begins a `DELETE`.

#### WHERE

* __$sq->where($column, $operator, $value)__

	Standard WHERE, extended to work with Sphinx filters and full-text.
### `PDO` driver

```php
<?php

use OpenRegion\SphinxQL\Drivers\Pdo\Connection;

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

use OpenRegion\SphinxQL\SphinxQL;

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
    ```php
    <?php
    use OpenRegion\SphinxQL\SphinxQL;

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
    try
    {
        $result = (new SphinxQL($conn))
            ->select()
            ->from('rt')
            ->match('title', 'Sora no || Otoshimono', true)
            ->match('title', SphinxQL::expr('"Otoshimono"/3'))
            ->match('loves', SphinxQL::expr(custom_escaping_fn('(you | me)')));
            ->execute();
    }
    catch (\OpenRegion\SphinxQL\DatabaseException $e)
    {
        // an error is thrown because two `|` one after the other aren't allowed
    }
	```

#### GROUP, WITHIN GROUP, ORDER, OFFSET, LIMIT, OPTION

* __$sq->groupBy($column)__

	`GROUP BY $column`

* __$sq->withinGroupOrderBy($column, $direction = null)__

	`WITHIN GROUP ORDER BY $column [$direction]`

	Direction can be omitted with `null`, or be `ASC` or `DESC` case insensitive.

* __$sq->orderBy($column, $direction = null)__

	`ORDER BY $column [$direction]`

	Direction can be omitted with `null`, or be `ASC` or `DESC` case insensitive.

* __$sq->offset($offset)__

	`LIMIT $offset, 9999999999999`

	Set the offset. Since SphinxQL doesn't support the `OFFSET` keyword, `LIMIT` has been set at an extremely high number.

* __$sq->limit($limit)__

	`LIMIT $limit`

* __$sq->limit($offset, $limit)__

	`LIMIT $offset, $limit`

* __$sq->option($name, $value)__

	`OPTION $name = $value`

	Set a SphinxQL option such as `max_matches` or `reverse_scan` for the query.

#### TRANSACTION

* __(new SphinxQL($conn))->transactionBegin()__

	Begins a transaction.

* __(new SphinxQL($conn))->transactionCommit()__

	Commits a transaction.

// SELECT * FROM rt WHERE gid = 200 OR ( gid = 304 AND id > 12 )
```

### MATCH with builder callback

```php
<?php
use OpenRegion\SphinxQL\SphinxQL;

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

use OpenRegion\SphinxQL\Helper;

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

use OpenRegion\SphinxQL\Facet;
use OpenRegion\SphinxQL\SphinxQL;

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
Contains the results of the multi-query execution.

* __$result->getNext()__

	Returns a [`ResultSet`](#resultset) object containing the results of the next query.


### Helper

The `Helper` class contains useful methods that don't need "query building".

Remember to `->execute()` to get a result.

* __Helper::pairsToAssoc($result)__

	Takes the pairs from a SHOW command and returns an associative array key=>value

The following methods return a prepared `SphinxQL` object. You can also use `->enqueue($next_object)`:

```php
<?php

use OpenRegion\SphinxQL\Helper;
use OpenRegion\SphinxQL\SphinxQL;

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
The Percolate class provide a dedicated helper for inserting queries in a `percolate` index.

```php
<?php

use OpenRegion\SphinxQL\Percolate;

$query = (new Percolate($conn))
     ->insert('full text query terms',false)
     ->into('pq')
     ->tags(['tag1','tag2'])
     ->filter('price>3')
     ->execute();
 ```

* __`$pq = (new Percolate($conn))->insert($query,$noEscape)`__

    Begins an ``INSERT``. A single query is allowed to be added per insert. By default, the query string is escaped. Optional second parameter  `$noEscape` can be set to  `true` for not applying the escape.

* __`$pq->into($index)`__

   Set the percolate index for insert.

* __`$pq->tags($tags)`__

   Set a list of tags per query. Accepts array of strings or string delimited by comma

* __`$pq->filter($filter)`__
   Sets an attribute filtering string. The string must look the same as string of an WHERE attribute filters clause

* __`$pq->execute()`__

   Execute the `INSERT`.

#### CALLPQ

  Searches for stored queries that provide matching for input documents.

```php
<?php
use OpenRegion\SphinxQL\Percolate;
$query = (new Percolate($conn))
     ->callPQ()
     ->from('pq')
     ->documents(['multiple documents', 'go this way'])
     ->options([
           Percolate::OPTION_VERBOSE => 1,
           Percolate::OPTION_DOCS_JSON => 1
     ])
     ->execute();
 ```
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
* __`$pq = (new Percolate($conn))->callPQ()`__

   Begins a `CALL PQ`

* __`$pq->from($index)`__

   Set percolate index.

* __`$pq->documents($docs)`__

   Set the incoming documents. $docs can be:

  - a single plain string (requires `Percolate::OPTION_DOCS_JSON` set to 0)
  - array of plain strings (requires `Percolate::OPTION_DOCS_JSON` set to 0)
  - a single JSON document
  - an array of JSON documents
  - a JSON object containing an  array of JSON objects


* __`$pq->options($options)`__

    Set options of `CALL PQ`. Refer the Manticore docs for more information about the `CALL PQ` parameters.

  - __Percolate::OPTION_DOCS_JSON__ (`as docs_json`) default to 1 (docs are json objects). Needs to be set to 0 for plain string documents.
        Documents added as associative arrays will be converted to JSON when sending the query to Manticore.
   - __Percolate::OPTION_VERBOSE__ (`as verbose`) more information is printed by following `SHOW META`, default is 0
   - __Percolate::OPTION_QUERY__  (`as query`) returns all stored queries fields , default is 0
   - __Percolate::OPTION_DOCS__  (`as docs`) provide result set as per document matched (instead of per query), default is 0

* `$pq->execute()`

   Execute the `CALL PQ`.

## Laravel

Laravel's dependency injection and realtime facades brings more convenience to SphinxQL Query Builder usage.

```php
// Register connection:
use OpenRegion\SphinxQL\Drivers\ConnectionInterface;
use OpenRegion\SphinxQL\Drivers\Mysqli\Connection;
use Illuminate\Support\ServiceProvider;
<?php

use OpenRegion\SphinxQL\Helper;

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
// In another file:
use Facades\OpenRegion\SphinxQL\SphinxQL;

```bash
./scripts/run-tests-docker.sh
```

This runs the repository test matrix (mysqli + pdo) in Docker.

## Contributing

Pull requests are welcome. Please include tests for behavior changes and keep docs in sync with API updates.
