<?php

namespace Foolz\SphinxQL\Drivers\Pdo;

use Foolz\SphinxQL\Drivers\ResultSetAdapterInterface;
use PDO;
use PDOStatement;

class ResultSetAdapter implements ResultSetAdapterInterface
{
    /**
     * @var PDOStatement
     */
    protected $statement;

    /**
     * @var bool
     */
    protected $valid = true;

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
    public function getAffectedRows()
    {
        return $this->statement->rowCount();
    }

    /**
     * @inheritdoc
     */
    public function getNumRows()
    {
        return $this->statement->rowCount();
    }

    /**
     * @inheritdoc
     */
    public function getFields()
    {
        $fields = array();

        for ($i = 0; $i < $this->statement->columnCount(); $i++) {
            $fields[] = (object)$this->statement->getColumnMeta($i);
        }

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function isDml()
    {
        return $this->statement->columnCount() == 0;
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        return $this->normalizeRows($this->statement->fetchAll(PDO::FETCH_NUM));
    }

    /**
     * @inheritdoc
     */
    public function toRow($num)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function freeResult()
    {
        $this->statement->closeCursor();
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {

    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->valid;
    }

    /**
     * @inheritdoc
     */
    public function fetch($assoc = true)
    {
        if ($assoc) {
            $row = $this->statement->fetch(PDO::FETCH_ASSOC);
        } else {
            $row = $this->statement->fetch(PDO::FETCH_NUM);
        }

        if (!$row) {
            $this->valid = false;
            $row = null;
        } else {
            $row = $this->normalizeRow($row);
        }

        return $row;
    }

    /**
     * @inheritdoc
     */
    public function fetchAll($assoc = true)
    {
        if ($assoc) {
            $row = $this->statement->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $row = $this->statement->fetchAll(PDO::FETCH_NUM);
        }

        if (empty($row)) {
            $this->valid = false;
        }

        return $this->normalizeRows($row);
    }

    /**
     * Cast scalar non-string values to string to keep PDO and MySQLi
     * result typing aligned across PHP versions.
     *
     * @param array $row
     * @return array
     */
    protected function normalizeRow(array $row)
    {
        foreach ($row as $key => $value) {
            if (is_scalar($value) && !is_string($value)) {
                $row[$key] = (string) $value;
            }
        }

        return $row;
    }

    /**
     * @param array $rows
     * @return array
     */
    protected function normalizeRows(array $rows)
    {
        foreach ($rows as $index => $row) {
            $rows[$index] = $this->normalizeRow($row);
        }

        return $rows;
    }
}
