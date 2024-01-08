<?php

ini_set('date.timezone', 'Asia/Jakarta');
set_time_limit(120); // 2 minutes

if (PHP_SAPI == 'cli-server') {
    // to help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];

    if (is_file($file)) {
        return false;
    }
}

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Tuupola\Middleware\Cors as Cors;

// composer autoload
require __DIR__ . '/vendor/autoload.php';

// reqister config
$config = require __DIR__ . '/src/config.php';
$app = new \Slim\App($config);

// register cors
$app->add(new Cors([
    'origin' => ['*'],
    'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']
]));

// register function
require __DIR__ . '/src/function.php';

// register dependency
require __DIR__ . '/src/dependency.php';

// register middleware
require __DIR__ . '/src/middleware.php';

// register controller
require __DIR__ . '/src/controllers/ApiController.php';

// register logger
require __DIR__ . '/src/logger.php';

// register routes
foreach (glob('src/routes/*.php') as $route_file) {
    include __DIR__ . '/' . $route_file;
}

// run app
$app->run();