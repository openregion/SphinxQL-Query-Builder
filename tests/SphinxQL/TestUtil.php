<?php

namespace Foolz\SphinxQL\Tests;

use Foolz\SphinxQL\Drivers\ConnectionInterface;
use Foolz\SphinxQL\Drivers\Mysqli\Connection as MysqliConnection;
use Foolz\SphinxQL\Drivers\Pdo\Connection as PdoConnection;

class TestUtil
{
    /**
     * @var null|string
     */
    private static $detectedSearchBuild;

    /**
     * @return PdoConnection|MysqliConnection
     */
    public static function getConnectionDriver()
    {
        $connection = '\\Foolz\\SphinxQL\\Drivers\\'.$GLOBALS['driver'].'\\Connection';

        return new $connection();
    }

    /**
     * @param ConnectionInterface|null $connection
     *
     * @return bool
     */
    public static function isSphinx3(ConnectionInterface $connection = null)
    {
        return self::getSearchBuild($connection) === 'SPHINX3';
    }

    /**
     * @param array $rows
     * @param array $columns
     *
     * @return array
     */
    public static function pickColumns(array $rows, array $columns)
    {
        return array_map(function (array $row) use ($columns) {
            $picked = array();
            foreach ($columns as $column) {
                if (array_key_exists($column, $row)) {
                    $picked[$column] = $row[$column];
                }
            }

            return $picked;
        }, $rows);
    }

    /**
     * @param ConnectionInterface|null $connection
     *
     * @return string|null
     */
    private static function getSearchBuild(ConnectionInterface $connection = null)
    {
        if (self::$detectedSearchBuild !== null) {
            return self::$detectedSearchBuild;
        }

        $fromEnv = strtoupper((string) getenv('SEARCH_BUILD'));
        if ($fromEnv !== '') {
            self::$detectedSearchBuild = $fromEnv;

            return self::$detectedSearchBuild;
        }

        if ($connection === null) {
            return null;
        }

        try {
            $rows = $connection->query('SELECT VERSION()')->getStored();
            $firstRow = isset($rows[0]) ? $rows[0] : array();
            $version = (string) reset($firstRow);
            $versionLower = strtolower($version);

            if (strpos($versionLower, 'manticore') !== false) {
                self::$detectedSearchBuild = 'MANTICORE';
            } elseif (preg_match('/^3\./', $version) || strpos($versionLower, 'sphinx 3') !== false) {
                self::$detectedSearchBuild = 'SPHINX3';
            } elseif (preg_match('/^2\./', $version) || strpos($versionLower, 'sphinx') !== false) {
                self::$detectedSearchBuild = 'SPHINX2';
            }
        } catch (\Exception $exception) {
            return null;
        }

        return self::$detectedSearchBuild;
    }
}
