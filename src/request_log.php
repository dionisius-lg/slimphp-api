<?php

$log_dir = __DIR__ . '/../log/';
$log_name = 'request-'.date('Ymd').'.log';

createLog($log_dir.$log_name);
