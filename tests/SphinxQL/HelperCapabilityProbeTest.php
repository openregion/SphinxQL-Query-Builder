<?php

use OpenRegion\SphinxQL\Drivers\ConnectionInterface;
use OpenRegion\SphinxQL\Expression;
use OpenRegion\SphinxQL\Helper;

class HelperCapabilityProbeTest extends \PHPUnit\Framework\TestCase
{
    public function testShowTableSettingsProbeUsesDiscoveredTableName()
    {
        $connection = new HelperProbeConnection(array(
            'SELECT VERSION()' => array(array('VERSION()' => '3.9.1')),
            'SHOW TABLES' => array(array('Index' => 'products', 'Type' => 'rt')),
            'SHOW TABLE rt SETTINGS' => new \RuntimeException('missing rt'),
            'SHOW TABLE products SETTINGS' => array(),
        ));

        $helper = new Helper($connection);

        $this->assertTrue($helper->supports('show_table_settings'));
        $this->assertContains('SHOW TABLE products SETTINGS', $connection->queries);
    }

    public function testCallSuggestProbeUsesDiscoveredTableName()
    {
        $connection = new HelperProbeConnection(array(
            'SELECT VERSION()' => array(array('VERSION()' => '3.9.1')),
            'SHOW TABLES' => array(array('Index' => 'products', 'Type' => 'rt')),
            "CALL SUGGEST('teh', 'rt')" => new \RuntimeException('missing rt'),
            "CALL SUGGEST('teh', 'products')" => array(),
        ));

        $helper = new Helper($connection);

        $this->assertTrue($helper->supports('call_suggest'));
        $this->assertContains("CALL SUGGEST('teh', 'products')", $connection->queries);
    }

    public function testQSuggestAndAutocompleteFollowBuddySupport()
    {
        $connection = new HelperProbeConnection(array(
            'SELECT VERSION()' => array(array('VERSION()' => '6.3.0 Manticore')),
            'SHOW VERSION' => new \RuntimeException('buddy unavailable'),
        ));

        $helper = new Helper($connection);

        $this->assertFalse($helper->supports('buddy'));
        $this->assertFalse($helper->supports('call_qsuggest'));
        $this->assertFalse($helper->supports('call_autocomplete'));
    }
}

class HelperProbeConnection implements ConnectionInterface
{
    /**
     * @var array<int,string>
     */
    public $queries = array();

    /**
     * @var array<string,mixed>
     */
    private $responses = array();

    /**
     * @param array<string,mixed> $responses
     */
    public function __construct(array $responses)
    {
        $this->responses = $responses;
    }

    public function query(string $query): \OpenRegion\SphinxQL\Drivers\ResultSetInterface
    {
        $this->queries[] = $query;

        if (!array_key_exists($query, $this->responses)) {
            throw new \RuntimeException('Unexpected query: '.$query);
        }

        $response = $this->responses[$query];
        if ($response instanceof \Closure) {
            $response = $response($query, $this);
        }
        if ($response instanceof \Exception) {
            throw $response;
        }

        return new HelperProbeResultSet($response);
    }

    public function multiQuery(array $queue): \OpenRegion\SphinxQL\Drivers\MultiResultSetInterface
    {
        throw new \BadMethodCallException('Not implemented in test double.');
    }

    public function escape(string $value): string
    {
        return "'".str_replace("'", "\\'", $value)."'";
    }

    public function quote(Expression|string|null|bool|array|int|float $value): string|int|float
    {
        if ($value === null) {
            return 'null';
        }
        if ($value === true) {
            return 1;
        }
        if ($value === false) {
            return 0;
        }
        if ($value instanceof Expression) {
            return $value->value();
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $this->escape($value);
    }

    public function quoteArr(array $array = array()): array
    {
        $quoted = array();
        foreach ($array as $key => $item) {
            $quoted[$key] = $this->quote($item);
        }

        return $quoted;
    }

  public function quoteIdentifier(string $identifier): string
  {
    return '';
  }

  public function quoteIdentifierArray(array $array = []): array
  {
    return [];
  }
}

class HelperProbeResultSet implements \OpenRegion\SphinxQL\Drivers\ResultSetInterface
{
    /**
     * @var mixed
     */
    private $stored;

    /**
     * @param mixed $stored
     */
    public function __construct($stored)
    {
        $this->stored = $stored;
    }

    /**
     * @return mixed
     */
    public function getStored(): array|int
    {
        if (is_array($this->stored) || is_int($this->stored)) {
            return $this->stored;
        }

        return array();
    }

    public function store(): self
    {
        return $this;
    }

    public function hasRow(int $row): bool
    {
        return false;
    }

    public function toRow(int $row): self
    {
        return $this;
    }

    public function hasNextRow(): bool
    {
        return false;
    }

    public function toNextRow(): self
    {
        return $this;
    }

    public function getAffectedRows(): int
    {
        return 0;
    }

    public function fetchAllAssoc(): array
    {
        return is_array($this->stored) ? $this->stored : array();
    }

    public function fetchAllNum(): array
    {
        return is_array($this->stored) ? $this->stored : array();
    }

    public function fetchAssoc(): ?array
    {
        return null;
    }

    public function fetchNum(): ?array
    {
        return null;
    }

    public function freeResult(): self
    {
        return $this;
    }

    public function offsetExists(mixed $offset): bool
    {
        return false;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }

    public function current(): mixed
    {
        return null;
    }

    public function next(): void
    {
    }

    public function key(): mixed
    {
        return null;
    }

    public function valid(): bool
    {
        return false;
    }

    public function rewind(): void
    {
    }

    public function count(): int
    {
        return 0;
    }
}
