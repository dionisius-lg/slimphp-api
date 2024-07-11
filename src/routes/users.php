<?php

$path = pathinfo(basename(__FILE__), PATHINFO_FILENAME);
$controller = camelcase($path, true) . 'Controller';
$schema = camelcase($path, true) . 'Schema';

require_once __DIR__ . "/../controllers/{$controller}.php";
require_once __DIR__ . "/../schemas/{$schema}.php";

$app->get("/{$path}", "\\{$controller}:getAll")
    // auth token
    ->add($authenticate);

$app->get("/{$path}/{id}", "\\{$controller}:getDetail")
    // auth token
    ->add($authenticate)
    // validate request
    ->add($validation($schema::detail(), 'params'));

$app->post("/{$path}", "\\{$controller}:insert")
    // auth token
    ->add($authenticate)
    // validate request
    ->add($validation($schema::insert(), 'body'));

$app->put("/{$path}/{id}", "\\{$controller}:update")
    // auth token
    ->add($authenticate)
    // validate request
    ->add($validation($schema::update(), 'body'));

$app->delete("/{$path}/{id}", "\\{$controller}:delete")
    // auth token
    ->add($authenticate)
    // validate request
    ->add($validation($schema::detail(), 'params'));

$app->post("/{$path}/many", "\\{$controller}:insertMany")
    // auth token
    ->add($authenticate)
    // validate request
    ->add($validation($schema::insertMany(), 'body'));

$app->post("/{$path}/many/update", "\\{$controller}:insertManyUpdate")
    // auth token
    ->add($authenticate)
    // validate request
    ->add($validation($schema::insertManyUpdate(), 'body'));

$app->get("/{$path}/{id}/photo", "\\{$controller}:getPhoto")
    // auth token
    ->add($authenticate)
    // validate request
    ->add($validation($schema::detail(), 'params'));

$app->post("/{$path}/{id}/photo", "\\{$controller}:insertPhoto")
    // auth token
    ->add($authenticate)
    // validate request
    ->add($validation($schema::detail(), 'params'));