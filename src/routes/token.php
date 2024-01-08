<?php

$pathname   = basename(__FILE__, '.php');
$controller = camelcase($pathname, true) . 'Controller';
$schema     = camelcase($pathname, true) . 'Schema';

require_once __DIR__ . "/../controllers/{$controller}.php";
require_once __DIR__ . "/../schemas/{$schema}.php";

$app->post("/{$pathname}", "\\{$controller}:generate")
    ->add($validation($schema::generate()));

$app->post("/{$pathname}/refresh", "\\{$controller}:refresh")
    ->add($authenticate_refresh)
    ->add($validation($schema::refresh()));
