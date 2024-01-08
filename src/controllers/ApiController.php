<?php

use Psr\Container\ContainerInterface;
use Firebase\JWT\JWT;

class ApiController
{
    protected $ci; //get slim container
    protected $conf; //global config
    protected $conn; //connection

    /**
     *  __construct method
     *  variable initialization
     *  @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
        $this->conf = $this->ci->get('config');
        $this->conn = $this->dbConnect();
    }

    /**
     *  dbConnect method
     *  database connection using pdo
     */
    public function dbConnect()
    {
        $conn = new PDO("mysql:host=".$this->conf['database']['host'].";port=".$this->conf['database']['port'].";dbname=".$this->conf['database']['dbname'].";charset=utf8", $this->conf['database']['username'], $this->conf['database']['password']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }

    /**
     *  checkColumn method
     *  check table column
     *  @param string $db, string $table
     *  @return array $column
     */
    public function checkColumn($db, $table)
    {
        $schema = $this->dbConnect()->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
        $schema->execute([$db, $table]);

        $column = [];
        while ($row = $schema->fetch(PDO::FETCH_ASSOC)) {
            $column[] = $row['COLUMN_NAME'];
        }

        return $column;
    }

    /**
     *  checkColumnWithType method
     *  check table column with data type
     *  @param string $db, string $table
     *  @return array multidimensional $column
     */
    public function checkColumnType($db, $table)
    {
        $schema = $this->dbConnect()->prepare("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
        $schema->execute([$db, $table]);

        $column = [];
        while ($row = $schema->fetch(PDO::FETCH_ASSOC)) {
            $column[$row['COLUMN_NAME']] = $row['DATA_TYPE'];
        }

        return $column;
    }

    /**
     *  checkColumnDetail method
     *  check table column with data type
     *  @param string $db, string $table
     *  @return array multidimensional $column
     */
    public function checkColumnDetail($db, $table)
    {
        $schema = $this->dbConnect()->prepare("SELECT ORDINAL_POSITION, COLUMN_NAME, COLUMN_KEY, COLUMN_TYPE, COLUMN_DEFAULT, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, CHARACTER_SET_NAME, COLLATION_NAME, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
        $schema->execute([$db, $table]);
        $column = [];

        while ($row = $schema->fetch(PDO::FETCH_ASSOC)) {
            $column[$row['COLUMN_NAME']] = [
                'column_key' => $row['COLUMN_KEY'],
                'data_type' => $row['DATA_TYPE'],
                'is_nullable' => $row['IS_NULLABLE'],
            ];
        }

        return $column;
    }

    /**
     *  checkData method
     *  check data from table
     *  @param string $table, array $condition
     *  @return bool $count
     */
    public function checkData($table, $condition = [])
    {
        if (empty($table)) {
            return false;
        }

        $query = "SELECT COUNT(*) FROM {$table}";

        if (!empty($condition)) {
            $prefix = 'xxx_';
            $term = [];

            foreach ($condition as $key => $val) {
                if (!empty($val) || $val === '0') {
                    if (strpos($key, $prefix) === 0) {
                        array_push($term, substr($key, strlen($prefix)) . ' <> \'' . addslashes($val) . '\'');
                    } else {
                        array_push($term, $key . ' = \'' . addslashes($val) . '\'');
                    }
                }
            }

            if (!empty($term)) {
                $term = implode(' AND ', $term);
                $query .= " WHERE {$term}";
            }
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     *  getData method
     *  get data from table
     *  @param string $table, array $condition, array $condition_custom, array $column_select, array $column_deselect, array $column_custom, array $join, array $group_by, array $order_custom
     *  @return array $result
     */
    public function getData($table, $condition = [], $condition_custom = [], $column_select = [], $column_deselect = [], $column_custom = [], $join = [], $group_by = [], $order_custom = [])
    {
        $master_column = $this->checkColumn($this->conf['database']['dbname'], $table);

        if (empty($table) || empty($master_column)) {
            return false;
        }

        $column = $master_column;
        $order = null;
        $limit = null;
        $sort = 'ASC';
        $page = 1;

        if (is_array_index($column_select)) {
            $selected = array_intersect($column, $column_select);
            $column = $selected;
        }

        if (is_array_index($column_deselect)) {
            if (in_array('*', $column_deselect)) {
                $column = [];
            } else {
                $deselected = array_intersect($column_deselect, $column);
                $column = array_diff($column, $deselected);
            }
        }

        if (is_array_index($join)) {
            $column_prefixed = array_map(function ($col) use ($table) {
                return "{$table}.{$col}";
            }, $column);

            $column = $column_prefixed;
        }

        if (is_array($column_custom) && count($column_custom) > 0) {
            $column = array_merge($column, $column_custom);
        }

        if (!empty($column)) {
            $column = array_values($column);
            $order = "ORDER BY {$column[0]}";
        }

        if (is_array_assoc($condition)) {
            $sort_list = ['ASC', 'DESC'];

            if (array_key_exists('sort', $condition)) {
                $sort_index = array_search(strtoupper($condition['sort']), $sort_list);

                if ($sort_index >= 0) {
                    $sort = $sort_list[$sort_index];
                }
            }

            if (array_key_exists('order', $condition)) {
                if (!empty($condition['order']) && in_array($condition['order'], $column)) {
                    $order = "ORDER BY {$condition['order']}";
                }
            }

            if (array_key_exists('limit', $condition)) {
                if (is_numeric($condition['limit'])) {
                    $limit = $condition['limit'];
                }
            }

            if (array_key_exists('page', $condition)) {
                if (is_numeric($condition['page'])) {
                    $page = $condition['page'];
                }
            }

            $condition = filter_data($condition, $master_column);
            $condition_temp = [];
            $null_char = ['\'NULL\'', ''];

            foreach ($condition as $key => $val) {
                $val = trim($val);
                $val = str_replace($null_char, NULL, $val);

                if (!empty($val) || $val === '0') {
                    array_push($condition_temp, "{$table}.{$key} = {$val}");
                    continue;
                }

                array_push($condition_temp, "{$table}.{$key} IS NULL");
            }

            if (!empty($condition_temp)) {
                $condition = $condition_temp;
            }

            unset($condition_temp);
        }

        $condition = (!empty($condition) ? 'WHERE ' : '') . implode(' AND ', $condition);

        if (is_array_index($condition_custom)) {
            foreach ($condition_custom as $custom) {
                $condition .= !empty($condition) ? " AND {$custom}" : "WHERE {$custom}";
            }
        }

        if (is_array_index($order_custom)) {
            $order = "ORDER BY " . implode(', ', $order_custom);
        }

        if (is_array_index($group_by)) {
            $group_temp = [];

            foreach ($group_by as $val) {
                $val = trim($val);
                $val = !strpos($val, '.') ? "{$table}.{$val}" : $val;

                if (in_array($val, $column)) {
                    array_push($group_temp, $val);
                }
            }

            if (!empty($group_temp)) {
                $group_by = implode(', ', $group_temp);
                $order = "GROUP BY {$group_by} {$order}";
            }

            unset($group_temp);
        }

        $join = implode(' ', $join);
        $column = implode(', ', $column);
        $query = "SELECT {$column} FROM {$table} {$join} {$condition} {$order} {$sort}";

        if (!empty($limit)) {
            $query .= " LIMIT {$limit}";
            $offset = ($limit * $page) - $limit;

            if (is_numeric($offset) && $offset >= 0) {
                $query .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $count = "SELECT COUNT(*) FROM {$table} {$table} {$condition}";

        if (!empty($group_by)) {
            $count = "SELECT COUNT(*) FROM (SELECT COUNT(*) FROM {$table} {$join} {$condition} {$order}) AS {$table}";
        }

        $total = $this->conn->query($count)->fetchColumn();
        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = array_map('utf8_encode', $row);
        }

        $result = [
            'total' => (int) $total,
            'data' => $data
        ];

        if (!empty($limit)) {
            $page_last = ceil($total/$limit);
            $page_first = 1;
            $page_current = (int) $page;
            $page_next = $page_current + 1;
            $page_previous = $page_current - 1;

            $result['paging'] = [
                'current' => $page_current,
                'next' => ($page_next <= $page_last) ? $page_next : $page_current,
                'previous' => ($page_previous > 0) ? $page_previous : 1,
                'first' => $page_first,
                'last' => ($page_last > 0) ? $page_last : 1,
            ];
        }

        return $result;
    }

    /**
     *  insertData method
     *  insert data to table
     *  @param string $table, array $data, array $protected
     *  @return bool $inserted
     */
    public function insertData($table, $data = [], $protected = [])
    {
        $master_column = $this->checkColumnDetail($this->conf['database']['dbname'], $table);

        if (empty($table) || empty($master_column)) {
            return false;
        }

        $time_char = ['\'CURRENT_TIMESTAMP()\'', '\'NOW()\''];
        $null_char = ['\'NULL\'', ''];
        $zero_char = ['\'0\''];

        $data = filter_data($data, $master_column, $protected);

        if (!empty($data) && is_array($data)) {
            $column = [];

            foreach ($data as $key => $val) {
                array_push($column, "{$key} = {$val}");
            }

            $column = implode(', ', $column);
            $column = str_replace($time_char, 'NOW()', $column);
            $column = str_replace($null_char, 'NULL', $column);
            $column = str_replace($zero_char, '0', $column);

            $query = "INSERT INTO {$table} SET {$column}";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt->rowCount();
        }

        return false;
    }

    /**
     *  insertManyData method
     *  insert many data to table
     *  @param string $table, array $data, array $protected
     *  @return bool $inserted
     */
    public function insertManyData($table, $data = [], $protected = [])
    {
        $master_column = $this->checkColumnDetail($this->conf['database']['dbname'], $table);

        if (empty($table) || empty($master_column)) {
            return false;
        }

        $time_char = ['\'CURRENT_TIMESTAMP()\'', '\'NOW()\''];
        $null_char = ['\'NULL\'', ''];
        $zero_char = ['\'0\''];

        $data = filter_data($data, $master_column, $protected);

        if (!empty($data) && is_array($data) && count($data) != count($data, COUNT_RECURSIVE)) {
            $keys = array_keys($master_column);
            $values = [];

            for ($i = 0; $i < count($data); $i++) {
                $reorder = array_replace(array_flip($keys), $data[$i]);
                $values_temp = [];

                foreach ($reorder as $key => $val) {
                    $val = array_key_exists($key, $data[$i]) ? $data[$i][$key] : null;

                    if (empty($val)) {
                        if (in_array($master_column[$key]['data_type'], ['int', 'tinyint', 'mediumint', 'bigint'])) {
                            $val = '0';
                        } else {
                            $val = $val === '0' ? $val : 'NULL';
                        }
                    }

                    array_push($values_temp, $val);
                }

                $values_temp = implode(', ', $values_temp);
                array_push($values, "({$values_temp})");
            }

            $values = implode(', ', $values);
            $values = str_replace($time_char, 'NOW()', $values);
            $values = str_replace($null_char, 'NULL', $values);
            $values = str_replace($zero_char, '0', $values);

            $column = implode(", ", $keys);
            $query = "INSERT INTO {$table} ({$column}) VALUES {$values}";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt->rowCount();
        }

        return false;
    }

    /**
     *  insertDuplicateUpdateData method
     *  insert or updata data on duplicate key to table
     *  @param string $table, array $data, array $protected
     *  @return bool $inserted
     */
    public function insertDuplicateUpdateData($table, $data = [])
    {
        $master_column = $this->checkColumnDetail($this->conf['database']['dbname'], $table);

        if (empty($table) || empty($master_column)) {
            return false;
        }

        $time_char = ['\'CURRENT_TIMESTAMP()\'', '\'NOW()\''];
        $null_char = ['\'NULL\'', ''];
        $zero_char = ['\'0\''];

        $data = filter_data($data, $master_column);

        $column = [];
        $update = [];
        $duplicates = [];

        if (!empty($data) && is_array($data) && count($data) != count($data, COUNT_RECURSIVE)) {
            foreach ($master_column as $key => $val) {
                if ($val['column_key'] === 'PRI' || $val['column_key'] === 'UNI') {
                    array_push($duplicates, $key);
                }
            }

            $keys = array_keys($master_column);
            $keys = array_keys($data[0]);

            if (empty($keys)) {
                return false;
            }

            $values = [];

            for ($i = 0; $i < count($data); $i++) {
                if (array_diff($keys, array_keys($data[$i])) != array_diff(array_keys($data[$i]), $keys)) {
                    continue;
                }

                $reorder = array_replace(array_flip($keys), $data[$i]);
                $values_temp = [];

                foreach ($reorder as $key => $val) {
                    $val = array_key_exists($key, $data[$i]) ? $data[$i][$key] : null;

                    if (empty($val)) {
                        $val = $val === '0' ? $val : 'NULL';
                    }

                    array_push($values_temp, $val);
                }

                $values_temp = implode(', ', $values_temp);
                array_push($values, "({$values_temp})");
            }

            $values = implode(', ', $values);
            $values = str_replace($time_char, 'NOW()', $values);
            $values = str_replace($null_char, 'NULL', $values);
            $values = str_replace($zero_char, '0', $values);

            $column = implode(", ", $keys);
            $query = "INSERT INTO {$table} ({$column}) VALUES {$values}";

            if (!empty($duplicates)) {
                for ($j = 0; $j < count($keys); $j++) {
                    if (!in_array($keys[$j], $duplicates)) {
                        array_push($update, "{$keys[$j]}=VALUES({$keys[$j]})");
                    }
                }

                if (!empty($update)) {
                    if (in_array('created', $keys)) {
                        array_push($update, "updated=VALUES(created)");
                    }

                    if (in_array('created_by', $keys)) {
                        array_push($update, "updated_by=VALUES(created_by)");
                    }

                    $update = implode(", ", $update);
                    $query .= " ON DUPLICATE KEY UPDATE {$update}";
                }
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt->rowCount();
        }

        return false;
    }

    /**
     *  updateData method
     *  update data from table
     *  @param string $table, array $data, array $condition, array $protected
     *  @return bool $updated
     */
    public function updateData($table, $data = [], $condition = [], $protected = [])
    {
        $master_column = $this->checkColumnDetail($this->conf['database']['dbname'], $table);

        if (empty($table) || empty($master_column)) {
            return false;
        }

        $time_char = ['\'CURRENT_TIMESTAMP()\'', '\'NOW()\''];
        $null_char = ['\'NULL\'', ''];
        $zero_char = ['\'0\''];

        $data = filter_data($data, $master_column, $protected);
        $column = [];
        $term = [];

        if (!empty($data) && is_array($data)) {
            foreach ($data as $key => $val) {
                array_push($column, "{$key} = {$val}");
            }
        }

        if (!empty($condition) && is_array($condition)) {
            foreach ($condition as $key => $val) {
                $val = trim($val);

                if (!empty($val) || $val === '0') {
                    $val = '\'' . addslashes($val) . '\'';
                    array_push($term, "{$key} = {$val}");
                } else {
                    array_push($term, "{$key} IS NULL");
                }
            }
        }

        if (!empty($column) && !empty($term)) {
            $column = implode(', ', $column);
            $column = str_replace($time_char, 'NOW()', $column);
            $column = str_replace($null_char, 'NULL', $column);
            $column = str_replace($zero_char, '0', $column);

            $term = implode(" AND ", $term);
            $term = str_replace($time_char, 'NOW()', $term);
            $term = str_replace($zero_char, '0', $term);

            $query = "UPDATE {$table} SET {$column} WHERE {$term}";

            $stmt = $this->conn->prepare($query);

            return $stmt->execute();
        }

        return false;
    }

    /**
     *  deleteData method
     *  delete data from table
     *  @param string $table, array $condition
     *  @return bool $deleted
     */
    public function deleteData($table, $condition = [])
    {
        $master_column = $this->checkColumn($this->conf['database']['dbname'], $table);

        if (empty($table) || empty($master_column)) {
            return false;
        }

        $time_char = ['\'CURRENT_TIMESTAMP()\'', '\'NOW()\''];
        $null_char = ['\'NULL\'', ''];
        $zero_char = ['\'0\''];

        $term = [];

        if (!empty($condition) && is_array($condition)) {
            foreach ($condition as $key => $val) {
                $val = trim($val);

                if (in_array($key, $master_column)) {
                    if (!empty($val) || $val === '0') {
                        $val = '\'' . addslashes($val) . '\'';
                        array_push($term, "{$key} = {$val}");
                    } else {
                        array_push($term, "{$key} IS NULL");
                    }
                }
            }
        }

        if (!empty($term)) {
            $term = implode(" AND ", $term);
            $term = str_replace($time_char, 'NOW()', $term);
            $term = str_replace($zero_char, '0', $term);

            $query = "DELETE FROM {$table} WHERE {$term}";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt->rowCount();
        }

        return false;
    }

    /**
     *  generateToken method
     *  @param array $data
     *  @return array $result
     */
    public function generateToken($data)
    {
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

            $encoded = JWT::encode(
                $payload, // Payload to be encoded in the JWT
                $this->conf['jwt']['key'], // The signing key
                $this->conf['jwt']['algorithm'] // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
            );

            $refresh_encoded = $this->generateRefreshToken($payload['data']);

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
     *  generateRefreshToken method - private
     *  @param array $jwt_data
     *  @return array $result
     */
    private function generateRefreshToken($data)
    {
        if (is_array_assoc($data)) {
            $issued_at = time();
            $token_id = base64_encode(mcrypt_create_iv(32));
            $server_name = $_SERVER['REMOTE_ADDR'];
            $not_before = $issued_at + $this->conf['jwt']['live'];
            $expire = $not_before + $this->conf['jwt']['expire_refresh'];

            $payload = [
                'iat' => $issued_at, // Issued at: time when the token was generated
                'jti' => $token_id, // Json Token Id: an unique identifier for the token
                'iss' => $server_name, // Issuer
                'aud' => client_ip(), // Audience
                'nbf' => $not_before, // Not before
                'exp' => $expire, // Expire
                'data' => $data // Data related to the signer user
            ];

            $encoded = JWT::encode(
                $payload, // Payload to be encoded in the JWT
                $this->conf['jwt']['key_refresh'], // The signing key
                $this->conf['jwt']['algorithm'] // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
            );

            $result = [
                'token' => $encoded,
                'expire' => $expire
            ];

            return $result;
        }

        return false;
    }
}