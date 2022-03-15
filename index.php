<?php
ini_set('date.timezone', 'Asia/Jakarta');
set_time_limit(120); // 2 minutes

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
// use \Psr\Http\Message\ServerRequestInterface as Request;
// use \Psr\Http\Message\ResponseInterface as Response;

// composer autoload
require __DIR__ . '/vendor/autoload.php';
// functions
require __DIR__ . '/src/functions.php';
// request log
require __DIR__ . '/src/request_log.php';
// validation
require __DIR__ . '/src/validator.php';

$app_settings = require __DIR__ . '/src/settings.php';

$app = new \Slim\App($app_settings);

// set up dependencies
require __DIR__ . '/src/dependencies.php';

// register middleware
require __DIR__ . '/src/middleware.php';

// register controllers
require __DIR__ . '/src/controllers/AppController.php';

// register routes
foreach (glob('src/routes/*.php') as $routefile) {
    include __DIR__ . '/' . $routefile;
}

// run app
$app->run();
