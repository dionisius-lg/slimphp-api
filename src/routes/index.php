<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $req, Response $res) {
    $config = $this->get('config');
    $result = ['App' => 'SlimPHP API'];

    return $res->withHeader('Content-type', 'application/json')->withJson($result, 200);
});

// load routes dynamically
$files = array_diff(scandir(dirname(__FILE__)), ['.', '..', basename(__FILE__)]);

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $filename = pathinfo($file, PATHINFO_FILENAME);
        require_once dirname(__FILE__) . '/' . $filename . '.php';
    }
}