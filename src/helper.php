<?php

$container = $app->getContainer();

/**
 *  check whether value is JSON
 *  @param {any} $value
 *  @return {bool}
 */
if (!function_exists('is_json')) {
    function is_json($value) {
        return is_string($value) && is_array(json_decode($value, true)) ? true : false;
    }
}

/**
 *  check whether array is indexed
 *  @param {array} $array
 *  @return {bool}
 */
if (!function_exists('is_array_index')) {
    function is_array_index($array) {
        if (is_array($array)) {
            foreach ($array as $key => $val) {
                if (!is_int($key)) return false;
            }

            return true;
        }

        return false;
    }
}

/**
 *  check whether array is associative
 *  @param {array} $array
 *  @return {bool}
 */
if (!function_exists('is_array_assoc')) {
    function is_array_assoc($array) {
        if (is_array($array)) {
            foreach ($array as $key => $val) {
                if (!is_string($key)) return false;
            }

            return true;
        }

        return false;
    }
}

/**
 *  check whether array is multidimensional
 *  @param {array} $array
 *  @return {bool}
 */
if (!function_exists('is_array_multi')) {
    function is_array_multi($array) {
        if (is_array($array)) {
            rsort($array);

            if (isset($array[0]) && is_array($array[0])) {
                return true;
            }
        }

        return false;
    }
}

/**
 *  convert array to object
 *  @param {array} $array
 *  @return {object} $result
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

/**
 *  convert object to array
 *  @param {object} $object
 *  @return {array} $result
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
 *  convert json to object
 *  @param {json} $json
 *  @return {object} $result
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
 *  convert array value to numeric if !NaN
 *  @param {array} $data
 *  @return {array} $data
 */
if (!function_exists('array2numeric')) {
    function array2numeric($data = []) {
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
 *  get client's IP Address from request
 *  @return {string} IP Address
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
 *  safe base64 encode
 *  @param {string} $value
 *  @return {string} $encoded
 */
if (!function_exists('safe_base64_encode')) {
    function safe_base64_encode($value) {
        if (is_string($value)) {
            $encoded = base64_encode($value);
            $encoded = str_replace(['+', '/', '='], ['-', '_', ''], $encoded);

            return $encoded;
        }

        return $value;
    }
}

/**
 *  safe base64 decode
 *  @param {string} $value
 *  @return {string} $decoded
 */
if (!function_exists('safe_base64_decode')) {
    function safe_base64_decode($value) {
        if (is_string($value)) {
            $str  = str_replace(['-', '_'], ['+', '/'], $value);
            $mod4 = strlen($str) % 4;

            if ($mod4) {
                $str .= substr('====', $mod4);
            }

            $decoded = base64_decode($str);

            return $decoded;
        }

        return $value;
    }
}

/**
 *  encrypt using openssl_encrypt
 *  @param {string} $value
 *  @return {string} $encoded
 */
if (!function_exists('encrypt')) {
    function encrypt($value) {
        if (!empty($value) && is_string($value)) {
            global $container;

            $cipher = 'aes-256-cbc';
            $key = $container['secret_key'];
            $options = 0;
            $iv = 'initVector16Bits';
            $encrypted = openssl_encrypt($value, $cipher, $key, $options, $iv);
            $encoded = safe_base64_encode($encrypted);

            return $encoded;
        }

        return null;
    }
}

/**
 *  decrypt using openssl_encrypt
 *  @param {string} $value
 *  @return {string} $decode
 */
if (!function_exists('decrypt')) {
    function decrypt($value) {
        global $container;

        try {
            $cipher = 'aes-256-cbc';
            $key = $container['secret_key'];
            $options = 0;
            $iv = 'initVector16Bits';
            $decoded = safe_base64_decode($value);
            $decrypted = openssl_decrypt($decoded, $cipher, $key, $options, $iv);

            return $decrypted;
        } catch (\Exception $e) {
            return $e->getMessage;
        }
    }
}

/**
 *  format value to date object
 *  @param {string} $value
 *  @return {object} $format
 */
if (!function_exists('format_date_object')) {
    function format_date_object($value) {
        if (!empty($value) && is_string($value)) {
            $format = DateTime::createFromFormat('Y-m-d', $value);
            $errors = DateTime::getLastErrors();

            if ($errors['warning_count'] === 0 && $errors['error_count'] === 0) {
                return $format;
            }
        }

        return false;
    }
}

/**
 *  format value datetime object
 *  @param {string} $value
 *  @return {object} $format
 */
if (!function_exists('format_datetime_object')) {
    function format_datetime_object($value) {
        if (!empty($value) && is_string($value)) {
            $format = DateTime::createFromFormat('Y-m-d H:i:s', $value);
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
 *  convert value to camelcase
 *  @param {string} $value, {bool} $capitalize_first
 *  @return {string} $result
 */
if (!function_exists('camelcase')) {
    function camelcase($value, $capitalize_first = false) {
        if (!empty($value)) {
            $result = preg_replace('/[_\-\s-]/', ' ', $value);
            $result = preg_replace('/[\s-]+/', ' ', $result);
            $result = str_replace(' ', '', ucwords($result, ' '));

            if (!$capitalize_first) {
                $result = lcfirst($result);
            }

            return $result;
        }

        return $value;
    }
}

/**
 *  read content on file
 *  @param {string} $filename, {string} $folder
 *  @return {any} $content
 */
if (!function_exists('file_read')) {
    function file_read($filename, $folder = null) {
        global $container;

        try {
            $fileinfo = pathinfo($filename);

            if (!empty($fileinfo['filename'])) {
                $fileinfo['filename'] = preg_replace("/[^A-Za-z0-9\_\-]/", '', $fileinfo['filename']);
            }

            if (!empty($fileinfo['extension'])) {
                $fileinfo['extension'] = preg_replace("/[^A-Za-z0-9]/", '', $fileinfo['extension']);
            }

            if (!empty($fileinfo['filename']) && !empty($fileinfo['extension'])) {
                $filename = $fileinfo['filename'] . '.' . $fileinfo['extension'];
                $path = $container['dir']['files'];

                if (!empty($folder) && is_string($folder)) {
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

            throw new Exception(false);
        } catch (\Exception $e) {
            return false;
        }
    }
}

/**
 *  write content on file
 *  @param {string} $filename, {string} $content, {string} $folder
 *  @return {string} $path . $filename
 */
if (!function_exists('file_write')) {
    function file_write($filename, $content = null, $folder = null) {
        global $container;

        try {
            if (!empty($content)) {
                $fileinfo = pathinfo($filename);

                if (!empty($fileinfo['filename'])) {
                    $fileinfo['filename'] = preg_replace("/[^A-Za-z0-9\_\-]/", '', $fileinfo['filename']);
                }

                if (!empty($fileinfo['extension'])) {
                    $fileinfo['extension'] = preg_replace("/[^A-Za-z0-9]/", '', $fileinfo['extension']);
                }

                if (!empty($fileinfo['filename']) && !empty($fileinfo['extension'])) {
                    $filename = $fileinfo['filename'] . '.' . $fileinfo['extension'];
                    $path = $container['dir']['files'];

                    if (!empty($folder) && is_string($folder)) {
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
        } catch (\Exception $e) {
            return false;
        }
    }
}

/**
 *  remove file
 *  @param {string} $filename
 *  @return {bool}
 */
if (!function_exists('file_remove')) {
    function file_remove($filename, $folder = null) {
        global $container;

        try {
            $fileinfo = pathinfo($filename);

            if (!empty($fileinfo['filename'])) {
                $fileinfo['filename'] = preg_replace("/[^A-Za-z0-9\_\-]/", '', $fileinfo['filename']);
            }

            if (!empty($fileinfo['extension'])) {
                $fileinfo['extension'] = preg_replace("/[^A-Za-z0-9]/", '', $fileinfo['extension']);
            }

            if (!empty($fileinfo['filename']) && !empty($fileinfo['extension'])) {
                $filename = $fileinfo['filename'] . '.' . $fileinfo['extension'];
                $path = $container['dir']['files'];

                if (!empty($folder) && is_string($folder)) {
                    $path .= $folder . '/';
                }

                $path = preg_replace('/(\/+)/','/', $path);

                if (is_dir($path) && file_exists($path . $filename)) {
                    unlink($path . $filename);
                    return true;
                }
            }

            throw new Exception(false);
        } catch (\Exception $e) {
            return false;
        }
    }
}

/**
 *  remove invalid column from array
 *  @param {array} $column, array $master_column
 *  @return {array} $column
 */
if (!function_exists('filter_column')) {
    function filter_column($column = [], $master_column = []) {
        if (is_array_assoc($column)) {
            foreach ($column as $key => $val) {
                if (is_array_index($master_column)) {
                    if (!in_array($key, $master_column)) {
                        unset($column[$key]);
                        continue;
                    }
                }

                if (is_array_assoc($master_column)) {
                    if (!array_key_exists($key, $master_column)) {
                        unset($column[$key]);
                        continue;
                    }
                }
            }
        }

        return $column;
    }
}

/**
 *  remove invalid data from array
 *  @param {array} $data, {array} $master_column, {array} $protected_column
 *  @return {array} $data
 */
if (!function_exists('filter_data')) {
    function filter_data($data = [], $master_column = [], $protected_column = []) {
        switch (true) {
            case (is_array_assoc($data)):
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

                    $data[$key] = $val;
                }
                break;
            case (is_array_multi($data)):
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

                            $data[$i][$key] = $val;
                        }
                    } else {
                        unset($data[$i]);
                    }
                }
                break;
        }

        return $data;
    }
}

/**
 *  create & write log for every access
 *  @param {int} $status, {string} $method, {string} $path, {string} $body
 *  @return {array} $data
 */
if (!function_exists('access_log')) {
    function access_log($method, $endpoint, $body, $status) {
        global $container;

        $log_dir = rtrim($container['dir']['logs'], '/') . '/';

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777);
        }

        $log_path = $log_dir . 'access-' . date('Ymd') . '.log';
        $log_date = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $log_content_length = array_key_exists('CONTENT_LENGTH', $_SERVER) && !empty($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : 0;
        $log_message = client_ip() . " - [{$log_date}] {$status} \"{$method} {$endpoint}\" {$body} - {$log_content_length}";

        // always update not replace (FILE_APPEND)
        file_put_contents($log_path, $log_message . "\n", FILE_APPEND);
    }
}

/**
 *  mask sensitive data
 *  @param {array} $data
 *  @return {array} $data
 */
if (!function_exists('mask_data')) {
    function mask_data($data) {
        if (!empty($data) && is_array_assoc($data)) {
            $sensitives = ['password', 'secret'];

            foreach ($data as $key => $val) {
                if (in_array(strtolower($key), $sensitives) && is_string($val)) {
                    $data[$key] = str_repeat('*', strlen($val));
                }
            }
        }

        return $data;
    }
}

/**
 *  check domain address
 *  @param {string} $value
 *  @return {bool}
 */
if (!function_exists('is_domain')) {
    function is_domain($value) {
        // Regular expression to match IP address in the form of x.x.x.x
        $ip_pattern = '/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';

        // Check if the value matches the IP pattern or contains 'localhost'
        if (preg_match($ip_pattern, $value) || strpos($value, 'localhost') !== false) {
            return false;
        }

        return true;
    }
}