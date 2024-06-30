<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$container = $app->getContainer();

/**
 * Success 200 OK
 * @param {Request} $req, {Response} $res, {array} $data
 * @return {object} $result
 */
$container['successHandler'] = function ($container) {
    return function (Request $req, Response $res, $data = []) use ($container) {
        $result = [
            'request_time' => $_SERVER['REQUEST_TIME'],
            'status' => 200
        ];

        if (!empty($data) && is_array($data)) {
            if (array_key_exists('total', $data) && is_numeric($data['total'])) {
                $result['total'] = $data['total'];
            }

            if (array_key_exists('data', $data) && is_array($data['data'])) {
                $result['data'] = $data['data'];
            }

            if (array_key_exists('paging', $data) && is_array_assoc($data['paging'])) {
                $result['paging'] = $data['paging'];
            }
        }

        return $res->withHeader('Content-type', 'application/json')->withJson($result, $result['status']);
    };
};

/**
 * Success 201 Created
 * @param {Request} $req, {Response} $res, {array} $data
 * @return {object} $result
 */
$container['successCreatedHandler'] = function ($container) {
    return function (Request $req, Response $res, $data = []) use ($container) {
        $result = [
            'request_time' => $_SERVER['REQUEST_TIME'],
            'status' => 201
        ];

        if (!empty($data) && is_array($data)) {
            if (array_key_exists('total', $data) && is_numeric($data['total'])) {
                $result['total'] = $data['total'];
            }

            if (array_key_exists('data', $data) && is_array($data['data'])) {
                $result['data'] = $data['data'];
            }
        }

        return $res->withHeader('Content-type', 'application/json')->withJson($result, $result['status']);
    };
};

/**
 * Error 400 Bad Request
 * @param {Request} $req, {Response} $res, {string} $message
 * @return {object} $result
 */
$container['badRequestHandler'] = function ($container) {
    return function (Request $req, Response $res, $message = '') use ($container) {
        $result = [
            'request_time' => $_SERVER['REQUEST_TIME'],
            'status' => 400,
            'error' => 'Bad request'
        ];

        if (!empty($message) && is_string($message)) {
            $result['error'] .= ". {$message}";
        }

        return $res->withHeader('Content-type', 'application/json')->withJson($result, $result['status']);
    };
};

/**
 * Error 401 Unauthorized
 * @param {Request} $req, {Response} $res, {string} $message
 * @return {object} $result
 */
$container['unauthorizedHandler'] = function ($container) {
    return function (Request $req, Response $res, $message = '') use ($container) {
        $result = [
            'request_time' => $_SERVER['REQUEST_TIME'],
            'status' => 401,
            'error' => 'Unauthorized'
        ];

        if (!empty($message) && is_string($message)) {
            $result['error'] .= ". {$message}";
        }

        return $res->withHeader('Content-type', 'application/json')->withJson($result, $result['status']);
    };
};


/**
 * Error 403 Forbidden
 * @param {Request} $req, {Response} $res, {string} $message
 * @return {object} $result
 */
$container['forbiddenHandler'] = function ($container) {
    return function (Request $req, Response $res, $message = '') use ($container) {
        $result = [
            'request_time' => $_SERVER['REQUEST_TIME'],
            'status' => 403,
            'error' => 'Forbidden'
        ];

        if (!empty($message) && is_string($message)) {
            $result['error'] .= ". {$message}";
        }

        return $res->withHeader('Content-type', 'application/json')->withJson($result, $result['status']);
    };
};


/**
 * Error 404 Not Found
 * @param {Request} $req, {Response} $res, {string} $message
 * @return {object} $result
 */
$container['notFoundHandler'] = function ($container) {
    return function (Request $req, Response $res, $message = '') use ($container) {
        $result = [
            'request_time' => $_SERVER['REQUEST_TIME'],
            'status' => 404,
            'error'=> 'Not found'
        ];

        if (!empty($message) && is_string($message)) {
            $result['error'] .= ". {$message}";
        }

        return $res->withHeader('Content-type', 'application/json')->withJson($result, $result['status']);
    };
};

/**
 * Error 405 Method Not Allowed
 * @param {Request} $req, {Response} $res, {string} $method
 * @return {object} $result
 */
$container['notAllowedHandler'] = function ($container) {
    return function (Request $req, Response $res, $method) use ($container) {
        $result = [
            'request_time' => $_SERVER['REQUEST_TIME'],
            'status' => 405,
            'error' => 'Method not allowed'
        ];

        return $res->withHeader('Content-type', 'application/json')->withJson($result, $result['status']);
    };
};

/**
 * Error 500 Internal Server Error
 * @param {Request} $req, {Response} $res, {Exception} $except
 * @return {object} $result
 */
$container['errorHandler'] = function ($container) {
    return function (Request $req, Response $res, $except) use ($container) {
        $result = [
            'request_time' => $_SERVER['REQUEST_TIME'],
            'status' => 500,
            'error' => 'Internal server error'
        ];

        if ($except->getMessage()) {
            $result['error'] .= ". {$except->getMessage()}";
        }

        return $res->withHeader('Content-type', 'application/json')->withJson($result, $result['status']);
    };
};

/**
 * Global Config
 * @return {array} $config
 */
$container['config'] = function ($container) use ($config) {
    return $config;
};
