<?php

use Firebase\JWT\JWT as JWT;

$jwt_middleware = function ($request, $response, $next) use ($app_settings, $container) {
    $header = $request->getHeaderLine('authorization');
    $result = [];
    
    if ($header) {
        list($auth_token) = sscanf($header, 'Bearer %s');

        if ($auth_token) {
            try {
                $decoded = JWT::decode(
                    $auth_token, //Token to be decoded in the JWT
                    base64_decode($app_settings['jwt']['key']), // The signing key
                    [$app_settings['jwt']['algorithm']] // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
                );

                $client_ip  = getClientIp();
                $user_agent = $_SERVER['HTTP_USER_AGENT'];

                if ($decoded->data->client_ip != $client_ip || $decoded->data->user_agent != $user_agent) {
                    $handler = $container['unauthorizedHandler']; 
                    return $handler($request, $response);
                }

                $response = $next($request, $response);
            } catch (Exception $e) {
                $handler = $container['unauthorizedHandler']; 
                return $handler($request, $response);
            }
        } else {
            $handler = $container['forbiddenHandler']; 
            return $handler($request, $response);
        }
    } else {        
        $handler = $container['forbiddenHandler']; 
        return $handler($request, $response);
    }

    return $response;
};
