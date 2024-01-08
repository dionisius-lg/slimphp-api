<?php

$container = $app->getContainer();

/**
 * Success Handler
 * @param {object} $request, {object} $response, {array} $args
 * @return {object} $result
 */
$container['successHandler'] = function ($c) {
    return function ($request, $response, $args = []) use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => execution_time($_SERVER['REQUEST_TIME']),
            'response_code'  => 200,
            'success'        => true
        ];

        if (!empty($args) && is_array($args)) {
            if (array_key_exists('total', $args) && is_numeric($args['total'])) {
                $result['total'] = $args['total'];
            }

            if (array_key_exists('data', $args) && is_array($args['data'])) {
                $result['data'] = $args['data'];
            }

            if (array_key_exists('paging', $args) && is_array_assoc($args['paging'])) {
                $result['paging'] = $args['paging'];
            }

            if (array_key_exists('code', $args) && in_array($args['code'], [200, 201])) {
                $result['response_code'] = $args['code'];
            }
        }

        return $c['response']->withJson($result, $result['response_code'])->withHeader('Content-type', 'application/json');
    };
};

/**
 * Error Handler
 * @param {object} $request, {object} $response, {object} $excp
 * @return {object} $result
 */
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exeption) use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => execution_time($_SERVER['REQUEST_TIME']),
            'response_code'  => 500,
            'success'        => false,
            'message'        => 'Internal server error'
        ];

        return $c['response']->withJson($result, $result['response_code'])->withHeader('Content-type', 'application/json');
    };
};

/**
 * Not Found Handler
 * @param {object} $request, {object} $response, {string} $message
 * @return {object} $result
 */
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response, $message = '') use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => execution_time($_SERVER['REQUEST_TIME']),
            'response_code'  => 404,
            'success'        => false,
            'message'        => 'Not found'
        ];

        if (!empty($message) && is_string($message)) {
            $result['message'] .= ". {$message}";
        }

        return $c['response']->withJson($result, $result['response_code'])->withHeader('Content-type', 'application/json');
    };
};

/**
 * Not Allowed Handler
 * @param {object} $request, {object} $response, {string} $methods
 * @return {object} $result
 */
$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => execution_time($_SERVER['REQUEST_TIME']),
            'response_code'  => 405,
            'success'        => false,
            'message'        => 'Method not allowed'
        ];

        return $c['response']->withJson($result, $result['response_code'])->withHeader('Content-type', 'application/json');
    };
};

/**
 * Bad Request Handler
 * @param {object} $request, {object} $response, {string} $message
 * @return {object} $result
 */
$container['badRequestHandler'] = function ($c) {
    return function ($request, $response, $message = '') use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => execution_time($_SERVER['REQUEST_TIME']),
            'response_code'  => 400,
            'success'        => false,
            'message'        => 'Bad request'
        ];

        if (!empty($message) && is_string($message)) {
            $result['message'] .= ". {$message}";
        }

        return $c['response']->withJson($result, $result['response_code'])->withHeader('Content-type', 'application/json');
    };
};

/**
 * Unauthorized Handler
 * @param {object} $request, {object} $response, {string} $message
 * @return {object} $result
 */
$container['unauthorizedHandler'] = function ($c) {
    return function ($request, $response, $message = '') use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => execution_time($_SERVER['REQUEST_TIME']),
            'response_code'  => 401,
            'success'        => false,
            'message'        => 'Unauthorized'
        ];

        if (!empty($message) && is_string($message)) {
            $result['message'] .= ". {$message}";
        }

        return $c['response']->withJson($result, $result['response_code'])->withHeader('Content-type', 'application/json');
    };
};

/**
 * Forbidden Handler
 * @param {object} $request, {object} $response, {string} $message
 * @return {object} $result
 */
$container['forbiddenHandler'] = function ($c) {
    return function ($request, $response, $message = '') use ($c) {
        $result = [
            'request_time'   => $_SERVER['REQUEST_TIME'],
            'execution_time' => execution_time($_SERVER['REQUEST_TIME']),
            'response_code'  => 403,
            'success'        => false,
            'message'        => 'Forbidden'
        ];

        if (!empty($message) && is_string($message)) {
            $result['message'] .= ". {$message}";
        }

        return $c['response']->withJson($result, $result['response_code'])->withHeader('Content-type', 'application/json');
    };
};

/**
 * Global Config
 * @return {array} $config
 */
$container['config'] = function ($c) use ($config) {
    return $config;
};
