<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT as Jwt;
use \Firebase\JWT\Key;
use \JsonSchema\Validator;

/**
 *  verify authentification (jwt token)
 *  @param {Request} $req, {Response} $res,  $next
 */
$authenticate = function (Request $req, Response $res, callable $next) use ($config, $container) {
    $auth_header = $req->getHeaderLine('authorization');
    $client_ip = client_ip();
    $user_agent = 'unknown';

    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
    }

    if ($auth_header) {
        list($token) = sscanf($auth_header, 'Bearer %s');

        try {
            $decoded = Jwt::decode($token, new Key($config['jwt']['key'], $config['jwt']['algorithm']));
            $decoded = object2array($decoded);

            if ($decoded['data']['client_ip'] != $client_ip || !is_numeric($decoded['data']['id'])) {
                throw new Exception('Unauthorized');
            }

            $req = $req->withAttribute('decoded', $decoded['data']);

            return $next($req, $res);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $handler = $container['unauthorizedHandler'];
            return $handler($req, $res);
        }
    }

    $handler = $container['unauthorizedHandler'];
    return $handler($req, $res);
};

/**
 *  verify refresh authentification (jwt token)
 *  @param {Request} $req, {Response} $res, $next
 */
$authenticate_refresh = function (Request $req, Response $res, callable $next) use ($config, $container) {
    $auth_header = $req->getHeaderLine('authorization');
    $client_ip = client_ip();
    $user_agent = 'unknown';

    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
    }

    if ($auth_header) {
        list($token) = sscanf($auth_header, 'Bearer %s');

        try {
            $decoded = Jwt::decode($token, new Key($config['jwt']['refresh_key'], $config['jwt']['algorithm']));
            $decoded = object2array($decoded);

            if ($decoded['data']['client_ip'] != $client_ip || !is_numeric($decoded['data']['id'])) {
                throw new Exception('Unauthorized');
            }

            $req = $req->withAttribute('decoded', $decoded['data']);

            return $next($req, $res);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $handler = $container['unauthorizedHandler'];
            return $handler($req, $res);
        }
    }

    $handler = $container['unauthorizedHandler'];
    return $handler($req, $res);
};

/**
 *  Validation for given schema
 *  @param {Request} $req, {Response} $res, $next
 */
$validation = function ($schema = [], $property = '') use ($config, $container) {
    return function (Request $req, Response $res, callable $next) use ($config, $container, $schema, $property) {
        switch ($property) {
            case 'params':
                $data = array_merge($req->getQueryParams(), $req->getAttribute('route')->getArguments());
                break;
            case 'body':
                $data = $req->getParsedBody();
                break;
            default:
                return $next($req, $res);
                break;
        }

        try {
            if (!empty($schema)) {
                $validator = new Validator();
                $data = json_decode(json_encode($data));

                if (!is_object($schema)) {
                    $schema = array2object($schema);
                }

                $validator->coerce($data, $schema);

                if (!$validator->isValid()) {
                    $errors = $validator->getErrors();
                    $message = "Error property {$errors[0]['property']}. {$errors[0]['message']}";
                    throw new Exception($message);
                }

                if ($schema->type == 'array') {
                    $schema_properties = $schema->items->properties;
                } else {
                    $schema_properties = $schema->properties;
                }

                $datetime_keys = [];
                $time_keys = [];

                foreach ($schema_properties as $key => $val) {
                    if (array_key_exists('format', $val)) {
                        // custom validation for datetime (yyyy-mm-dd hh:ii:ss)
                        if ($val->format == 'datetime') {
                            array_push($datetime_keys, $key);
                        }

                        // custom validation for datetime (hh:ii:ss)
                        if ($val->format == 'time2') {
                            array_push($time_keys, $key);
                        }
                    }
                }

                // custom validation for datetime (yyyy-mm-dd hh:ii:ss)
                if (!empty($datetime_keys)) {
                    if (is_array_multi($data) && $schema->type == 'array') {
                        for ($i = 0; $i < count($data); $i++) {
                            foreach ($data[$i] as $key => $val) {
                                if (in_array($key, $datetime_keys)) {
                                    if (format_datetime_object($val) === false) {
                                        $message = "Error property [{$i}].{$key}. Invalid datetime \"{$val}\", expected format YYYY-MM-DD hh:mm:ss";
                                        throw new Exception($message);
                                        break;
                                    }
                                }
                            }
                        }
                    } else {
                        foreach ($data as $key => $val) {
                            if (in_array($key, $datetime_keys)) {
                                if (format_datetime_object($val) === false) {
                                    $message = "Error property {$key}. Invalid datetime \"{$val}\", expected format YYYY-MM-DD hh:mm:ss";
                                    throw new Exception($message);
                                    break;
                                }
                            }
                        }
                    }
                }

                // custom validation for datetime (hh:ii:ss)
                if (!empty($time_keys)) {
                    if (is_array_multi($data) && $schema->type == 'array') {
                        for ($i = 0; $i < count($data); $i++) {
                            foreach ($data[$i] as $key => $val) {
                                if (in_array($key, $time_keys)) {
                                    if (format_datetime_object($val) === false) {
                                        $message = "Error property [{$i}].{$key}. Invalid time \"{$val}\", expected format hh:mm";
                                        throw new Exception($message);
                                        break;
                                    }
                                }
                            }
                        }
                    } else {
                        foreach ($data as $key => $val) {
                            if (in_array($key, $time_keys)) {
                                if (format_datetime_object(date('Y-m-d') . $val . ':00') === false) {
                                    $message = "Error property {$key}. Invalid time \"{$val}\", expected format hh:mm";
                                    throw new Exception($message);
                                    break;
                                }
                            }
                        }
                    }
                }

                $data = object2array($data);

                // remove data key not in schema
                if (!empty($data)) {
                    switch (true) {
                        case (is_array_multi($data)):
                            for ($i = 0; $i < count($data); $i++) {
                                $data[$i] = array_intersect_key((array) $data[$i], (array) $schema_properties);
                            }
                            break;
                        default:
                            $data = array_intersect_key((array) $data, (array) $schema_properties);
                            break;
                    }
                }

                // rewrite request body with filtered schema data
                $req = $req->withParsedBody($data);
            }

            return $next($req, $res);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $handler = $container['badRequestHandler'];
            return $handler($req, $res, $error);
        }
    };
};