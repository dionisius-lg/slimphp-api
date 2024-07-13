<?php

// load vendor
require __DIR__ . '/vendor/autoload.php';

use \Phinx\Migration\AbstractMigration;
use \Dotenv\Dotenv;

// load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// define paths
$migrations_path = __DIR__ . '/db/migrations';
$seeds_path = __DIR__ . '/db/seeds';

// function to create a directory if it doesn't exist
function check_dir($path) {
    if (!is_dir($path)) {
        // create directory with 777 permissions and recursive flag
        mkdir($path, 0777, true);
    }
}

check_dir($migrations_path);
check_dir($seeds_path);

return [
    'paths' => [
        'migrations' => $migrations_path,
        'seeds' => $seeds_path,
    ],
    'environments' => [
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