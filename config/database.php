<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    */

    'default' => env('DB_CONNECTION', 'sqlsrv_web'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        'sqlsrv_web' => [
            'driver' => 'sqlsrv',
            'host' => env('SQLSRV_HOST', 'localhost'),
            'port' => env('SQLSRV_PORT', '1433'),
            'database' => env('SQLSRV_WEB_DATABASE', 'db_drill_webapp'),
            'username' => env('SQLSRV_USERNAME'),
            'password' => env('SQLSRV_PASSWORD'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'trust_server_certificate' => env('SQLSRV_TRUST_SERVER_CERTIFICATE', false),
        ],

        'sqlsrv_log' => [
            'driver' => 'sqlsrv',
            'host' => env('SQLSRV_HOST', 'localhost'),
            'port' => env('SQLSRV_PORT', '1433'),
            'database' => env('SQLSRV_LOG_DATABASE', 'db_drill_logs'),
            'username' => env('SQLSRV_USERNAME'),
            'password' => env('SQLSRV_PASSWORD'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'trust_server_certificate' => env('SQLSRV_TRUST_SERVER_CERTIFICATE', false),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('MYSQL_HOST', '127.0.0.1'),
            'port' => env('MYSQL_PORT', '3306'),
            'database' => env('MYSQL_DATABASE', 'ehelpdesk_dnia'),
            'username' => env('MYSQL_USERNAME'),
            'password' => env('MYSQL_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

    ],

];
