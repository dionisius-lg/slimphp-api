<?php

use Firebase\JWT\JWT as Jwt;
use Firebase\JWT\Key as JwtKey;
use JsonSchema\Validator as JsonValidator;

$authenticate = function ($request, $response, $next) use ($config, $container) {
    $auth_key   = $request->getHeaderLine('X-Api-Key');
    $auth_token = $request->getHeaderLine('Authorization');
    $client_ip  = client_ip();
    $user_agent = 'unknown';

    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
    }

    if ($auth_key) {
        try {
            $decoded = json_decode(base64_decode(base64_decode($auth_key)));

            if (is_object($decoded)) {
                $decoded = object2array($decoded);
            }

            if (is_array_assoc($decoded) && count($decoded) === 3) {
                if ($decoded['secret'] === $config['secret_key'] && $decoded['date'] === date('Y-m-d') && is_numeric($decoded['user_id'])) {
                    $apic = new ApiController($container);
                    $stmt = $apic->dbconnect()->prepare("SELECT * FROM users WHERE id = :id AND is_active = :is_active");
                    $stmt->bindValue(':id', $decoded['user_id'], PDO::PARAM_INT);
                    $stmt->bindValue(':is_active', '1', PDO::PARAM_INT);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $user_data    = $stmt->fetch(PDO::FETCH_ASSOC);
                        $decoded_data = [
                            'id'         => $user_data['id'],
                            'client_ip'  => $client_ip,
                            'user_agent' => $user_agent,
                        ];

                        $request = $request->withAttribute('decoded', $decoded_data);
                        return $next($request, $response);
                    }
                }
            }

            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            $handler = $container['unauthorizedHandler'];
            return $handler($request, $response);
        }
    }

    if ($auth_token) {
        list($token) = sscanf($auth_token, 'Bearer %s');

        try {
            $decoded = Jwt::decode(
                // Token to be decoded in the JWT
                $token,
                // The signing key & algorithm used to sign the token,
                // see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
                new JwtKey($config['jwt']['key'], $config['jwt']['algorithm'])
            );

            $decoded = object2array($decoded);

            if ($decoded['data']['client_ip'] != $client_ip || !is_numeric($decoded['data']['id'])) {
                throw new Exception('Unauthorized');
            }

            $request = $request->withAttribute('decoded', $decoded['data']);
            return $next($request, $response);
        } catch (Exception $e) {
            $handler = $container['unauthorizedHandler'];
            return $handler($request, $response);
        }
    }

    $handler = $container['unauthorizedHandler'];
    return $handler($request, $response);
};

$authenticate_refresh = function ($request, $response, $next) use ($config, $container) {
    $auth_token = $request->getHeaderLine('Authorization');
    $client_ip  = client_ip();
    $user_agent = 'unknown';

    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
    }

    if ($auth_token) {
        list($token) = sscanf($auth_token, 'Bearer %s');

        try {
            $decoded = Jwt::decode(
                // Token to be decoded in the JWT
                $token,
                // The signing key & algorithm used to sign the token,
                // see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
                new JwtKey($config['jwt']['key_refresh'], $config['jwt']['algorithm'])
            );

            $decoded = object2array($decoded);

            if ($decoded['data']['client_ip'] != $client_ip || !is_numeric($decoded['data']['id'])) {
                throw new Exception('Unauthorized');
            }

            $request = $request->withAttribute('decoded', $decoded['data']);
            return $next($request, $response);
        } catch (Exception $e) {
            $handler = $container['unauthorizedHandler'];
            return $handler($request, $response);
        }
    }

    $handler = $container['unauthorizedHandler'];
    return $handler($request, $response);
};

$validation = function ($schema = false, $property = null) use ($config, $container) {
    return function ($request, $response, $next) use ($config, $container, $schema, $property) {
        if (is_array($schema) && array_key_exists('type', $schema)) {
            $validator = new JsonValidator();
            $data = $property === 'param' ? $request->getQueryParams() : $request->getParsedBody();

            try {
                $validate_schema = !is_object($schema) ? array2object($schema) : $schema;

                switch (true) {
                    case (is_array_multi($data) && $schema['type'] == 'array'):
                        for ($i = 0; $i < count($data); $i++) {
                            $data[$i] = array_intersect_key($data[$i], $schema['items']['properties']);
                        }

                        $validate_data = json_decode(json_encode($data), true);
                        break;
                    default:
                        $data = array_intersect_key($data, $schema['properties']);
                        $validate_data = (object) $data;
                        break;
                }

                $validator->coerce($validate_data, $validate_schema);

                if (!$validator->isValid()) {
                    $errors = $validator->getErrors();
                    print_r($errors); exit;
                    $message = "Error property {$errors[0]['property']}. {$errors[0]['message']}";
                    throw new Exception($message);
                }

                $schema_properties = $schema['type'] == 'array' ? $schema['items']['properties'] : $schema['properties'];

                $datetime_keys = [];
                $time_keys     = [];

                foreach ($schema_properties as $key => $val) {
                    if (array_key_exists('format', $val)) {
                        if ($val['format'] == 'datetime') {
                            array_push($datetime_keys, $key);
                        }

                        if ($val['format'] == 'time2') {
                            array_push($time_keys, $key);
                        }
                    }
                }

                if (!empty($datetime_keys)) {
                    if (is_array_multi($data) && $schema['type'] == 'array') {
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

                if (!empty($time_keys)) {
                    if (is_array_multi($data) && $schema['type'] == 'array') {
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

                return $next($request, $response);
            } catch (Exception $e) {
                $handler = $container['badRequestHandler'];
                return $handler($request, $response, $e->getmessage());
            }
        }

        return $next($request, $response);
    };
};