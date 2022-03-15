<?php

require_once __DIR__ .'/../controllers/UsersController.php';

$app->get('/users', '\UsersController:getAll')->add($mw_jwt);

$app->get('/users/{id:[0-9]+}', '\UsersController:getDetail')->add($mw_jwt);

$app->post('/users', '\UsersController:create')->add($mw_jwt);

$app->post('/users/multiple', '\UsersController:createMultiple')->add($mw_jwt);

$app->put('/users/{id:[0-9]+}', '\UsersController:update')->add($mw_jwt);

$app->delete('/users/{id:[0-9]+}', '\UsersController:delete')->add($mw_jwt);
