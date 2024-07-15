<?php

// See https://hrportal.readthedocs.io/en/latest/index.html for more about documentation.

// load vendor
require __DIR__ . '/vendor/autoload.php';

use \Phinx\Migration\AbstractMigration;
use \Dotenv\Dotenv;

// load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$paths = [
    'migrations' => __DIR__ . '/db/migrations',
    'seeds' => __DIR__ . '/db/seeds'
];

foreach($paths as $path) {
    if (!is_dir($path)) {
        // create directory with 777 permissions and recursive flag
        mkdir($path, 0777, true);
    }
}

return [
    'paths' => [
        'migrations' => $paths['migrations'],
        'seeds' => $paths['seeds'],
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database' => 'development',
        'development' => [
            'adapter' => getenv('DB_ADAPTER') ?: 'mysql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '3306',
            'user' => getenv('DB_USERNAME') ?: 'root',
            'pass' => getenv('DB_PASSWORD') ?: '',
            'name' => getenv('DB_NAME') ?: 'test',
            'charset' => 'utf8',
        ],
        'production' => [
            'adapter' => getenv('DB_ADAPTER') ?: 'mysql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '3306',
            'user' => getenv('DB_USERNAME') ?: 'root',
            'pass' => getenv('DB_PASSWORD') ?: '',
            'name' => getenv('DB_NAME') ?: 'test',
            'charset' => 'utf8',
        ],
    ],
    'migration_base_class' => AbstractMigration::class,
];