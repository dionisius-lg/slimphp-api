<?php

$path = pathinfo(basename(__FILE__), PATHINFO_FILENAME);
$controller = camelcase($path, true) . 'Controller';
$schema = camelcase($path, true) . 'Schema';

require_once __DIR__ . "/../controllers/{$controller}.php";
require_once __DIR__ . "/../schemas/{$schema}.php";

$app->post("/{$path}", "\\{$controller}:auth")
    // validate request
    ->add($validation($schema::auth(), 'body'));

$app->post("/{$path}/refresh", "\\{$controller}:refreshAuth")
    // auth refresh token
    ->add($authenticate_refresh)
    // validate request
    ->add($validation($schema::refreshAuth(), 'body'));