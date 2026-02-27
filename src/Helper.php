<?php

namespace Foolz\SphinxQL;

use Foolz\SphinxQL\Drivers\ConnectionInterface;
use Foolz\SphinxQL\Exception\SphinxQLException;

/**
 * SQL queries that don't require "query building"
 * These return a valid SphinxQL that can even be enqueued
 */
class Helper
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns a new SphinxQL instance
     *
     * @return SphinxQL
     */
    protected function getSphinxQL()
    {
        return new SphinxQL($this->connection);
    }

    /**
     * Prepares a query in SphinxQL (not executed)
     *
     * @param $sql
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    protected function query($sql)
    {
        return $this->getSphinxQL()->query($sql);
    }

    /**
     * Converts the columns from queries like SHOW VARIABLES to simpler key-value
     *
     * @param array $result The result of an executed query
     *
     * @return array Associative array with Variable_name as key and Value as value
     * @todo make non static
     */
    public static function pairsToAssoc($result)
    {
        $ordered = array();

        foreach ($result as $item) {
            $ordered[$item['Variable_name']] = $item['Value'];
        }

        return $ordered;
    }

    /**
     * Runs query: SHOW META
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    public function showMeta()
    {
        return $this->query('SHOW META');
    }

    /**
     * Runs query: SHOW WARNINGS
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    public function showWarnings()
    {
        return $this->query('SHOW WARNINGS');
    }

    /**
     * Runs query: SHOW STATUS
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    public function showStatus()
    {
        return $this->query('SHOW STATUS');
    }

    /**
     * Runs query: SHOW TABLES
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     * @throws Exception\ConnectionException
     * @throws Exception\DatabaseException
     */
    public function showTables( $index )
    {
        $this->assertNonEmptyString($index, 'showTables() index');

        return $this->query('SHOW TABLES LIKE '.$this->connection->quote($index));
    }

    /**
     * Runs query: SHOW VARIABLES
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    public function showVariables()
    {
        return $this->query('SHOW VARIABLES');
    }

    /**
     * SET syntax
     *
     * @param string $name   The name of the variable
     * @param mixed  $value  The value of the variable
     * @param bool   $global True if the variable should be global, false otherwise
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     * @throws Exception\ConnectionException
     * @throws Exception\DatabaseException
     */
    public function setVariable($name, $value, $global = false)
    {
        if (!is_bool($global)) {
            throw new SphinxQLException('setVariable() global flag must be boolean.');
        }
        $this->assertNonEmptyString($name, 'setVariable() name');

        $query = 'SET ';

        if ($global) {
            $query .= 'GLOBAL ';
        }

        $user_var = strpos($name, '@') === 0;
        if ($user_var) {
            if (!preg_match('/^@[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
                throw new SphinxQLException('setVariable() user variable name is invalid.');
            }
        } elseif (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
            throw new SphinxQLException('setVariable() variable name is invalid.');
        }

        $query .= $name.' ';

        // user variables must always be processed as arrays
        if ($user_var && !is_array($value)) {
            $query .= '= ('.$this->connection->quote($value).')';
        } elseif (is_array($value)) {
            if (count($value) === 0) {
                throw new SphinxQLException('setVariable() array value cannot be empty.');
            }
            $query .= '= ('.implode(', ', $this->connection->quoteArr($value)).')';
        } else {
            $query .= '= '.$this->connection->quote($value);
        }

        return $this->query($query);
    }

    /**
     * CALL SNIPPETS syntax
     *
     * @param string|array $data    The document text (or documents) to search
     * @param string       $index
     * @param string       $query   Search query used for highlighting
     * @param array        $options Associative array of additional options
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     * @throws Exception\ConnectionException
     * @throws Exception\DatabaseException
     */
    public function callSnippets($data, $index, $query, $options = array())
    {
        if (!is_array($data) && !is_string($data)) {
            throw new SphinxQLException('callSnippets() data must be a string or array of strings.');
        }
        if (is_string($data) && trim($data) === '') {
            throw new SphinxQLException('callSnippets() data string cannot be empty.');
        }
        if (is_array($data)) {
            if (count($data) === 0) {
                throw new SphinxQLException('callSnippets() data array cannot be empty.');
            }
            foreach ($data as $item) {
                if (!is_string($item)) {
                    throw new SphinxQLException('callSnippets() data array must contain strings only.');
                }
            }
        }
        $this->assertNonEmptyString($index, 'callSnippets() index');
        $this->assertNonEmptyString($query, 'callSnippets() query');
        if (!is_array($options)) {
            throw new SphinxQLException('callSnippets() options must be an associative array.');
        }

        $documents = array();
        if (is_array($data)) {
            $documents[] = '('.implode(', ', $this->connection->quoteArr($data)).')';
        } else {
            $documents[] = $this->connection->quote($data);
        }

        array_unshift($options, $index, $query);

        $arr = $this->connection->quoteArr($options);
        foreach ($arr as $key => &$val) {
            if (is_string($key)) {
                $val .= ' AS '.$key;
            }
        }

        return $this->query('CALL SNIPPETS('.implode(', ', array_merge($documents, $arr)).')');
    }

    /**
     * CALL KEYWORDS syntax
     *
     * @param string      $text
     * @param string      $index
     * @param null|string $hits
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     * @throws Exception\ConnectionException
     * @throws Exception\DatabaseException
     */
    public function callKeywords($text, $index, $hits = null)
    {
        $this->assertNonEmptyString($text, 'callKeywords() text');
        $this->assertNonEmptyString($index, 'callKeywords() index');
        if ($hits !== null && !in_array($hits, array(0, 1, '0', '1'), true)) {
            throw new SphinxQLException('callKeywords() hits must be 0, 1, or null.');
        }

        $arr = array($text, $index);
        if ($hits !== null) {
            $arr[] = $hits;
        }

        return $this->query('CALL KEYWORDS('.implode(', ', $this->connection->quoteArr($arr)).')');
    }

    /**
     * DESCRIBE syntax
     *
     * @param string $index The name of the index
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    public function describe($index)
    {
        $this->assertNonEmptyString($index, 'describe() index');

        return $this->query('DESCRIBE '.$index);
    }

    /**
     * CREATE FUNCTION syntax
     *
     * @param string $udf_name
     * @param string $returns  Whether INT|BIGINT|FLOAT|STRING
     * @param string $so_name
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     * @throws Exception\ConnectionException
     * @throws Exception\DatabaseException
     */
    public function createFunction($udf_name, $returns, $so_name)
    {
        $this->assertNonEmptyString($udf_name, 'createFunction() udf_name');
        $this->assertNonEmptyString($returns, 'createFunction() returns');
        $this->assertNonEmptyString($so_name, 'createFunction() so_name');

        $normalizedReturn = strtoupper(trim($returns));
        if (!in_array($normalizedReturn, array('INT', 'UINT', 'BIGINT', 'FLOAT', 'STRING'), true)) {
            throw new SphinxQLException('createFunction() returns must be one of: INT, UINT, BIGINT, FLOAT, STRING.');
        }

        return $this->query('CREATE FUNCTION '.$udf_name.
            ' RETURNS '.$normalizedReturn.' SONAME '.$this->connection->quote($so_name));
    }

    /**
     * DROP FUNCTION syntax
     *
     * @param string $udf_name
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    public function dropFunction($udf_name)
    {
        $this->assertNonEmptyString($udf_name, 'dropFunction() udf_name');

        return $this->query('DROP FUNCTION '.$udf_name);
    }

    /**
     * ATTACH INDEX * TO RTINDEX * syntax
     *
     * @param string $disk_index
     * @param string $rt_index
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    public function attachIndex($disk_index, $rt_index)
    {
        $this->assertNonEmptyString($disk_index, 'attachIndex() disk_index');
        $this->assertNonEmptyString($rt_index, 'attachIndex() rt_index');

        return $this->query('ATTACH INDEX '.$disk_index.' TO RTINDEX '.$rt_index);
    }

    /**
     * FLUSH RTINDEX syntax
     *
     * @param string $index
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    public function flushRtIndex($index)
    {
        $this->assertNonEmptyString($index, 'flushRtIndex() index');

        return $this->query('FLUSH RTINDEX '.$index);
    }

    /**
     * TRUNCATE RTINDEX syntax
     *
     * @param string $index
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    public function truncateRtIndex($index)
    {
        $this->assertNonEmptyString($index, 'truncateRtIndex() index');

        return $this->query('TRUNCATE RTINDEX '.$index);
    }

    /**
     * OPTIMIZE INDEX syntax
     *
     * @param string $index
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    public function optimizeIndex($index)
    {
        $this->assertNonEmptyString($index, 'optimizeIndex() index');

        return $this->query('OPTIMIZE INDEX '.$index);
    }

    /**
     * SHOW INDEX STATUS syntax
     *
     * @param $index
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    public function showIndexStatus($index)
    {
        $this->assertNonEmptyString($index, 'showIndexStatus() index');

        return $this->query('SHOW INDEX '.$index.' STATUS');
    }

    /**
     * FLUSH RAMCHUNK syntax
     *
     * @param $index
     *
     * @return SphinxQL A SphinxQL object ready to be ->execute();
     */
    public function flushRamchunk($index)
    {
        $this->assertNonEmptyString($index, 'flushRamchunk() index');

        return $this->query('FLUSH RAMCHUNK '.$index);
    }

    /**
     * @param mixed  $value
     * @param string $field
     *
     * @throws SphinxQLException
     */
    private function assertNonEmptyString($value, $field)
    {
        if (!is_string($value) || trim($value) === '') {
            throw new SphinxQLException($field.' must be a non-empty string.');
        }
    }
}
