<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->add(function (Request $req, Response $res, callable $next) {
    $uri = $req->getUri();
    $endpoint = $uri->getPath();
    $query = $uri->getQuery();
    $body = $req->getParsedBody();
    $method = $req->getMethod();
    $status = $res->getStatusCode();

    if (strpos($endpoint, '/') !== 0) {
        $endpoint = '/' . $endpoint;
    }

    if (!empty($query)) {
        $endpoint .= '?' . $query;
    }

    $body = mask_data($body);
    $body = json_encode($body);

    access_log($method, $endpoint, $body, $status);

    return $next($req, $res);
});