<?php

$classname = "UserLevelsController";
$endpoint  = "user_levels";

require_once __DIR__ . "/../controllers/{$classname}.php";

$app->get("/{$endpoint}", "\\{$classname}:getAll")->add($mw_jwt);

$app->get("/{$endpoint}/{id:[0-9]+}", "\\{$classname}:getDetail")->add($mw_jwt);

$app->post("/{$endpoint}", "\\{$classname}:create")->add($mw_jwt);

$app->post("/{$endpoint}/multiple", "\\{$classname}:createMultiple")->add($mw_jwt);

$app->put("/{$endpoint}/{id:[0-9]+}", "\\{$classname}:update")->add($mw_jwt);

$app->delete("/{$endpoint}/{id:[0-9]+}", "\\{$classname}:delete")->add($mw_jwt);
