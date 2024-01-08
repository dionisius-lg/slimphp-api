<?php

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // allow the web server to send the content-length header
        'determineRouteBeforeAppMiddleware' => true, // allow middleware determine route
    ],
    'jwt' => [
        'key' => 'jWT53CRetT0K3n',
        'key_refresh' => 'jWT53CRetReFR3ShT0K3n',
        'algorithm' => 'HS256',
        'live' => '0', // token will apply after this value (in seconds)
        'expire' => '86400', // token will expire after this value (in seconds) || 24h
        'expire_refresh' => '604800', // token will expire after this value (in seconds) || 1w
    ],
    'database' => [
        'host' => 'localhost',
        'port' => '3306',
        'username' => 'root',
        'password' => '',
        'dbname' => 'test',
    ],
    'dir' => [
        'logger' => __DIR__ . '/../logs',
    ],
    'secret_key' => '5UP3RS3CR3TKEYAPP',
    'unique_key' => '**super_unique+key**',
    'version' => '1.0.0'
];