<?php

use \Psr\Container\ContainerInterface as Container;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT as Jwt;
use \Firebase\JWT\Key as Key;

class TokenController extends Controller {

    /**
     *  variable initialization
     *  @param {Container} $cont
     */
    public function __construct(Container $cont) {
        parent::__construct($cont);
        $this->table = 'users';
        $this->table_refresh_tokens = 'refresh_tokens';
    }

    /**
     *  create new token
     *  @param {array} $data
     *  @return {array} $result
     */
    private function createToken($data) {
        if (is_array_assoc($data)) {
            $issued_at = time();
            $token_id = base64_encode(mcrypt_create_iv(32));
            $server_name = $_SERVER['REMOTE_ADDR'];
            $not_before = $issued_at + $this->conf['jwt']['live'];
            $expire = $not_before + $this->conf['jwt']['expire'];
            $client_ip = client_ip();
            $user_agent = 'unknown';

            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
            }

            $payload = [
                'iat' => $issued_at, // Issued at: time when the token was generated
                'jti' => $token_id, // Json Token Id: an unique identifier for the token
                'iss' => $server_name, // Issuer
                'aud' => $client_ip, // Audience
                'nbf' => $not_before, // Not before
                'exp' => $expire, // Expire
                'data' => [ // Data related to the signer user
                    'id' => $data['id'],
                    'client_ip' => $client_ip,
                    'user_agent' => $user_agent,
                ]
            ];

            $encoded = Jwt::encode($payload, $this->conf['jwt']['key'], $this->conf['jwt']['algorithm']);
            $refresh_encoded = $this->refreshToken($payload['data']);

            if ($refresh_encoded) {
                $result = [
                    'id' => $data['id'],
                    'token' => $encoded,
                    'token_expire' => date('Y-m-d H:i:s', $expire),
                    'refresh_token' => $refresh_encoded['token'],
                    'refresh_token_expire' => date('Y-m-d H:i:s', $refresh_encoded['expire'])
                ];

                return $result;
            }
        }

        return false;
    }

    /**
     *  refresh token
     *  @param {array} $data
     *  @return {array} $result
     */
    private function refreshToken($data) {
        if (is_array_assoc($data)) {
            $issued_at = time();
            $token_id = base64_encode(mcrypt_create_iv(32));
            $server_name = $_SERVER['REMOTE_ADDR'];
            $not_before = $issued_at + $this->conf['jwt']['live'];
            $expire = $not_before + $this->conf['jwt']['refresh_expire'];

            $payload = [
                'iat' => $issued_at, // Issued at: time when the token was generated
                'jti' => $token_id, // Json Token Id: an unique identifier for the token
                'iss' => $server_name, // Issuer
                'aud' => client_ip(), // Audience
                'nbf' => $not_before, // Not before
                'exp' => $expire, // Expire
                'data' => $data // Data related to the signer user
            ];

            $encoded = Jwt::encode($payload, $this->conf['jwt']['refresh_key'], $this->conf['jwt']['algorithm']);

            $result = [
                'token' => $encoded,
                'expire' => $expire
            ];

            return $result;
        }

        return false;
    }

    /**
     *  validate auth request
     *  @param {Request} $req, {Response} $res
     *  @return {array} $handler
     */
    public function auth(Request $req, Response $res) {
        $data = $req->getParsedBody();

        $user = $this->dbGetDetail($this->table, [
            'username' => $data['username'],
            'is_active' => 1
        ]);

        if ($user['total_data'] == 0) {
            $handler = $this->cont->get('unauthorizedHandler');
            return $handler($req, $res, 'Username not found');
        }

        if (password_verify($data['password'], $user['data']['password'])) {
            $data = $this->createToken($user['data']);

            if ($data) {
                $user_agent = 'unknown';

                if (isset($_SERVER['HTTP_USER_AGENT'])) {
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                }

                $refresh_token_data = [
                    'user_id' => $user['data']['id'],
                    'user_agent' => $user_agent,
                    'ip_address' => client_ip(),
                    'token' => $data['token'],
                    'expired' => $data['token_expire']
                ];

                $this->dbInsertManyUpdate($this->table_refresh_tokens, [$refresh_token_data]);

                $handler = $this->cont->get('successHandler');
                return $handler($req, $res, ['total_data' => 1, 'data' => $data]);
            }
        }

        $handler = $this->cont->get('unauthorizedHandler');
        return $handler($req, $res);
    }

    /**
     *  refresh token by given auth
     *  @param {Request} $req, {Response} $res
     *  @return {array} $handler
     */
    public function refreshAuth(Request $req, Response $res) {
        $decoded = $req->getAttribute('decoded');
        $token = $req->getParsedBody()['token'];
        $token_decoded = [];

        try {
            $token_decoded = Jwt::decode($token, new Key($this->conf['jwt']['key'], $this->conf['jwt']['algorithm']));
            $token_decoded = object2array($token_decoded);
        } catch (\Exception $e) {
            $error = $e->getMessage();

            if ($error == 'Expired token') {
                $token_list = explode(".", $token);

                if (count($token_list) === 3) {
                    list($header, $user_token, $signature) = $token_list;
                    $token_decoded = json_decode(base64_decode($user_token));
                    $token_decoded = object2array($token_decoded);
                }
            }
        }

        if (!array_key_exists('data', $token_decoded)) {
            $handler = $this->cont->get('unauthorizedHandler');
            return $handler($req, $res);
        }

        if ($decoded != $token_decoded['data']) {
            $handler = $this->cont->get('unauthorizedHandler');
            return $handler($req, $res);
        }

        $user = $this->dbGetDetail($this->table, [
            'id' => $decoded['id'],
            'is_active' => 1
        ]);

        $refresh_token = $this->dbGetDetail($this->table_refresh_tokens, [
            'user_id' => $decoded['id'],
            'token' => $token
        ]);

        if ($user['total_data'] == 0 || $refresh_token['total_data'] == 0) {
            $handler = $this->cont->get('unauthorizedHandler');
            return $handler($req, $res);
        }

        $data = $this->createToken($user['data']);

        if ($data) {
            $user_agent = 'unknown';

            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
            }

            $refresh_token_data = [
                'user_id' => $user['data']['id'],
                'user_agent' => $user_agent,
                'ip_address' => client_ip(),
                'token' => $data['token'],
                'expired' => $data['token_expire']
            ];

            $this->dbInsertManyUpdate($this->table_refresh_tokens, [$refresh_token_data]);

            $handler = $this->cont->get('successHandler');
            return $handler($req, $res, ['total_data' => 1, 'data' => $data]);
        }

        $handler = $this->cont->get('unauthorizedHandler');
        return $handler($req, $res);
    }

}