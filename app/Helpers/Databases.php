<?php

namespace App\Helpers;

use DB;
use PDO;

class Databases {

    /**
     * Set database connection on the fly, from database saved parameters.
     */
    public static function setConnection($params) 
    {
        config([
            'database.connections.onthefly' => [
                'driver' => $params->driver,
                'host' => $params->host,
                'port' => $params->port,
                'database' => $params->database,
                'username' => $params->username,
                'password' => $params->password,
                'charset' => 'utf8',
            ]
        ]);

        return DB::connection('onthefly');
    }
}