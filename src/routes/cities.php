<?php

$pathname   = basename(__FILE__, '.php');
$controller = camelcase($pathname, true) . 'Controller';
$schema     = camelcase($pathname, true) . 'Schema';

require_once __DIR__ . "/../controllers/{$controller}.php";
require_once __DIR__ . "/../schemas/{$schema}.php";

$app->get("/{$pathname}", "\\{$controller}:getAll")
    ->add($authenticate);

$app->get("/{$pathname}/{id:[0-9]+}", "\\{$controller}:getDetail")
    ->add($authenticate);
