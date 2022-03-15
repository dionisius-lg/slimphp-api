<?php

use Firebase\JWT\JWT as JWT;

if (!function_exists('getallheaders')) {
    function getallheaders() {
       $headers = array ();

       foreach ($_SERVER as $name => $value) {
           if (substr($name, 0, 5) == 'HTTP_') {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
       }

       return $headers;
    }
}

/**
 *  createLog method
 *  create log for every request (input request info to .log file)
 *  @param string $targetFile
 */
if (!function_exists('createLog')) {
	function createLog($targetFile) {
		$headers = '';

		foreach (getallheaders() as $name => $value) {
			$headers .= "$name: $value\n";
		}

		$data = sprintf(
			"%s \n%s %s %s %s\nHTTP headers:\n%s",
			date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
			getClientIp(),
			$_SERVER['REQUEST_METHOD'],
			$_SERVER['REQUEST_URI'],
			$_SERVER['SERVER_PROTOCOL'],
			$headers
		);

		$data .= "Request body:\n";
	
		file_put_contents(
			$targetFile,
			$data . file_get_contents('php://input') . "\n========================================\n",
			FILE_APPEND // always update (not replace)
		);
	}
}

/**
 *  getClientIp method
 *  get client's IP Address from request
 *  @return string $ipaddress
 */
if (!function_exists('getClientIp')) {
	function getClientIp() {
		if (getenv('HTTP_CLIENT_IP')) {
			return getenv('HTTP_CLIENT_IP');
		} else if(getenv('HTTP_X_FORWARDED_FOR')) {
			return getenv('HTTP_X_FORWARDED_FOR');
		} else if(getenv('HTTP_X_FORWARDED')) {
			return getenv('HTTP_X_FORWARDED');
		} else if(getenv('HTTP_FORWARDED_FOR')) {
			return getenv('HTTP_FORWARDED_FOR');
		} else if(getenv('HTTP_FORWARDED')) {
			return getenv('HTTP_FORWARDED');
		} else if(getenv('REMOTE_ADDR')) {
			return getenv('REMOTE_ADDR');
		} else {
			return 'UNKNOWN';
		}
	}
}

/**
 *  filterParamString method
 *  filter get parameter value for safe sql query
 *  @param string $value
 *  @return string $result
 */
if (!function_exists('filterParamString')) {
	function filterParamString($value) {
		$result = preg_replace("/[^a-zA-Z0-9 +]+/", "", $value);

		return $result;
	}
}

/**
 *  filterParamNumber method
 *  filter get parameter value for safe sql query
 *  @param string $value
 *  @return string $result
 */
if (!function_exists('filterParamNumber')) {
	function filterParamNumber($value) {
		$result = preg_replace("/[^0-9]+/", "", $value);

		return $result;
	}
}

/**
 *  executionTime method
 *  count request execution time (in seconds)
 *  @param int $request_time
 *  @return int $time
 */
if (!function_exists('executionTime')) {
	function executionTime($request_time) {
		$time = ((time() - $request_time) % 86400) % 60;

		return $time;
	}
}

/**
 *  jwtDecode method
 *  decode jwt token
 *  @return array $decoded
 */
if (!function_exists('jwtDecode')) {
	function jwtDecode($raw_token = false, $key = false, $algorithm = false) {
		if ($raw_token && $key && $algorithm) {
			list($token) = sscanf($raw_token, 'Bearer %s');

			try {
				$decoded = JWT::decode(
                    $token, //Token to be decoded in the JWT
                    base64_decode($key), // The signing key
                    [$algorithm] // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
                );

				if (is_object($decoded)) {
					return object2array($decoded);
				}

				return $decoded;
			} catch (Exception $e) {
				return false;
			}
		}

		return false;
	}
}

/**
 *  object2array method
 *  convert object to array
 *  @return array $resutl
 */
if (!function_exists('object2array')) {
	function object2array($object) {
		if (is_object($object) || is_array($object)) {
			$result = (array) $object;

			foreach($result as &$item) {
				$item = object2array($item);
			}

			return $result;
		}
		
		return $object;
	}
}

/**
 *  array2object method
 *  convert array to object
 *  @return object $resutl
 */
if (!function_exists('array2object')) {
	function array2object($array) {
		if (is_object($array) || is_array($array)) {
			$result = (object) $array;

			foreach($result as &$item) {
				$item = array2object($item);
			}

			return $result;
		}
		
		return $array;
	}
}
