<?php

namespace OpenRegion\SphinxQL\Drivers\Mysqli;

use OpenRegion\SphinxQL\Drivers\MultiResultSetAdapterInterface;
use OpenRegion\SphinxQL\Drivers\ResultSet;
use OpenRegion\SphinxQL\Exception\ConnectionException;

class MultiResultSetAdapter implements MultiResultSetAdapterInterface
{
    /**
     * @var bool
     */
    protected $valid = true;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritdoc
     * @throws ConnectionException
     */
    public function getNext()
    {
        if (
            !$this->valid() ||
            !$this->connection->getConnection()->more_results()
        ) {
            $this->valid = false;
        } else {
            $this->connection->getConnection()->next_result();
        }
    }

    /**
     * @inheritdoc
     * @throws ConnectionException
     */
    public function current()
    {
        $adapter = new ResultSetAdapter($this->connection, $this->connection->getConnection()->store_result());
        return new ResultSet($adapter);
    }

    /**
     * @inheritdoc
     * @throws ConnectionException
     */
    public function valid()
    {
        return $this->connection->getConnection()->errno == 0 && $this->valid;
    }
}
