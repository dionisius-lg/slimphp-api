<?php

use Firebase\JWT\JWT as JWT;

/**
 *  access_log method
 *  create & write log for every access
 *  @param array $config, integer $status, string $method, string $path, string $body
 */
if (!function_exists('access_log')) {
    function access_log($config, $status, $method, $path, $body) {
        $log_dir = rtrim($config['dir']['logger'], '/') . '/';

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777);
        }

        $log_path = $log_dir . 'access-' . date('Ymd') . '.log';
        $log_date = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $log_content_length = array_key_exists('CONTENT_LENGTH', $_SERVER) && !empty($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : 0;
        $log_message = client_ip() . " - [{$log_date}] {$_SERVER['SERVER_PROTOCOL']} {$status} {$method} \"{$path}\" {$body} - {$log_content_length}";

        // always update not replace (FILE_APPEND)
        file_put_contents($log_path, $log_message . "\n", FILE_APPEND);
    }
}

/**
 *  client_ip method
 *  get client's IP Address from request
 *  @return string IP Address
 */
if (!function_exists('client_ip')) {
    function client_ip() {
        switch (true) {
            case array_key_exists('HTTP_CLIENT_IP', $_SERVER):
                return $_SERVER['HTTP_CLIENT_IP'];
                break;
            case array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER):
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
                break;
            case array_key_exists('HTTP_X_FORWARDED', $_SERVER):
                return $_SERVER['HTTP_X_FORWARDED'];
                break;
            case array_key_exists('HTTP_X_CLUSTER_CLIENT_IP', $_SERVER):
                return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
                break;
            case array_key_exists('HTTP_FORWARDED_FOR', $_SERVER):
                return $_SERVER['HTTP_FORWARDED_FOR'];
                break;
            case array_key_exists('HTTP_FORWARDED', $_SERVER):
                return $_SERVER['HTTP_FORWARDED'];
                break;
            case array_key_exists('REMOTE_ADDR', $_SERVER):
                return $_SERVER['REMOTE_ADDR'];
                break;
            default:
                return 'unknown';
                break;
        }
    }
}

/**
 *  filter_data method
 *  remove invalid data from array
 *  @param array $data, array $master_column, array $protected_column
 *  @return array $data
 */
if (!function_exists('filter_data')) {
    function filter_data($data = [], $master_column = [], $protected_column = []) {
        if (is_array_assoc($data)) {
            foreach ($data as $key => $val) {
                $val = trim($val);

                if (is_array_index($protected_column) && in_array($key, $protected_column)) {
                    unset($data[$key]);
                    continue;
                }

                if (is_array_index($master_column)) {
                    if (!in_array($key, $master_column)) {
                        unset($data[$key]);
                        continue;
                    }

                    if (empty($val)) {
                        $val = $val === '0' ? $val : 'NULL';
                    }
                }

                if (is_array_assoc($master_column)) {
                    if (!array_key_exists($key, $master_column)) {
                        unset($data[$key]);
                        continue;
                    }

                    if (empty($val)) {
                        if (in_array($master_column[$key]['data_type'], ['int', 'tinyint', 'mediumint', 'bigint'])) {
                            $val = '0';
                        } else {
                            $val = $val === '0' ? $val : 'NULL';
                        }
                    }
                }

                $data[$key] = '\'' . addslashes($val) . '\'';
            }
        } elseif (is_array_multi($data)) {
            for ($i = 0; $i < count($data); $i++) { 
                if (is_array_assoc($data[$i])) {
                    foreach ($data[$i] as $key => $val) {
                        $val = trim($val);

                        if (is_array_index($protected_column) && in_array($key, $protected_column)) {
                            unset($data[$i][$key]);
                            continue;
                        }

                        if (is_array_index($master_column)) {
                            if (!in_array($key, $master_column)) {
                                unset($data[$i][$key]);
                                continue;
                            }

                            if (empty($val)) {
                                $val = $val === '0' ? $val : 'NULL';
                            }
                        }

                        if (is_array_assoc($master_column)) {
                            if (!array_key_exists($key, $master_column)) {
                                unset($data[$key]);
                                continue;
                            }

                            if (empty($val)) {
                                if (in_array($master_column[$key]['data_type'], ['int', 'tinyint', 'mediumint', 'bigint'])) {
                                    $val = '0';
                                } else {
                                    $val = $val === '0' ? $val : 'NULL';
                                }
                            }
                        }

                        $data[$i][$key] = '\'' . addslashes($val) . '\'';
                    }
                } else {
                    unset($data[$i]);
                }
            }
        }

        return $data;
    }
}

/**
 *  numeric_array_value method
 *  convert array value to numeric if !NaN
 *  @param array $data
 *  @return array $data
 */
if (!function_exists('numeric_array_value')) {
    function numeric_array_value($data = []) {
        switch (true) {
            case is_array_assoc($data):
                foreach ($data as $key => $val) {
                    if (is_integer($val)) {
                        $data[$key] = (int) $val;
                    }

                    if (is_float($val)) {
                        $data[$key] = (float) $val;
                    }
                }
                break;
            case is_array_multi($data):
                for ($i = 0; $i < count($data); $i++) {
                    if (is_array_assoc($data[$i])) {
                        foreach ($data[$i] as $key => $val) {
                            if (is_integer($val)) {
                                $data[$i][$key] = (int) $val;
                            }

                            if (is_float($val)) {
                                $data[$i][$key] = (float) $val;
                            }
                        }
                    } else {
                        if (is_integer($data[$i])) {
                            $data[$i] = (int) $data[$i];
                        }

                        if (is_float($data[$i])) {
                            $data[$key] = (float) $val;
                        }
                    }
                }
                break;
        }

        return $data;
    }
}

/**
 *  execution_time method
 *  count request execution time (in seconds)
 *  @param int $request_time
 *  @return int $time
 */
if (!function_exists('execution_time')) {
    function execution_time($request_time) {
        $time = ((time() - $request_time) % 86400) % 60;

        return $time;
    }
}

/**
 *  is_json method
 *  check whether variable is JSON or not
 *  @param string $string
 *  @return bool;
 */
if (!function_exists('is_json')) {
    function is_json($str) {
        return is_string($str) && is_array(json_decode($str, true)) ? true : false;
    }
}


/**
 *  json2array method
 *  convert json to object
 *  @param json $json
 *  @return object $result
 */
if (!function_exists('json2array')) {
    function json2array($json) {
        if (is_json($json)) {
            $result = json_decode($json, true);

            foreach($result as &$item) {
                $item = json2array($item);
            }

            return $result;
        }

        return $json;
    }
}

/**
 *  object2array method
 *  convert object to array
 *  @param object $obj
 *  @return array $result
 */
if (!function_exists('object2array')) {
    function object2array($obj) {
        if (is_object($obj) || is_array($obj)) {
            $result = (array) $obj;

            foreach($result as &$item) {
                $item = object2array($item);
            }

            return $result;
        }
        
        return $obj;
    }
}

/**
 *  array2object method
 *  convert array to object
 *  @param array $arr
 *  @return object $result
 */
if (!function_exists('array2object')) {
    function array2object($arr) {
        if (is_object($arr) || is_array($arr)) {
            $result = (object) $arr;

            foreach($result as &$item) {
                $item = array2object($item);
            }

            return $result;
        }
        
        return $arr;
    }
}

/**
 *  camelcase method
 *  convert string to camelcase
 *  @param string $arr, boolean $capitalize_first
 *  @return string $str or null
 */
if (!function_exists('camelcase')) {
    function camelcase($str, $capitalize_first = false) {
        if ($str && !empty($str)) {
            $str = preg_replace('/[_\-\s-]/', ' ', $str);
            $str = preg_replace('/[\s-]+/', ' ', $str);
            $str = str_replace(' ', '', ucwords($str, ' '));

            if (!$capitalize_first) {
                $str = lcfirst($str);
            }

            return $str;
        }

        return null;
    }
}

/**
 *  is_array_index method
 *  check whether array is indexed or not
 *  @param array $arr
 *  @return bool;
 */
if (!function_exists('is_array_index')) {
    function is_array_index($arr) {
        if ($arr && !empty($arr) && is_array($arr)) {
            foreach ($arr as $key => $val) {
                if (!is_int($key)) return false;
            }

            return true;
        }

        return false;
    }
}

/**
 *  is_array_assoc method
 *  check whether array is associative or not
 *  @param array $arr
 *  @return bool;
 */
if (!function_exists('is_array_assoc')) {
    function is_array_assoc($arr) {
        if ($arr && !empty($arr) && is_array($arr)) {
            foreach ($arr as $key => $val) {
                if (!is_string($key)) return false;
            }

            return true;
        }

        return false;
    }
}

/**
 *  is_array_multi method
 *  check whether array is multidimensional or not
 *  @param array $arr
 *  @return bool;
 */
if (!function_exists('is_array_multi')) {
    function is_array_multi($arr) {
        if ($arr && !empty($arr) && is_array($arr)) {
            rsort($arr);
            if (isset($arr[0]) && is_array($arr[0])) {
                return true;
            }
        }

        return false;
    }
}

/**
 *  safe_base64_encode method
 *  safe base64 encode
 *  @param string $str
 *  @return string $encoded
 */
if (!function_exists('safe_base64_encode')) {
    function safe_base64_encode($str) {
        $encoded = base64_encode($str);
        $encoded = str_replace(['+', '/', '='], ['-', '_', ''], $encoded);

        return $encoded;
    }
}

/**
 *  safe_base64_decode method
 *  safe base64 decode
 *  @param string $str
 *  @return string $decoded
 */
if (!function_exists('safe_base64_decode')) {
    function safe_base64_decode($str) {
        $str  = str_replace(['-', '_'], ['+', '/'], $str);
        $mod4 = strlen($str) % 4;

        if ($mod4) {
            $str .= substr('====', $mod4);
        }

        $decoded = base64_decode($str);

        return $decoded;
    }
}

/**
 *  encode method
 *  encode using mcyrpt
 *  @param string $val, string $key
 *  @return string $encoded
 */
if (!function_exists('encode')) {
    function encode($val, $key = null) {
        if ($val && !empty($val)) {
            $unique_key = (!empty($key) && is_string($key)) ? $key : 's3cR3tKEy';
            $text       = $val;
            $iv_size    = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
            $iv         = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $crypt_text = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $unique_key, $text, MCRYPT_MODE_ECB, $iv);
            $encoded    = safe_base64_encode($crypt_text);

            return trim($encoded);
        }

        return false;
    }
}

/**
 *  decode method
 *  decode using mcyrpt
 *  @param string $val, string $key
 *  @return string $decode
 */
if (!function_exists('decode')) {
    function decode($val, $key = null) {
        if ($val && !empty($val)) {
            $unique_key = (!empty($key) && is_string($key)) ? $key : 's3cR3tKEy';
            $crypt_text = safe_base64_decode($val);
            $iv_size    = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
            $iv         = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $decoded    = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypt_text, MCRYPT_MODE_ECB, $iv);

            return trim($decoded);
        }

        return false;
    }
}

/**
 *  write_file_content method
 *  write content on file
 *  @param string $filename, string $content, string $folder
 *  @return string $path . $filename
 */
if (!function_exists('write_file_content')) {
    function write_file_content($filename = null, $content = null, $folder = null) {
        try {
            if (!empty($filename) && !empty($content)) {
                $fileinfo = pathinfo($filename);

                if (!empty($fileinfo['filename'])) {
                    $fileinfo['filename'] = preg_replace("/[^A-Za-z0-9\_\-]/", '', $fileinfo['filename']);
                }

                if (!empty($fileinfo['extension'])) {
                    $fileinfo['extension'] = preg_replace("/[^A-Za-z0-9]/", '', $fileinfo['extension']);
                }

                if (!empty($fileinfo['filename']) && !empty($fileinfo['extension'])) {
                    $filename = $fileinfo['filename'] . '.' . $fileinfo['extension'];
                    $path = __DIR__ . '/../files/';

                    if (!empty($folder)) {
                        $path .= $folder . '/';
                    }

                    $path = preg_replace('/(\/+)/','/', $path);

                    if (!is_dir($path)) {
                        mkdir($path, 0777);
                    }

                    if (is_array($content)) {
                        file_put_contents($path . $filename, json_encode($content));
                        return $path . $filename;
                    }
                }
            }

            throw new Exception(false);
        } catch (Exception $e) {
            return false;
        }
    }
}

/**
 *  read_file_content method
 *  read content on file
 *  @param string $filename, string $folder
 *  @return any $content
 */
if (!function_exists('read_file_content')) {
    function read_file_content($filename = null, $folder = null) {
        try {
            if (!empty($filename)) {
                $fileinfo = pathinfo($filename);

                if (!empty($fileinfo['filename'])) {
                    $fileinfo['filename'] = preg_replace("/[^A-Za-z0-9\_\-]/", '', $fileinfo['filename']);
                }

                if (!empty($fileinfo['extension'])) {
                    $fileinfo['extension'] = preg_replace("/[^A-Za-z0-9]/", '', $fileinfo['extension']);
                }

                if (!empty($fileinfo['filename']) && !empty($fileinfo['extension'])) {
                    $filename = $fileinfo['filename'] . '.' . $fileinfo['extension'];
                    $path = __DIR__ . '/../files/';

                    if (!empty($folder)) {
                        $path .= $folder . '/';
                    }

                    $path = preg_replace('/(\/+)/','/', $path);

                    if (is_dir($path) && file_exists($path . $filename)) {
                        $content = file_get_contents($path . $filename);

                        if (is_json($content)) {
                            $content = json2array($content);
                        }

                        return $content;
                    }
                }
            }

            throw new Exception(false);
        } catch (Exception $e) {
            return false;
        }
    }
}

/**
 *  remove_file method
 *  remove file
 *  @param string $filename
 *  @return boolean
 */
if (!function_exists('remove_file')) {
    function remove_file($filename = null) {
        try {
            if (!empty($filename)) {
                $fileinfo = pathinfo($filename);

                if (!empty($fileinfo['filename'])) {
                    $fileinfo['filename'] = preg_replace("/[^A-Za-z0-9\_\-]/", '', $fileinfo['filename']);
                }

                if (!empty($fileinfo['extension'])) {
                    $fileinfo['extension'] = preg_replace("/[^A-Za-z0-9]/", '', $fileinfo['extension']);
                }

                if (!empty($fileinfo['filename']) && !empty($fileinfo['extension'])) {
                    $filename = $fileinfo['filename'] . '.' . $fileinfo['extension'];
                    $path = __DIR__ . '/../files/';

                    if (!empty($folder)) {
                        $path .= $folder . '/';
                    }

                    $path = preg_replace('/(\/+)/','/', $path);

                    if (is_dir($path) && file_exists($path . $filename)) {
                        unlink($path . $filename);
                        return true;
                    }
                }
            }

            throw new Exception(false);
        } catch (Exception $e) {
            return false;
        }
    }
}

/**
 *  format_datetime_object method
 *  format datetime object
 *  @param string $str
 *  @return object $format
 */
if (!function_exists('format_datetime_object')) {
    function format_datetime_object($str) {
        if ($str && !empty($str) && is_string($str)) {
            $format = DateTime::createFromFormat('Y-m-d H:i:s', $str);
			$errors = DateTime::getLastErrors();

            if ($errors['warning_count'] === 0 && $errors['error_count'] === 0) {
                // return object2array($format);
                return $format;
            }
        }

        return false;
    }
}

/**
 *  format_date_object method
 *  format date object
 *  @param string $str
 *  @return object $format
 */
if (!function_exists('format_date_object')) {
    function format_date_object($str) {
        if ($str && !empty($str) && is_string($str)) {
            $format = DateTime::createFromFormat('Y-m-d', $str);
			$errors = DateTime::getLastErrors();

            if ($errors['warning_count'] === 0 && $errors['error_count'] === 0) {
                // return object2array($format);
                return $format;
            }
        }

        return false;
    }
}