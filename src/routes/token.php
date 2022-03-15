<?php

$classname = "TokenController";
$endpoint  = "token";

require_once __DIR__ . "/../controllers/{$classname}.php";

$app->post("/{$endpoint}", "\\{$classname}:createToken");

$app->get("/{$endpoint}/refresh", "\\{$classname}:getRefreshToken");
