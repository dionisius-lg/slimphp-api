<?php

ini_set('date.timezone', 'Asia/Jakarta');

// load vendor
require __DIR__ . '/vendor/autoload.php';

use \Slim\App;
use \Dotenv\Dotenv;

// load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// reqister config
$config = require __DIR__ . '/src/config.php';

// reqister app
$app = new App($config);

// register helper
require __DIR__ . '/src/helper.php';

// register container
require __DIR__ . '/src/container.php';

// register middleware
require __DIR__ . '/src/middleware.php';

// register controller
require __DIR__ . '/src/controllers/Controller.php';

// register route
require __DIR__ . '/src/routes/index.php';

// register logger
require __DIR__ . '/src/logger.php';

// run app
$app->run();