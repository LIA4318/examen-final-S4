<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public $defaultGroup = 'default';

    public $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => __DIR__ . '/../../writable/database.db',
        'DBDriver'     => 'SQLite3',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8',
        'DBCollat'     => 'utf8_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => '',
        'foreignKeyChecks' => true,
    ];
}