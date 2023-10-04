<?php

namespace OpenRegion\SphinxQL\Tests;

use OpenRegion\SphinxQL\Drivers\Mysqli\Connection as MysqliConnection;
use OpenRegion\SphinxQL\Drivers\Pdo\Connection as PdoConnection;

class TestUtil
{
    /**
     * @return PdoConnection|MysqliConnection
     */
    public static function getConnectionDriver()
    {
        $connection = '\\OpenRegion\\SphinxQL\\Drivers\\'.$GLOBALS['driver'].'\\Connection';

        return new $connection();
    }
}
