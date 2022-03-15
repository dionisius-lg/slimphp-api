<?php

return [
    'settings' => [
        'displayErrorDetails'    => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
    ],
    'socket.io' => [
		'base_url' => 'http://localhost:8001'
	],
	'attachment' => [
		'email_inbox'  => 'D:/Dionisius/laragon/www/slimphp-api/files/email/inbox/',
		'email_outbox' => 'D:/Dionisius/laragon/www/slimphp-api/files/email/outbox/',
		'info'         => 'D:/Dionisius/laragon/www/slimphp-api/files/info/',
	],
	'photo' => [
		'user' => 'D:/Dionisius/laragon/www/slimphp-api/files/photo/user/',
	],
	'jwt' => [
		'key'            => 's3cR3tT0k3n',
		'key_refresh'    => 's3cR3tR3fr3ShT0k3n',
		'algorithm'      => 'HS256',
		'live'           => 0, // token will apply after this value (in seconds)
		'expire'         => 3600, // token will expire after this value (in seconds) || 3h
		'expire_refresh' => 604800, // token will expire after this value (in seconds) || 1w
	],
	'database' => [
		'username' => 'root',
		'password' => '',
		'host'     => 'localhost',
		'dbname'   => 'db_test'
	]
];
