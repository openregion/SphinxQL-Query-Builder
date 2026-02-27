<?php

use Foolz\SphinxQL\Drivers\ConnectionInterface;
use Foolz\SphinxQL\Helper;
use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Tests\TestUtil;

class HelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConnectionInterface
     */
    public $conn;

    protected function setUp(): void
    {
        $conn = TestUtil::getConnectionDriver();
        $conn->setParam('port', 9307);
        $this->conn = $conn;

        $this->createSphinxQL()->query('TRUNCATE RTINDEX rt')->execute();
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            try {
                $this->conn->close();
            } catch (\Exception $exception) {
                // no-op in test teardown
            }
        }
    }

    /**
     * @return SphinxQL
     */
    protected function createSphinxQL()
    {
        return new SphinxQL($this->conn);
    }

    /**
     * @return Helper
     */
    protected function createHelper()
    {
        return new Helper($this->conn);
    }

    public function testShowTables()
    {
        $this->assertEquals(
            array(array('Index' => 'rt', 'Type' => 'rt')),
            $this->createHelper()->showTables('rt')->execute()->getStored()
        );
    }

    public function testDescribe()
    {
        $describe = $this->createHelper()->describe('rt')->execute()->getStored();
        array_shift($describe);
        $describe = TestUtil::pickColumns($describe, array('Field', 'Type'));
        $this->assertSame(
            array(
                array('Field' => 'title', 'Type' => 'field'),
                array('Field' => 'content', 'Type' => 'field'),
                array('Field' => 'gid', 'Type' => 'uint'),
            ),
            $describe
        );
    }

    public function testSetVariable()
    {
        $this->createHelper()->setVariable('AUTOCOMMIT', 0)->execute();
        $vars = Helper::pairsToAssoc($this->createHelper()->showVariables()->execute()->getStored());
        $this->assertEquals(0, $vars['autocommit']);

        $this->createHelper()->setVariable('AUTOCOMMIT', 1)->execute();
        $vars = Helper::pairsToAssoc($this->createHelper()->showVariables()->execute()->getStored());
        $this->assertEquals(1, $vars['autocommit']);

        $this->createHelper()->setVariable('@foo', 1, true);
        $this->createHelper()->setVariable('@foo', array(0), true);
    }

    public function testCallSnippets()
    {
        $snippets = $this->createHelper()->callSnippets(
            'this is my document text',
            'rt',
            'is'
        )->execute()->getStored();
        $this->assertEquals(
            array(array('snippet' => 'this <b>is</b> my document text')),
            $snippets
        );

        $snippets = $this->createHelper()->callSnippets(
            'this is my document text',
            'rt',
            'is',
            array(
                'before_match' => '<em>',
                'after_match'  => '</em>',
            )
        )->execute()->getStored();
        $this->assertEquals(
            array(array('snippet' => 'this <em>is</em> my document text')),
            $snippets
        );

        $snippets = $this->createHelper()->callSnippets(
            array('this is my document text', 'another document'),
            'rt',
            'is',
            array('allow_empty' => 1)
        )->execute()->getStored();
        $this->assertEquals(
            array(
                array('snippet' => 'this <b>is</b> my document text'),
                array('snippet' => ''),
            ),
            $snippets
        );
    }

    public function testCallKeywords()
    {
        $keywords = $this->createHelper()->callKeywords(
            'test case',
            'rt'
        )->execute()->getStored();
        $keywords = TestUtil::pickColumns($keywords, array('qpos', 'tokenized', 'normalized'));
        $this->assertEquals(
            array(
                array(
                    'qpos'       => '1',
                    'tokenized'  => 'test',
                    'normalized' => 'test',
                ),
                array(
                    'qpos'       => '2',
                    'tokenized'  => 'case',
                    'normalized' => 'case',
                ),
            ),
            $keywords
        );

        $keywords = $this->createHelper()->callKeywords(
            'test case',
            'rt',
            1
        )->execute()->getStored();
        $keywords = TestUtil::pickColumns($keywords, array('qpos', 'tokenized', 'normalized', 'docs', 'hits'));
        $this->assertEquals(
            array(
                array(
                    'qpos'       => '1',
                    'tokenized'  => 'test',
                    'normalized' => 'test',
                    'docs'       => '0',
                    'hits'       => '0',
                ),
                array(
                    'qpos'       => '2',
                    'tokenized'  => 'case',
                    'normalized' => 'case',
                    'docs'       => '0',
                    'hits'       => '0',
                ),
            ),
            $keywords
        );
    }

    public function testUdfNotInstalled()
    {
        $this->expectException(Foolz\SphinxQL\Exception\DatabaseException::class);
        $this->expectExceptionMessage('Sphinx expr: syntax error');
        $this->conn->query('SELECT MY_UDF()');
    }

    public function testCreateFunction()
    {
        $returnType = TestUtil::isSphinx3($this->conn) ? 'BIGINT' : 'INT';
        $this->createHelper()->createFunction('my_udf', $returnType, 'test_udf.so')->execute();
        $this->assertSame(
            array(array('MY_UDF()' => '42')),
            $this->conn->query('SELECT MY_UDF()')->getStored()
        );
        $this->createHelper()->dropFunction('my_udf')->execute();

        $this->expectException(Foolz\SphinxQL\Exception\DatabaseException::class);
        $this->conn->query('SELECT MY_UDF()');
    }

    /**
     * @covers \Foolz\SphinxQL\Helper::truncateRtIndex
     */
    public function testTruncateRtIndex()
    {
        $this->createSphinxQL()
            ->insert()
            ->into('rt')
            ->set(array(
                'id' => 1,
                'title' => 'this is a title',
                'content' => 'this is the content',
                'gid' => 100
            ))
            ->execute();

        $result = $this->createSphinxQL()
            ->select()
            ->from('rt')
            ->execute()
            ->getStored();

        $this->assertCount(1, $result);

        $this->createHelper()->truncateRtIndex('rt')->execute();

        $result = $this->createSphinxQL()
            ->select()
            ->from('rt')
            ->execute()
            ->getStored();

        $this->assertCount(0, $result);
    }

    // actually executing these queries may not be useful nor easy to test
    public function testMiscellaneous()
    {
        $query = $this->createHelper()->showMeta();
        $this->assertEquals('SHOW META', $query->compile()->getCompiled());

        $query = $this->createHelper()->showWarnings();
        $this->assertEquals('SHOW WARNINGS', $query->compile()->getCompiled());

        $query = $this->createHelper()->showStatus();
        $this->assertEquals('SHOW STATUS', $query->compile()->getCompiled());

        $query = $this->createHelper()->attachIndex('disk', 'rt');
        $this->assertEquals('ATTACH INDEX disk TO RTINDEX rt', $query->compile()->getCompiled());

        $query = $this->createHelper()->flushRtIndex('rt');
        $this->assertEquals('FLUSH RTINDEX rt', $query->compile()->getCompiled());

        $query = $this->createHelper()->optimizeIndex('rt');
        $this->assertEquals('OPTIMIZE INDEX rt', $query->compile()->getCompiled());

        $query = $this->createHelper()->showIndexStatus('rt');
        $this->assertEquals('SHOW INDEX rt STATUS', $query->compile()->getCompiled());

        $query = $this->createHelper()->flushRamchunk('rt');
        $this->assertEquals('FLUSH RAMCHUNK rt', $query->compile()->getCompiled());

        $query = $this->createHelper()->showProfile();
        $this->assertEquals('SHOW PROFILE', $query->compile()->getCompiled());

        $query = $this->createHelper()->showPlan();
        $this->assertEquals('SHOW PLAN', $query->compile()->getCompiled());

        $query = $this->createHelper()->showThreads();
        $this->assertEquals('SHOW THREADS', $query->compile()->getCompiled());

        $query = $this->createHelper()->showVersion();
        $this->assertEquals('SHOW VERSION', $query->compile()->getCompiled());

        $query = $this->createHelper()->showPlugins();
        $this->assertEquals('SHOW PLUGINS', $query->compile()->getCompiled());

        $query = $this->createHelper()->showAgentStatus();
        $this->assertEquals('SHOW AGENT STATUS', $query->compile()->getCompiled());

        $query = $this->createHelper()->showScroll();
        $this->assertEquals('SHOW SCROLL', $query->compile()->getCompiled());

        $query = $this->createHelper()->showDatabases();
        $this->assertEquals('SHOW DATABASES', $query->compile()->getCompiled());

        $query = $this->createHelper()->showCreateTable('rt');
        $this->assertEquals('SHOW CREATE TABLE rt', $query->compile()->getCompiled());

        $query = $this->createHelper()->showTableStatus();
        $this->assertEquals('SHOW TABLE STATUS', $query->compile()->getCompiled());

        $query = $this->createHelper()->showTableStatus('rt');
        $this->assertEquals('SHOW TABLE rt STATUS', $query->compile()->getCompiled());

        $query = $this->createHelper()->showTableSettings('rt');
        $this->assertEquals('SHOW TABLE rt SETTINGS', $query->compile()->getCompiled());

        $query = $this->createHelper()->showTableIndexes('rt');
        $this->assertEquals('SHOW TABLE rt INDEXES', $query->compile()->getCompiled());

        $query = $this->createHelper()->showQueries();
        $this->assertEquals('SHOW QUERIES', $query->compile()->getCompiled());

        $query = $this->createHelper()->flushAttributes();
        $this->assertEquals('FLUSH ATTRIBUTES', $query->compile()->getCompiled());

        $query = $this->createHelper()->flushHostnames();
        $this->assertEquals('FLUSH HOSTNAMES', $query->compile()->getCompiled());

        $query = $this->createHelper()->flushLogs();
        $this->assertEquals('FLUSH LOGS', $query->compile()->getCompiled());

        $query = $this->createHelper()->reloadPlugins();
        $this->assertEquals('RELOAD PLUGINS', $query->compile()->getCompiled());

        $query = $this->createHelper()->kill(123);
        $this->assertEquals('KILL 123', $query->compile()->getCompiled());

        $query = $this->createHelper()->callSuggest('teh', 'rt', array('limit' => 5));
        $this->assertEquals("CALL SUGGEST('teh', 'rt', 5 AS limit)", $query->compile()->getCompiled());

        if ($this->createHelper()->supports('call_qsuggest')) {
            $query = $this->createHelper()->callQSuggest('teh', 'rt', array('limit' => 3));
            $this->assertEquals("CALL QSUGGEST('teh', 'rt', 3 AS limit)", $query->compile()->getCompiled());
        }

        if ($this->createHelper()->supports('call_autocomplete')) {
            $query = $this->createHelper()->callAutocomplete('te', 'rt', array('fuzzy' => 1));
            $this->assertEquals("CALL AUTOCOMPLETE('te', 'rt', 1 AS fuzzy)", $query->compile()->getCompiled());
        }
    }

    public function testShowWarningsAndStatusExecution()
    {
        $warnings = $this->createHelper()->showWarnings()->execute()->getStored();
        if (is_int($warnings)) {
            $this->assertGreaterThanOrEqual(0, $warnings);
        } else {
            $this->assertIsArray($warnings);
        }

        $status = $this->createHelper()->showStatus()->execute()->getStored();
        $this->assertNotEmpty($status);
        $this->assertArrayHasKey('Value', $status[0]);
    }

    public function testShowIndexStatusExecution()
    {
        $statusRows = $this->createHelper()->showIndexStatus('rt')->execute()->getStored();
        $this->assertNotEmpty($statusRows);

        $found = false;
        foreach ($statusRows as $row) {
            if (($row['Variable_name'] ?? null) === 'index_type') {
                $found = true;
                $this->assertSame('rt', (string) ($row['Value'] ?? ''));
                break;
            }
        }

        $this->assertTrue($found);
    }

    public function testFlushAndOptimizeExecution()
    {
        $result = $this->createHelper()->flushRamchunk('rt')->execute()->getStored();
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);

        $result = $this->createHelper()->flushRtIndex('rt')->execute()->getStored();
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);

        $result = $this->createHelper()->optimizeIndex('rt')->execute()->getStored();
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testHelperRequiresNonEmptyIdentifiers()
    {
        $this->expectException(Foolz\SphinxQL\Exception\SphinxQLException::class);
        $this->createHelper()->showTables('');
    }

    public function testSetVariableValidation()
    {
        $this->expectException(Foolz\SphinxQL\Exception\SphinxQLException::class);
        $this->createHelper()->setVariable('invalid-name', 1)->compile();
    }

    public function testCallSnippetsValidation()
    {
        $this->expectException(Foolz\SphinxQL\Exception\SphinxQLException::class);
        $this->createHelper()->callSnippets('', 'rt', 'is');
    }

    public function testCallKeywordsValidation()
    {
        $this->expectException(Foolz\SphinxQL\Exception\SphinxQLException::class);
        $this->createHelper()->callKeywords('test case', 'rt', 2);
    }

    public function testCreateFunctionValidation()
    {
        $this->expectException(Foolz\SphinxQL\Exception\SphinxQLException::class);
        $this->createHelper()->createFunction('my_udf', 'INVALID', 'test_udf.so');
    }

    public function testNewHelperValidation()
    {
        $this->expectException(Foolz\SphinxQL\Exception\SphinxQLException::class);
        $this->createHelper()->showCreateTable('');
    }

    public function testKillValidation()
    {
        $this->expectException(Foolz\SphinxQL\Exception\SphinxQLException::class);
        $this->createHelper()->kill(0);
    }

    public function testSuggestOptionValidation()
    {
        $this->expectException(Foolz\SphinxQL\Exception\SphinxQLException::class);
        $this->createHelper()->callSuggest('teh', 'rt', array('' => 1));
    }

    public function testCapabilitiesAndSupports()
    {
        $caps = $this->createHelper()->getCapabilities();

        $this->assertInstanceOf(Foolz\SphinxQL\Capabilities::class, $caps);
        $this->assertNotEmpty($caps->getEngine());
        $this->assertTrue($this->createHelper()->supports('grouped_where'));
        $this->assertIsBool($this->createHelper()->supports('show_profile'));
    }

    public function testSupportsUnknownFeatureValidation()
    {
        $this->expectException(Foolz\SphinxQL\Exception\SphinxQLException::class);
        $this->createHelper()->supports('definitely_not_a_real_feature');
    }

    public function testRequireSupportValidation()
    {
        $helper = $this->createHelper();
        if ($helper->supports('call_qsuggest')) {
            $this->assertSame($helper, $helper->requireSupport('call_qsuggest'));

            return;
        }

        $this->expectException(Foolz\SphinxQL\Exception\UnsupportedFeatureException::class);
        $helper->requireSupport('call_qsuggest', 'testRequireSupportValidation()');
    }

    public function testShowVersionExecutionWhenSupported()
    {
        if (!TestUtil::supportsCommand($this->conn, 'SHOW VERSION')) {
            $this->markTestSkipped('SHOW VERSION is not supported by this engine.');
        }

        $rows = $this->createHelper()->showVersion()->execute()->getStored();
        $this->assertNotEmpty($rows);
    }

    public function testShowPluginsExecutionWhenSupported()
    {
        if (!TestUtil::supportsCommand($this->conn, 'SHOW PLUGINS')) {
            $this->markTestSkipped('SHOW PLUGINS is not supported by this engine.');
        }

        $rows = $this->createHelper()->showPlugins()->execute()->getStored();
        $this->assertIsArray($rows);
    }

    public function testSuggestExecutionWhenSupported()
    {
        if (!TestUtil::supportsCommand($this->conn, "CALL SUGGEST('teh', 'rt')")) {
            $this->markTestSkipped('CALL SUGGEST is not supported by this engine.');
        }

        $rows = $this->createHelper()->callSuggest('teh', 'rt')->execute()->getStored();
        $this->assertIsArray($rows);
    }

    public function testQSuggestExecutionWhenBuddySupported()
    {
        if (!$this->createHelper()->supports('call_qsuggest')) {
            $this->expectException(Foolz\SphinxQL\Exception\UnsupportedFeatureException::class);
            $this->createHelper()->callQSuggest('teh', 'rt');

            return;
        }

        if (!TestUtil::supportsBuddy($this->conn) || !TestUtil::supportsCommand($this->conn, "CALL QSUGGEST('teh', 'rt')")) {
            $this->markTestSkipped('CALL QSUGGEST runtime requires Manticore Buddy support.');
        }

        $rows = $this->createHelper()->callQSuggest('teh', 'rt')->execute()->getStored();
        $this->assertIsArray($rows);
    }

    public function testAutocompleteExecutionWhenBuddySupported()
    {
        if (!$this->createHelper()->supports('call_autocomplete')) {
            $this->expectException(Foolz\SphinxQL\Exception\UnsupportedFeatureException::class);
            $this->createHelper()->callAutocomplete('te', 'rt');

            return;
        }

        if (!TestUtil::supportsBuddy($this->conn) || !TestUtil::supportsCommand($this->conn, "CALL AUTOCOMPLETE('te', 'rt')")) {
            $this->markTestSkipped('CALL AUTOCOMPLETE runtime requires Manticore Buddy support.');
        }

        $rows = $this->createHelper()->callAutocomplete('te', 'rt')->execute()->getStored();
        $this->assertIsArray($rows);
    }
}
