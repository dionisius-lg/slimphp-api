<?php

return [
    'settings' => [ // override slimphp settings
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // allow the web server to send the content-length header
        'determineRouteBeforeAppMiddleware' => true, // allow middleware determine route
    ],
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'name' => getenv('DB_NAME') ?: 'test',
    ],
    'jwt' => [
        'key' => getenv('JWT_KEY') ?: '',
        'refresh_key' => getenv('JWT_REFRESH_KEY') ?: '',
        'algorithm' => getenv('JWT_ALGORITHM') ?: '',
        'live' => getenv('JWT_LIVE') ?: '0', // token will apply after this value (in seconds)
        'expire' => getenv('JWT_EXPIRE') ?: '86400', // token will expire after this value (in seconds) (https://github.com/vercel/ms)
        'refresh_expire' => getenv('JWT_REFRESH_EXPIRE') ?: '604800', // token will expire after this value (in seconds) (https://github.com/vercel/ms)
    ],
    'secret_key' => 'mY5uP3rsEcR3tKEy',
    'dir' => [
        'logs' => __DIR__ . '/../logs',
        'files' =>getenv('DIR_FILE') ?: __DIR__ . '/../files',
    ]
];