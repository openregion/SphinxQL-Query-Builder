#### 5.0.0
* Changed namespace from `Foolz\SphinxQL` to `OpenRegion\SphinxQL`
* Added `quoteIdentifier` and `quoteIdentifierArray`
* Fixed readme

#### 4.0.0
* Dropped support for PHP 8.1 and lower (minimum PHP is now 8.2)
* Updated CI PHP matrix to 8.2 and 8.3
* Normalized MySQLi driver exception handling for modern PHP `mysqli_sql_exception` behavior
* Hardened runtime validation for `SphinxQL`, `Facet`, `Helper`, and `Percolate` input contracts (fail-fast exceptions for invalid query-shape input)
* Standardized driver exception message prefixes for better diagnostics (`[mysqli][...]`, `[pdo][...]`)
* Expanded helper runtime API coverage (`SHOW WARNINGS`, `SHOW STATUS`, `SHOW INDEX STATUS`, `FLUSH RAMCHUNK`, `FLUSH RTINDEX`, `OPTIMIZE INDEX`, UDF lifecycle checks)
* Added fluent boolean grouping APIs (`orWhere`, `whereOpen/whereClose`, `orHaving`, `havingOpen/havingClose`) and JOIN builders (`join`, `innerJoin`, `leftJoin`, `rightJoin`, `crossJoin`)
* Added `orderByKnn()` and broader helper wrappers for operational and Manticore-oriented commands (`SHOW PROFILE/PLAN/THREADS/VERSION/PLUGINS`, table status/settings/indexes, flush/reload/kill, suggest family)
* Added capability discovery and feature-gating APIs (`Capabilities`, `getCapabilities()`, `supports()`, `requireSupport()`) with `UnsupportedFeatureException` for unsupported command families
* Added `SphinxQL::requireSupport()` passthrough and convenience engine predicates on `Capabilities` (`isManticore`, `isSphinx2`, `isSphinx3`)
* Added helper parity wrappers for `SHOW CHARACTER SET` and `SHOW COLLATION`
* Added `docs/feature-matrix.yml` as a feature-level support map across Sphinx2/Sphinx3/Manticore
* Added capability-aware runtime tests for optional engine features (`supportsCommand`, Buddy-gated checks)
* Added and stabilized Sphinx 3 compatibility coverage while preserving Sphinx 2 and Manticore test behavior
* Added support for optional connection credentials (`username`/`password`) in both PDO and MySQLi drivers (closes #208)
* Added optional-index `update($index = null)` flow for fluent `->update()->into($index)` usage (closes #184)
* Added explicit `update()->compile()/execute()` guard when no target index is set via `into($index)` (prevents invalid `UPDATE` SQL emission)
* Restored `showTables($index = null)` compatibility (`SHOW TABLES` for null/empty, `SHOW TABLES LIKE ...` for non-empty) and removed hardcoded `rt` assumptions from runtime capability probes
* Aligned Buddy capability flags so `callQSuggest()`/`callAutocomplete()` are gated by detected Buddy availability
* Added MVA insert/update array example in README (closes #178)
* Corrected escaping docs to reference connection-level helpers and clarified `quoteIdentifier()` availability (closes #203)
* Added a root `LICENSE` file (closes #171)
* Migrated CI to GitHub Actions-only validation with strict composer metadata checks
* Hardened GitHub Actions reliability with SQL-readiness checks, full-history checkout for changed-line artifacts, and digest-pinned Buddy integration runtime image
* Updated documentation and added a dedicated `MIGRATING-5.0.md` guide

#### 3.0.2
* Dropped support for PHP 7.3 and lower

#### 3.0.1
* Fixed Exception Error for PDO Driver
* Dropped support for PHP 7.0 and lower

#### 3.0.0
* Added support for PHP 8
* Dropped support for PHP 7.0 and lower
* Renamed `Foolz\SphinxQL\Match` to `Foolz\SphinxQL\MatchBuilder` (BREAKING CHANGE)

#### 2.1.0
* Added exception code and previous throwable to errors
* Added `setType` method to `SphinxQL` class
* Added support for `MATCH` to `DELETE` queries
* Updated MySQLi driver to silence internal warnings by default

#### 2.0.0
* Added support for [Manticore](https://manticoresearch.com)
* Added `Percolate` class for `Manticore`
* Added `orPhrase` method to `Match` class
* Added `resetFacets` method to `SphinxQL` class
* Added support for multi-document snippet call
* Fixed `Connection` exception thrown
* Fixed incorrect property accessibility/visibility
* Refactored `ResultSet` and `MultiResultSet` classes to reduce duplicate code
* Removed `Connection` error suppression
* Removed `SphinxQL\Drivers\ResultSetAdapterInterface` constants
* Removed static `SphinxQL::create` method
* Removed deprecated `\Foolz\SphinxQL\Connection`
* Removed support for PHP 5.3 and HHVM
* Updated fetch type for drivers to use `boolean` to return assoc/indexed arrays
* Updated PHPDoc blocks

Note: This release contains **breaking changes** around the instantiation of the `SphinxQL` class with the removal of static methods. Please refer to the README for any API changes.

#### 1.2.0
* Added support for `GROUP N BY`
* Refactored `Connection`, `\Foolz\SphinxQL\Connection` is now deprecated.
* Refactored `ResultSet` and `MultiResultSet` to reduce duplicate code

Note: This release contains **breaking changes** with the introduction of `ResultSet` and `MultiResultSet` changes. Please refer to the README for any API changes.

#### 0.9.7
* Added support for unix sockets
* Added `NOT IN` condition in `WHERE` statements

#### 0.9.6
* Added named integer lists support to `OPTION` with associative array (@alpha0010)
* Deprecated special case `OPTION` for `field_weights` and `index_weights`
* Forced `Connection` to use utf8 charset (@t1gor)

#### 0.9.5
* `Expression` support for `OPTION` value

#### 0.9.4
* Replaced `getConnectionParams()` and `setConnectionParams()` with `getParam()`, `getParams()`, `setParam()` (thanks to @FindTheBest)
* Deprecated `getConnectionParams()` and `setConnectionParams()`
* Added `ConnectionInterface`

#### 0.9.3
* HHVM support
* Added escaping of new MATCH features by lowercasing the search string

#### 0.9.2
* Created `Helper` class to contain non-query-builder query methods, all returning `SphinxQL` objects
* Deprecated all non-query-builder query methods in `SphinxQL` class
* Improved `$sq->enqueue()` in `SphinxQL` class to have a parameter to append any custom `SphinxQL` objects
* Added `$sq->query()` method to `SphinxQL` to allow setting SQL queries without executing them

#### 0.9.1
* Deprecated SphinxQL::forge() with static Connection and implemented SphinxQL::create($conn)
* Added array and * support to MATCH columns (thanks to @FindTheBest)
* Added Expression support to MATCH value

#### 0.9.0
* Refactored to be fully OOP
* Changed code style to be PSR-2 compliant
* Removed all unnecessary `static` keywords
* Removed old bootstrap file for fuelphp

#### 0.8.6
* Added Connection::ping()
* Added Connection::close()
* Fixed uncaught exception thrown by Connection::getConnection()

#### 0.8.5
* Removed Array typehints
* Removed unsupported charset argument

#### 0.8.4
* Fixed composer bootstrap
* Removed `Sphinxql` prefix on Connection and Expression classes

#### 0.8.3
* Added Queue support

#### 0.8.2
* Fixed composer bootstrap

#### 0.8.1
* Improved phpunit tests

#### 0.8.0
* Initial release
