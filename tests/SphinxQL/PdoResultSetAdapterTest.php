<?php

use OpenRegion\SphinxQL\Drivers\Pdo\ResultSetAdapter;

class PdoResultSetAdapterTest extends \PHPUnit\Framework\TestCase
{
    public function testBooleanNormalizationUsesNumericStrings()
    {
        if ($GLOBALS['driver'] !== 'Pdo') {
            $this->markTestSkipped('PDO-specific adapter test.');
        }

        $statement = $this->getMockBuilder(\PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statement
            ->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(array(
                array(
                    'truthy' => true,
                    'falsy' => false,
                    'number' => 12,
                    'already_string' => '12',
                ),
            ));

        $adapter = new ResultSetAdapter($statement);
        $rows = $adapter->fetchAll();

        $this->assertSame('1', $rows[0]['truthy']);
        $this->assertSame('0', $rows[0]['falsy']);
        $this->assertSame('12', $rows[0]['number']);
        $this->assertSame('12', $rows[0]['already_string']);
    }
}
