<?php

ini_set('date.timezone', 'Asia/Jakarta');
set_time_limit(120); // 2 minutes

// load vendor
require __DIR__ . '/vendor/autoload.php';

// load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// reqister config
$config = require __DIR__ . '/src/config.php';

// reqister app
$app = new \Slim\App($config);

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

// run app
$app->run();