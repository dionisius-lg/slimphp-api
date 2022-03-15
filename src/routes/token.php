<?php

require_once __DIR__ .'/../controllers/TokenController.php';

$app->post('/token', '\TokenController:createToken');

$app->get('/token/refresh', '\TokenController:getRefreshToken');
