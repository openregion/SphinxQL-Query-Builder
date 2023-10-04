<?php

namespace OpenRegion\SphinxQL\Drivers\Pdo;

use OpenRegion\SphinxQL\Drivers\MultiResultSetAdapterInterface;
use OpenRegion\SphinxQL\Drivers\ResultSet;
use PDOStatement;

class MultiResultSetAdapter implements MultiResultSetAdapterInterface
{
    /**
     * @var bool
     */
    protected $valid = true;

    /**
     * @var PDOStatement
     */
    protected $statement;

    /**
     * @param PDOStatement $statement
     */
    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * @inheritdoc
     */
    public function getNext()
    {
        if (
            !$this->valid() ||
            !$this->statement->nextRowset()
        ) {
            $this->valid = false;
        }
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return new ResultSet(new ResultSetAdapter($this->statement));
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->statement && $this->valid;
    }
}
