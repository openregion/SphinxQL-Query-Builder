<?php

namespace Foolz\SphinxQL\Tests;

use Foolz\SphinxQL\Drivers\Mysqli\Connection as MysqliConnection;
use Foolz\SphinxQL\Drivers\Pdo\Connection as PdoConnection;
use PDO;

class TestUtil
{
    /**
     * @return PdoConnection|MysqliConnection
     */
    public static function getConnectionDriver()
    {
        if ($GLOBALS['driver'] === 'Pdo') {
            return new class extends PdoConnection {
                public function connect()
                {
                    $connected = parent::connect();
                    $this->getConnection()->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);

                    return $connected;
                }
            };
        }

        return new MysqliConnection();
    }
}
