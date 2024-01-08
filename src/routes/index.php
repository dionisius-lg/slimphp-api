<?php

$app->get('/', function ($request, $response) {
    $config = $this->get('config');
    $result = [
        'App' => 'SlimPHP API',
        'Description' => 'Provide service data for Desktop, Mobile, and Web App.',
        'Version' => array_key_exists('version', $config) ? $config['version'] : '1.0.0'
    ];

    return $response->withJson($result, 200)->withHeader('Content-type', 'application/json');
});