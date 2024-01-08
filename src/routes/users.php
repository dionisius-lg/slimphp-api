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

$app->post("/{$pathname}", "\\{$controller}:insert")
    ->add($authenticate)
    ->add($validation($schema::insert()));

$app->put("/{$pathname}/{id:[0-9]+}", "\\{$controller}:update")
    ->add($authenticate)
    ->add($validation($schema::update()));

$app->delete("/{$pathname}/{id:[0-9]+}", "\\{$controller}:delete")
    ->add($authenticate);

$app->post("/{$pathname}/many", "\\{$controller}:insertMany")
    ->add($authenticate)
    ->add($validation($schema::insertMany()));

$app->post("/{$pathname}/update", "\\{$controller}:insertManyUpdate")
    ->add($authenticate)
    ->add($validation($schema::insertManyUpdate()));

$app->put("/{$pathname}/{id:[0-9]+}/password", "\\{$controller}:updatePassword")
    ->add($authenticate);
