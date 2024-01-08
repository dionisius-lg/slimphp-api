<?php

use Psr\Container\ContainerInterface;
use Firebase\JWT\JWT as Jwt;
use Firebase\JWT\Key as JwtKey;

class TokenController extends ApiController
{
    /**
     *  __construct method
     *  variable initialization
     *  @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
        $this->table = 'users';
        $this->table_refresh = 'refresh_tokens';
        $this->jwt = $this->conf['jwt'];
    }

    /**
     *  generate method
     *  generate token by given auth
     */
    public function generate($request, $response)
    {
        $body = $request->getParsedBody();
        $required = ['username', 'password'];

        if (array_diff($required, array_keys($body)) == array_diff(array_keys($body), $required)) {
            $query = "SELECT * FROM {$this->table} WHERE username = :username AND is_active = :is_active LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':username', $body['username'], PDO::PARAM_STR);
            $stmt->bindValue(':is_active', '1', PDO::PARAM_INT);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $generated = false;

            if (!$user) {
                $handler = $this->ci->get('notFoundHandler');
                return $handler($request, $response);
            }

            if (password_verify($body['password'], $user['password'])) {
                $generated = $this->generateToken($user);

                if ($generated) {
                    $user_agent = 'unknown';

                    if (isset($_SERVER['HTTP_USER_AGENT'])) {
                        $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    }

                    $data_refresh = [
                        'user_id'    => $user['id'],
                        'user_agent' => $user_agent,
                        'ip_address' => client_ip(),
                        'token'      => $generated['token'],
                        'expired'    => $generated['token_expire'],
                        'updated'    => date('Y-m-d H:i:s'),
                    ];

                    $inserted = $this->insertDuplicateUpdateData($this->table_refresh, [$data_refresh]);

                    if ($inserted) {
                        $handler = $this->ci->get('successHandler');
                        return $handler($request, $response, ['total' => 1, 'data' => $generated]);
                    }
                }
            }

            $handler = $this->ci->get('unauthorizedHandler');
            return $handler($request, $response);
        }

        $handler = $this->ci->get('badRequestHandler');
        return $handler($request, $response, 'Invalid data');
    }

    /**
     *  refresh method
     *  refresh token by given auth
     */
    public function refresh($request, $response)
    {
        $decoded = $request->getAttribute('decoded');
        $body = $request->getParsedBody();
        $required = ['token'];

        if (array_diff($required, array_keys($body)) == array_diff(array_keys($body), $required)) {
            $token = false;

            try {
                $token = Jwt::decode(
                    // Token to be decoded in the JWT
                    $token,
                    // The signing key & algorithm used to sign the token,
                    // see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
                    new JwtKey($config['jwt']['key'], $config['jwt']['algorithm'])
                );

                $token = object2array($token);
            } catch (Exception $e) {
                if ($e->getMessage() == "Expired token") {
                    list($header, $token, $signature) = explode(".", $body['token']);
                    $token = json_decode(base64_decode($token));
                    $token = object2array($token);
                }
            }

            if ($token) {
                if ($token['data']['id'] !== $decoded['id'] || $token['data']['client_ip'] !== $decoded['client_ip']) {
                    $handler = $this->ci->get('unauthorizedHandler');
                    return $handler($request, $response);
                }

                $query = "SELECT * FROM {$this->table} WHERE id = :id AND is_active = :is_active";

                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':username', $token['data']['id'], PDO::PARAM_INT);
                $stmt->bindValue(':is_active', '1', PDO::PARAM_INT);
                $stmt->execute();

                $user = [
                    'total' => $stmt->rowCount(),
                    'data' => $stmt->fetch(PDO::FETCH_ASSOC)
                ];

                $query = "SELECT * FROM {$this->table_refresh} WHERE user_id = :user_id AND token = :token";

                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':user_id', $token['data']['id'], PDO::PARAM_INT);
                $stmt->bindValue(':token', $body['token'], PDO::PARAM_STR);
                $stmt->execute();

                $refresh_token = [
                    'total' => $stmt->rowCount(),
                    'data' => $stmt->fetch(PDO::FETCH_ASSOC)
                ];

                if ($user['total'] == 0 || $refresh_token['total'] == 0) {
                    $handler = $this->ci->get('unauthorizedHandler');
                    return $handler($request, $response);
                }

                $generated = $this->generateToken($user);

                if ($generated) {
                    $user_agent = 'unknown';

                    if (isset($_SERVER['HTTP_USER_AGENT'])) {
                        $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    }

                    $data_refresh = [
                        'user_id'    => $user['data']['id'],
                        'ip_address' => client_ip(),
                        'user_agent' => $user_agent,
                        'token'      => $generated['token'],
                        'expired'    => $generated['token_expire'],
                        'updated'    => date('Y-m-d H:i:s'),
                    ];

                    $inserted = $this->insertDuplicateUpdateData($this->table_refresh, [$data_refresh]);

                    if ($inserted) {
                        $handler = $this->ci->get('successHandler');
                        return $handler($request, $response, ['total' => 1, 'data' => $generated]);
                    }
                }

                $handler = $this->ci->get('unauthorizedHandler');
                return $handler($request, $response);
            }
        }

        $handler = $this->ci->get('badRequestHandler');
        return $handler($request, $response, 'Invalid data');
    }
}