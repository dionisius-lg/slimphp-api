<?php

$app->add(function ($request, $response, $next) use ($config) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    $body = $request->getParsedBody();
    $method = $request->getMethod();
    $status = $response->getStatusCode();

    if (strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }

    if (!empty($uri->getQuery())) {
        $path .= '?' . $uri->getQuery();
    }

    access_log($config, $status, $method, $path, json_encode($body));

    return $next($request, $response);
});