<?php

namespace OpenRegion\SphinxQL\Drivers\Pdo;

use OpenRegion\SphinxQL\Drivers\ConnectionBase;
use OpenRegion\SphinxQL\Drivers\MultiResultSet;
use OpenRegion\SphinxQL\Drivers\ResultSet;
use OpenRegion\SphinxQL\Exception\ConnectionException;
use OpenRegion\SphinxQL\Exception\DatabaseException;
use OpenRegion\SphinxQL\Exception\SphinxQLException;
use PDO;
use PDOException;

class Connection extends ConnectionBase
{
    /**
     * @inheritdoc
     */
    public function query($query)
    {
        $this->ensureConnection();

        $statement = $this->connection->prepare($query);

        try {
            $statement->execute();
        } catch (PDOException $exception) {
            throw new DatabaseException('[' . $exception->getCode() . '] ' . $exception->getMessage() . ' [' . $query . ']',
                (int)$exception->getCode(), $exception);
        }

        return new ResultSet(new ResultSetAdapter($statement));
    }

    /**
     * @inheritdoc
     */
    public function connect()
    {
        $params = $this->getParams();

        $dsn = 'mysql:';
        if (isset($params['host']) && $params['host'] != '') {
            $dsn .= 'host=' . $params['host'] . ';';
        }
        if (isset($params['port'])) {
            $dsn .= 'port=' . $params['port'] . ';';
        }
        if (isset($params['charset'])) {
            $dsn .= 'charset=' . $params['charset'] . ';';
        }

        if (isset($params['socket']) && $params['socket'] != '') {
            $dsn .= 'unix_socket=' . $params['socket'] . ';';
        }

        try {
            $con = new PDO($dsn);
        } catch (PDOException $exception) {
            throw new ConnectionException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->connection = $con;
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return true;
    }

    /**
     * @return bool
     * @throws ConnectionException
     */
    public function ping()
    {
        $this->ensureConnection();

        return $this->connection !== null;
    }

    /**
     * @inheritdoc
     */
    public function multiQuery(array $queue)
    {
        $this->ensureConnection();

        if (count($queue) === 0) {
            throw new SphinxQLException('The Queue is empty.');
        }

        try {
            $statement = $this->connection->query(implode(';', $queue));
        } catch (PDOException $exception) {
            throw new DatabaseException($exception->getMessage() .' [ '.implode(';', $queue).']', $exception->getCode(), $exception);
        }

        return new MultiResultSet(new MultiResultSetAdapter($statement));
    }

    /**
     * @inheritdoc
     */
    public function escape($value)
    {
        $this->ensureConnection();

        return $this->connection->quote($value);
    }
}
