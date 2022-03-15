<?php

$container = $app->getContainer();
$error_message = '';

/**
 * Error Handler
 * @apiDefine InternalServerError
 */
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => executionTime($_SERVER['REQUEST_TIME']),
            'response_code'  => 500,
            'status'         => 'fail',
            'message'        => 'Internal Server Error'
        ];

        return $c['response']->withJson($result)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($result['response_code']);
    };
};

/**
 * Not Found Handler
 * @apiDefine NotFound Error
 */
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => executionTime($_SERVER['REQUEST_TIME']),
            'response_code'  => 404,
            'status'         => 'error',
            'message'        => 'Not Found'
        ];

        return $c['response']->withJson($result)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($result['response_code']);
    };
};

/**
 * Not Allowed Handler
 * @apiDefine NotAllowedError
 */
$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => executionTime($_SERVER['REQUEST_TIME']),
            'response_code'  => 405,
            'status'         => 'error',
            'message'        => 'Method Not Allowed'
        ];

        return $c['response']->withJson($result)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($result['response_code']);
    };
};

/**
 * Bad Request Handler
 * @apiDefine BadRequestError
 */
$container['badRequestHandler'] = function ($c) use ($error_message) {
    return function ($request, $response, $error_message = '') use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => executionTime($_SERVER['REQUEST_TIME']),
            'response_code'  => 400,
            'status'         => 'error',
            'message'        => (empty($error_message) ? 'Bad Request' : 'Bad Request. '.$error_message)
        ];

        return $c['response']->withJson($result)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($result['response_code']);
    };
};

/**
 * Unauthorized Handler
 * @apiDefine UnauthorizedError
 */
$container['unauthorizedHandler'] = function ($c) use ($error_message) {
    return function ($request, $response, $error_message = '') use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => executionTime($_SERVER['REQUEST_TIME']),
            'response_code'  => 401,
            'status'         => 'error',
            'message'        => (empty($error_message) ? 'Unauthorized' : 'Unauthorized. '.$error_message)
        ];

        return $c['response']->withJson($result)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($result['response_code']);
    };
};

/**
 * Forbidden Handler
 * @apiDefine ForbiddenError
 */
$container['forbiddenHandler'] = function ($c) use ($error_message) {
    return function ($request, $response, $error_message = '') use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => executionTime($_SERVER['REQUEST_TIME']),
            'response_code'  => 403,
            'status'         => 'error',
            'message'        => (empty($error_message) ? 'Forbidden' : 'Forbidden. '.$error_message)
        ];

        return $c['response']->withJson($result)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($result['response_code']);
    };
};

/**
 * Global Settings
 * @apiDefine GlobalSettings
 */
$container['globalSettings'] = function ($c) use ($app_settings) {
    $settings = $app_settings;

    return $settings;
};
