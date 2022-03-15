<?php

$app->get('/', function($request, $response) {
	$result = [
		'Api Name' => 'PHP API',
		'Description' => 'Provide service data for Desktop, Mobile, and Web App.',
		'Version' => '1.0'
	];
	
	return $response->withJson($result)
			->withHeader('Content-Type', 'application/json');
});