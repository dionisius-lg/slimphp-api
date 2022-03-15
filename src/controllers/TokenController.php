<?php

use Firebase\JWT\JWT as JWT;

class TokenController extends AppController
{
	public function __construct(Slim\Container $ci)
	{
		parent::__construct($ci);
		$this->table = 'users';
		$this->jwt = $this->ci->get('globalSettings')['jwt'];
	}

	/**
	 *  token method
	 *  get token from given username & password
	 */
	public function createToken($request, $response)
	{
		$data_temp = $request->getParsedBody();
		$param     = [
			'username' => null,
			'password' => null
		];

		foreach ($data_temp as $key => $val) {
			if (!empty($val)) {
				$param[$key] = $val;
			}
		}

		if (empty($param['username']) || empty($param['password'])) {
			$handler = $this->ci->get('badRequestHandler');
			return $handler($request, $response);
		}

		$stmt = $this->con->prepare("SELECT * FROM ".$this->table." WHERE username = :username LIMIT 1");
		$stmt->bindValue(':username', $param['username'], PDO::PARAM_STR);
		$stmt->execute();
		$user = $stmt->fetch();

		if (!$user) {
			$handler = $this->ci->get('unauthorizedHandler');
			return $handler($request, $response);
		}

		if (password_verify($param['password'], $user['password'])) {
            $issued_at   = time();
            $token_id    = base64_encode(mcrypt_create_iv(32));
            $server_name = $_SERVER['REMOTE_ADDR'];
            $not_before  = $issued_at + $this->jwt['live'];
            $expire      = $not_before + $this->jwt['expire'];
            $client_ip   = getClientIp();
            $user_agent  = $_SERVER['HTTP_USER_AGENT'];

			$data = [
				'iat'  => $issued_at, // Issued at: time when the token was generated
				'jti'  => $token_id, // Json Token Id: an unique identifier for the token
				'iss'  => $server_name, // Issuer
				'nbf'  => $not_before, // Not before
				'exp'  => $expire, // Expire
				'data' => [ // Data related to the signer user
					'id'            => $user['id'],
					'username'      => $user['username'],
					'user_level_id' => $user['user_level_id'],
					'client_ip'     => $client_ip,
					'user_agent'    => $user_agent
				]
			];

			$encoded = JWT::encode(
				$data, //Data to be encoded in the JWT
				base64_decode($this->jwt['key']), // The signing key
				$this->jwt['algorithm'] // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
			);

            $refresh_encoded = $this->createRefreshToken($data);

			$result = [
				'request_time'   => $this->request_time,
				'execution_time' => executionTime($this->request_time),
				'response_code'  => 200,
				'status'         => 'success',
				'total_data'     => 1,
				'data'           => [
					'id'                   => $user['id'],
					'token'                => $encoded,
					'token_expire'         => date('Y-m-d H:i:s', $expire),
                    'refresh_token'        => $refresh_encoded['token'],
					'refresh_token_expire' => date('Y-m-d H:i:s', $refresh_encoded['expire'])
				]
			];

			return $response->withJson($result)
					->withHeader('Content-Type', 'application/json');
		} else {
			$handler = $this->ci->get('unauthorizedHandler');
			return $handler($request, $response);
		}
	}

    /**
	 * createRefreshToken method
	 * get token from refresh
	 */
	private function createRefreshToken($data)
	{
        $data['exp'] = $data['nbf'] + $this->jwt['expire_refresh'];

        $encoded = JWT::encode(
            $data, //Data to be encoded in the JWT
            base64_decode($this->jwt['key_refresh']), // The signing key
            $this->jwt['algorithm'] // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
        );

        return [
            'token'  => $encoded,
            'expire' => $data['exp']
        ];
	}

	/**
	 * getRefreshToken method
	 * get token from refresh
	 */
	public function getRefreshToken($request, $response)
	{
		$header = $request->getHeaderLine('authorization');

        list($auth_token) = sscanf($header, 'Bearer %s');

        if ($auth_token) {
            try {
                $decoded = JWT::decode(
                    $auth_token, //Token to be decoded in the JWT
                    base64_decode($this->jwt['key_refresh']), // The signing key
                    [$this->jwt['algorithm']] // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
                );

                $client_ip  = getClientIp();
                $user_agent = $_SERVER['HTTP_USER_AGENT'];

                if ($decoded->data->client_ip != $client_ip || $decoded->data->user_agent != $user_agent) {
                    $handler = $this->ci->get('unauthorizedHandler');
                    return $handler($request, $response);
                }

                $stmt = $this->con->prepare("SELECT * FROM ".$this->table." WHERE id = :id");
                $stmt->bindValue(':id', $decoded->data->id, PDO::PARAM_INT);
                $stmt->execute();

                $count = $stmt->rowCount();

                if ($count == 0) {
                    $handler = $this->ci->get('notFoundHandler');
                    return $handler($request, $response);
                }

                $issued_at   = time();
                $token_id    = base64_encode(mcrypt_create_iv(32));
                $server_name = $_SERVER['REMOTE_ADDR'];
                $not_before  = $issued_at + $this->jwt['live'];
                $expire      = $not_before + $this->jwt['expire'];
                $client_ip   = getClientIp();
                $user_agent  = $_SERVER['HTTP_USER_AGENT'];

                $data = [
                    'iat'  => $issued_at, // Issued at: time when the token was generated
                    'jti'  => $token_id, // Json Token Id: an unique identifier for the token
                    'iss'  => $server_name, // Issuer
                    'nbf'  => $not_before, // Not before
                    'exp'  => $expire, // Expire
                    'data' => (array) $decoded->data // Data related to the signer user
                ];

                $encoded = JWT::encode(
                    $data, //Data to be encoded in the JWT
                    base64_decode($this->jwt['key']), // The signing key
                    $this->jwt['algorithm'] // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
                );

                $refresh_encoded = $this->createRefreshToken($data);

                $result = [
                    'request_time'   => $this->request_time,
                    'execution_time' => executionTime($this->request_time),
                    'response_code'  => 200,
                    'status'         => 'success',
                    'total_data'     => 1,
                    'data'           => [
                        'token'                => $encoded,
                        'token_expire'         => date('Y-m-d H:i:s', $expire),
                        'refresh_token'        => $refresh_encoded['token'],
                        'refresh_token_expire' => date('Y-m-d H:i:s', $refresh_encoded['expire'])
                    ]
                ];

                return $response->withJson($result)
                        ->withHeader('Content-Type', 'application/json');
			} catch (Exception $e) {
                $handler = $this->ci->get('unauthorizedHandler');
		        return $handler($request, $response);
            }
        }

        $handler = $this->ci->get('unauthorizedHandler');
		return $handler($request, $response);
    }
}
