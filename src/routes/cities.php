<?php

$classname = "CitiesController";
$endpoint  = "cities";

require_once __DIR__ . "/../controllers/{$classname}.php";

$app->get("/{$endpoint}", "\\{$classname}:getAll")->add($jwt_middleware);

$app->get("/{$endpoint}/{id:[0-9]+}", "\\{$classname}:getDetail")->add($jwt_middleware);

$app->post("/{$endpoint}", "\\{$classname}:create")->add($jwt_middleware);

$app->post("/{$endpoint}/multiple", "\\{$classname}:createMultiple")->add($jwt_middleware);

$app->put("/{$endpoint}/{id:[0-9]+}", "\\{$classname}:update")->add($jwt_middleware);

$app->delete("/{$endpoint}/{id:[0-9]+}", "\\{$classname}:delete")->add($jwt_middleware);
