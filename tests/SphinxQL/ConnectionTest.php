<?php

use OpenRegion\SphinxQL\Drivers\ConnectionInterface;
use OpenRegion\SphinxQL\Expression;
use OpenRegion\SphinxQL\Tests\TestUtil;

class ConnectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConnectionInterface
     */
    private $connection = null;

    protected function setUp(): void
    {
        $this->connection = TestUtil::getConnectionDriver();
        $this->connection->setParams(array('host' => '127.0.0.1', 'port' => 9307));
    }

    protected function tearDown(): void
    {
        $this->connection = null;
    }

    public function test()
    {
        TestUtil::getConnectionDriver();
    }

    public function testGetParams()
    {
        $this->assertSame(
            array('host' => '127.0.0.1', 'port' => 9307, 'socket' => null),
            $this->connection->getParams()
        );

        // create a new connection and get info
        $this->connection->setParams(array('host' => '127.0.0.2'));
        $this->connection->setParam('port', 9308);
        $this->assertSame(
            array('host' => '127.0.0.2', 'port' => 9308, 'socket' => null),
            $this->connection->getParams()
        );

        $this->connection->setParam('host', 'localhost');
        $this->assertSame(
            array('host' => '127.0.0.1', 'port' => 9308, 'socket' => null),
            $this->connection->getParams()
        );

        // create a unix socket connection with host param
        $this->connection->setParam('host', 'unix:/var/run/sphinx.sock');
        $this->assertSame(
            array('host' => null, 'port' => 9308, 'socket' => '/var/run/sphinx.sock'),
            $this->connection->getParams()
        );

        // create unix socket connection with socket param
        $this->connection->setParam('host', '127.0.0.1');
        $this->connection->setParam('socket', '/var/run/sphinx.sock');
        $this->assertSame(
            array('host' => null, 'port' => 9308, 'socket' => '/var/run/sphinx.sock'),
            $this->connection->getParams()
        );
    }

    public function testGetConnectionParams()
    {
        // verify that (deprecated) getConnectionParams continues to work
        $this->assertSame(array('host' => '127.0.0.1', 'port' => 9307, 'socket' => null), $this->connection->getParams());

        // create a new connection and get info
        $this->connection->setParams(array('host' => '127.0.0.1', 'port' => 9308));
        $this->assertSame(array('host' => '127.0.0.1', 'port' => 9308, 'socket' => null), $this->connection->getParams());
    }

    public function testGetConnection()
    {
        $this->connection->connect();
        $this->assertNotNull($this->connection->getConnection());
    }

    public function testGetConnectionThrowsException()
    {
        $this->expectException(OpenRegion\SphinxQL\Exception\ConnectionException::class);
        $this->connection->getConnection();
    }

    public function testConnect()
    {
        $this->connection->connect();

        $this->connection->setParam('options', array(MYSQLI_OPT_CONNECT_TIMEOUT => 1));
        $this->connection->connect();
    }

    public function testConnectThrowsException()
    {
        $this->expectException(OpenRegion\SphinxQL\Exception\ConnectionException::class);
        $this->connection->setParam('port', 9308);
        $this->connection->connect();
    }

    public function testPing()
    {
        $this->connection->connect();
        $this->assertTrue($this->connection->ping());
    }

    public function testClose()
    {
        $this->expectException(OpenRegion\SphinxQL\Exception\ConnectionException::class);
        $encoding = mb_internal_encoding();
        $this->connection->connect();

        if (method_exists($this->connection, 'getInternalEncoding')) {
            $this->assertEquals($encoding, $this->connection->getInternalEncoding());
            $this->assertEquals('UTF-8', mb_internal_encoding());
        }

        $this->connection->close();
        $this->assertEquals($encoding, mb_internal_encoding());
        $this->connection->getConnection();
    }

    public function testQuery()
    {
        $this->connection->connect();
        $this->assertSame(array(
            array('Variable_name' => 'total', 'Value' => '0'),
            array('Variable_name' => 'total_found', 'Value' => '0'),
            array('Variable_name' => 'time', 'Value' => '0.000'),
        ), $this->connection->query('SHOW META')->getStored());
    }

    public function testMultiQuery()
    {
        $this->connection->connect();
        $query = $this->connection->multiQuery(array('SHOW META'));
        $this->assertSame(array(
            array('Variable_name' => 'total', 'Value' => '0'),
            array('Variable_name' => 'total_found', 'Value' => '0'),
            array('Variable_name' => 'time', 'Value' => '0.000'),
        ), $query->getNext()->fetchAllAssoc());
    }

    public function testEmptyMultiQuery()
    {
        $this->expectException(OpenRegion\SphinxQL\Exception\SphinxQLException::class);
        $this->expectExceptionMessage('The Queue is empty.');
        $this->connection->connect();
        $this->connection->multiQuery(array());
    }

    public function testMultiQueryThrowsException()
    {
        $this->expectException(OpenRegion\SphinxQL\Exception\DatabaseException::class);
        $this->connection->multiQuery(array('SHOW METAL'));
    }

    public function testQueryThrowsException()
    {
        $this->expectException(OpenRegion\SphinxQL\Exception\DatabaseException::class);
        $this->connection->query('SHOW METAL');
    }

    public function testEscape()
    {
        $result = $this->connection->escape('\' "" \'\' ');
        $this->assertEquals('\'\\\' \\"\\" \\\'\\\' \'', $result);
    }

    public function testEscapeThrowsException()
    {
        $this->expectException(OpenRegion\SphinxQL\Exception\ConnectionException::class);
        // or we get the wrong error popping up
        $this->connection->setParam('port', 9308);
        $this->connection->connect();
        $this->connection->escape('\' "" \'\' ');
    }

    public function testQuote()
    {
        $this->connection->connect();
        $this->assertEquals('null', $this->connection->quote(null));
        $this->assertEquals(1, $this->connection->quote(true));
        $this->assertEquals(0, $this->connection->quote(false));
        $this->assertEquals("fo'o'bar", $this->connection->quote(new Expression("fo'o'bar")));
        $this->assertEquals(123, $this->connection->quote(123));
        $this->assertEquals("12.300000", $this->connection->quote(12.3));
        $this->assertEquals("'12.3'", $this->connection->quote('12.3'));
        $this->assertEquals("'12'", $this->connection->quote('12'));
    }

    public function testQuoteArr()
    {
        $this->connection->connect();
        $this->assertEquals(
            array('null', 1, 0, "fo'o'bar", 123, "12.300000", "'12.3'", "'12'"),
            $this->connection->quoteArr(array(null, true, false, new Expression("fo'o'bar"), 123, 12.3, '12.3', '12'))
        );
    }

}
