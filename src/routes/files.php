<?php

$path = pathinfo(basename(__FILE__), PATHINFO_FILENAME);
$controller = camelcase($path, true) . 'Controller';
$schema = camelcase($path, true) . 'Schema';

require_once __DIR__ . "/../controllers/{$controller}.php";
require_once __DIR__ . "/../schemas/{$schema}.php";

$app->get("/{$path}/{encrypted}", "\\{$controller}:download")
    // validate request
    ->add($validation($schema::download(), 'params'));