<?php

namespace OpenRegion\SphinxQL\Drivers;

use OpenRegion\SphinxQL\Exception\ConnectionException;
use OpenRegion\SphinxQL\Exception\DatabaseException;
use OpenRegion\SphinxQL\Exception\SphinxQLException;
use OpenRegion\SphinxQL\Expression;

interface ConnectionInterface
{
    /**
     * Performs a query on the Sphinx server.
     *
     * @param string $query The query string
     *
     * @return ResultSetInterface The result array or number of rows affected
     * @throws DatabaseException If the executed query produced an error
     * @throws ConnectionException
     */
    public function query($query);

    /**
     * Performs multiple queries on the Sphinx server.
     *
     * @param array $queue Queue holding all of the queries to be executed
     *
     * @return MultiResultSetInterface The result array
     * @throws DatabaseException In case a query throws an error
     * @throws SphinxQLException In case the array passed is empty
     * @throws ConnectionException
     */
    public function multiQuery(array $queue);

    /**
     * Escapes the input
     *
     * @param string $value The string to escape
     *
     * @return string The escaped string
     * @throws DatabaseException If an error was encountered during server-side escape
     * @throws ConnectionException
     */
    public function escape($value);

    /**
     * Adds backtick quotes to the identifier.
     *
     * @param string $identifier Identifier string
     *
     * @return string Quoted identifier string
     */
    public function quoteIdentifier(string $identifier): string;

    /**
     * Calls $this->quoteIdentifier() on every element of the array passed.
     *
     * @param array $array The array of elements to quote
     *
     * @return array The array of quotes elements
     * @throws DatabaseException
     * @throws ConnectionException
     */
    public function quoteIdentifierArray(array $array = array()): array;

    /**
     * Adds quotes around values when necessary.
     *
     * @param Expression|string|null|bool|array|int|float $value The input string, eventually wrapped in an expression
     *      to leave it untouched
     *
     * @return Expression|string|int The untouched Expression or the quoted string
     * @throws DatabaseException
     * @throws ConnectionException
     */
    public function quote($value);

    /**
     * Calls $this->quote() on every element of the array passed.
     *
     * @param array $array The array of elements to quote
     *
     * @return array The array of quotes elements
     * @throws DatabaseException
     * @throws ConnectionException
     */
    public function quoteArr(array $array = array());
}
