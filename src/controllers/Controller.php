<?php

use \Psr\Container\ContainerInterface as Container;

class Controller {
    protected $cont; // get slim container
    protected $conf; // global config
    protected $conn; // connection

    /**
     *  __construct method
     *  variable initialization
     *  @param {Container} $cont
     */
    public function __construct(Container $cont) {
        $this->cont = $cont;
        $this->conf = $this->cont->get('config');
        $this->conn = $this->dbConnect();
    }

    /**
     *  dbConnect
     *  mysql database connection using pdo
     */
    private function dbConnect() {
        $data = $this->conf['database'];
        $conn = new PDO("mysql:host={$data['host']};port={$data['port']};dbname={$data['name']};charset=utf8", $data['username'], $data['password']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }

    /**
     *  check table column
     *  @param {string} $table
     *  @return {array} $column
     */
    public function dbColumn($table) {
        $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table";
        $column = [];

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':database', $this->conf['database']['name'], PDO::PARAM_STR);
            $stmt->bindValue(':table', $table, PDO::PARAM_STR);
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($column, $row['COLUMN_NAME']);
            }
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }

        return $column;
    }

    /**
     *  check table column with data type
     *  @param {string} $table
     *  @return {array} $column
     */
    public function dbColumnDetail($table) {
        $query = "SELECT COLUMN_NAME, COLUMN_KEY, DATA_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table";
        $column = [];

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':database', $this->conf['database']['name'], PDO::PARAM_STR);
            $stmt->bindValue(':table', $table, PDO::PARAM_STR);
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $column[$row['COLUMN_NAME']] = [
                    'column_key' => $row['COLUMN_KEY'],
                    'data_type' => $row['DATA_TYPE'],
                    'is_nullable' => $row['IS_NULLABLE'],
                ];
            }
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }

        return $column;
    }

    /**
     *  count data from table
     *  @param {string} $table, {array} $conditions, {array} $custom_conditions, {array} $join, {array} $group_by
     *  @return {int} $count
     */
    public function dbCount($table, $conditions = [], $custom_conditions = [], $join = [], $group_by = []) {
        $count = 0;

        if (!is_string($table)) {
            return $count;
        }

        $master_column = $this->dbColumn($table);
        $query = "SELECT COUNT(*) AS count FROM {$table}";
        $condition_query = [];

        if (is_array_index($join)) {
            $join_query = implode(" ", $join);
            $query .= " {$join_query}";
        }

        if (!empty($conditions)) {
            // remove invalid column from conditions
            $conditions = filter_column($conditions, $master_column);
            $null_char = ['NULL', 'null'];

            foreach ($conditions as $key => $val) {
                if (in_array($val, $null_char)) {
                    $val = null;
                }

                switch (true) {
                    case is_array_index($val):
                        array_push($condition_query, "{$table}.{$key} IN ({$this->conn->quote($val)})");
                        break;
                    case is_null($val):
                        array_push($condition_query,  "{$table}.{$key} IS NULL");
                    default:
                        array_push($condition_query, "{$table}.{$key} = {$this->conn->quote($val)}");
                        break;
                }
            }
        }

        if (!empty($custom_conditions) && is_array_index($custom_conditions)) {
            array_push($condition_query, ...$custom_conditions);
        }

        if (!empty($condition_query)) {
            $condition_query = implode(" AND ", $condition_query);
            $query .= " WHERE {$condition_query}";
        }

        if (!empty($group_by) && !is_array_index($group_by)) {
            $column_group = implode(", ", $group_by);
            $query .= " GROUP BY {$column_group}";
            $query = "SELECT COUNT(*) AS count FROM ({$query}) AS count";
        }

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $count = $stmt->fetchColumn();
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }

        return $count;
    }

    /**
     *  SELECT query from table
     *  @param {string} $table, {array} $conditions, {array} $custom_conditions, {array} $column_select, {array} $column_deselect, {array} $custom_columns, {array} $join, {array} $group_by, {array} $custom_orders
     *  @return {array} $result
     */
    public function dbGetAll($table, $conditions = [], $custom_conditions = [], $column_select = [], $column_deselect = [], $custom_columns = [], $join = [], $group_by = [], $custom_orders = []) {
        $result = [
            'total_data' => 0,
            'error' => null
        ];

        if (!is_string($table)) {
            return $result;
        }

        $master_column = $this->dbColumn($table);
        $column = $master_column;
        $sort_list = ['ASC', 'DESC'];
        $sort = $sort_list[0];
        $limit = 20;
        $page = 1;
        $order = $column[0];
        $condition_query = [];

        if (!empty($column_select) && is_array_index($column_select)) {
            // filter data from all table columns, only keep selected columns
            $selected = array_intersect($column, $column_select);
            $column = $selected;
        }

        if (!empty($column_deselect) && is_array_index($column_deselect)) {
            switch (true) {
                case (in_array('*', $column_deselect)):
                    // filter data, exclude all columns
                    $column = [];
                    break;
                default:
                    // filter data, get column to exclude from valid selected columns or table columns
                    $deselected = array_intersect($column_deselect, $column);
                    $column = array_diff($column, $deselected);
                    break;
            }
        }

        if (!empty($join) && is_array_index($join)) {
            $prefixed = array_map(function ($col) use ($table) {
                return "{$table}.{$col}";
            }, $column);

            $column = $prefixed;
        }

        if (!empty($custom_columns) && is_array_index($custom_columns)) {
            $column = array_merge($column, $custom_columns);
        }

        // invalid if no column selected
        if (empty($column)) {
            return $result;
        }

        $column = implode(", ", $column);
        $query = "SELECT {$column} FROM {$table}";

        if (!empty($join) && is_array_index($join)) {
            $join_query = implode(" ", $join);
            $query .= " {$join_query}";
        }

        if (!empty($conditions)) {
            if (array_key_exists('sort', $conditions)) {
                $sort_index = array_search(strtoupper($conditions['sort']), $sort_list);

                if ($sort_index >= 0) {
                    $sort = $sort_list[$sort_index];
                }
            }

            if (array_key_exists('order', $conditions)) {
                if (!empty($conditions['order']) && in_array($conditions['order'], $master_column)) {
                    $order = $conditions['order'];
                }
            }

            if (array_key_exists('limit', $conditions)) {
                if (is_numeric($conditions['limit']) && (int) $conditions['limit'] >= 0) {
                    $limit = (int) $conditions['limit'];
                }
            }

            if (array_key_exists('page', $conditions)) {
                if (is_numeric($conditions['page']) && (int) $conditions['page'] > 0) {
                    $page = (int) $conditions['page'];
                }
            }

            // remove invalid column from conditions
            $conditions = filter_column($conditions, $master_column);
            $null_char = ['NULL', 'null'];

            foreach ($conditions as $key => $val) {
                if (in_array($val, $null_char)) {
                    $val = null;
                }

                switch (true) {
                    case is_array_index($val):
                        array_push($condition_query, "{$table}.{$key} IN ({$this->conn->quote($val)})");
                        break;
                    case is_null($val):
                        array_push($condition_query,  "{$table}.{$key} IS NULL");
                    default:
                        array_push($condition_query, "{$table}.{$key} = {$this->conn->quote($val)}");
                        break;
                }
            }
        }

        if (!empty($custom_conditions) && is_array_index($custom_conditions)) {
            $condition_query = array_merge($condition_query, $custom_conditions);
        }

        if (!empty($condition_query)) {
            $condition_query = implode(" AND ", $condition_query);
            $query .= " WHERE {$condition_query}";
        }

        if (!empty($group_by) && is_array_index($group_by)) {
            $column_group = implode(", ", $group_by);
            $query .= " GROUP BY {$column_group}";
        }

        if (!empty($join) && is_array_index($join)) {
            $order = "{$table}.{$order}";
        }

        if (!empty($custom_orders) && is_array_index($custom_orders)) {
            $column_order = implode(", ", $custom_orders);
            $query .= " ORDER BY {$column_group} {$sort}";
        } else {
            $query .= " ORDER BY {$order} {$sort}";
        }

        if ($limit > 0) {
            $query .= " LIMIT {$limit}";
            $offset = ($limit * $page) - $limit;

            if (is_numeric($offset) && $offset >= 0) {
                $query .= " OFFSET {$offset}";
            }
        }

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $data = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = array_map('utf8_encode', $row);
            }

            if (!empty($data)) {
                $result['total_data'] = $this->dbCount($table, $conditions, $custom_conditions, $join, $group_by);
                $result['data'] = $data;

                $paging = [
                    'current' => $page,
                    'previous' => 1,
                    'next' => 1,
                    'first' => 1,
                    'last' => 1
                ];
    
                if ($limit > 0) {
                    $page_last = ceil((int) $result['total_data'] / $limit);
                    $page_next = $page + 1;
                    $page_previous = $page - 1;
    
                    $paging['previous'] = $page_previous > 0 ? $page_previous : 1;
                    $paging['next'] = $page_next <= $page_last ? $page_next : $page_last;
                    $paging['last'] = $page_last > 0 ? $page_last : 1;
                }
    
                $result['paging'] = $paging;
    
                unset($result['error']);
            }
        } catch (PDOException $e) {
            $error = $e->getMessage();
            $result['error'] = $error;
        }

        return $result;
    }

    /**
     *  SELECT query for detail specific condition
     *  @param {string} $table, {array} $conditions, {array} $custom_conditions, {array} $column_select, {array} $column_deselect, {array} $custom_columns, {array} $join, {array} $group_by, {array} $custom_orders
     *  @return {array} $result
     */
    public function dbGetDetail($table = '', $conditions = [], $custom_conditions = [], $column_select = [], $column_deselect = [], $custom_columns = [], $join = []) {
        $result = [
            'total_data' => 0,
            'error' => null
        ];

        if (!is_string($table)) {
            return $result;
        }

        $master_column = $this->dbColumn($table);
        $column = $master_column;
        $condition_query = [];

        if (!empty($column_select) && is_array_index($column_select)) {
            // filter data from all table columns, only keep selected columns
            $selected = array_intersect($column, $column_select);
            $column = $selected;
        }

        if (!empty($column_deselect) && is_array_index($column_deselect)) {
            switch (true) {
                case (in_array('*', $column_deselect)):
                    // filter data, exclude all columns
                    $column = [];
                    break;
                default:
                    // filter data, get column to exclude from valid selected columns or table columns
                    $deselected = array_intersect($column_deselect, $column);
                    $column = array_diff($column, $deselected);
                    break;
            }
        }

        if (!empty($join) && is_array_index($join)) {
            $prefixed = array_map(function ($col) use ($table) {
                return "{$table}.{$col}";
            }, $column);

            $column = $prefixed;
        }

        if (!empty($custom_columns) && is_array_index($custom_columns)) {
            $column = array_merge($column, $custom_columns);
        }

        // invalid if no column selected
        if (empty($column)) {
            return $result;
        }

        $column = implode(", ", $column);
        $query = "SELECT {$column}";

        if (!empty($table)) {
            $query .= " FROM {$table}";

            if (!empty($join) && is_array_index($join)) {
                $join_query = implode(" ", $join);
                $query .= " {$join_query}";
            }

            if (!empty($conditions)) {
                // remove invalid column from conditions
                $conditions = filter_column($conditions, $master_column);
                $null_char = ['NULL', ''];

                foreach ($conditions as $key => $val) {
                    switch (true) {
                        case is_array($val):
                            $val = implode(",", $val);
                            array_push($condition_query, "{$table}.{$key} IN ({$this->conn->quote($val)})");
                            break;
                        case is_null($val):
                            array_push($condition_query,  "{$table}.{$key} IS NULL");
                        case in_array(strtoupper($val), $null_char):
                            array_push($condition_query, "{$table}.{$key} IS NULL");
                            break;
                        default:
                            array_push($condition_query, "{$table}.{$key} = {$this->conn->quote($val)}");
                            break;
                    }
                }
            }

            if (!empty($custom_conditions) && is_array_index($custom_conditions)) {
                $condition_query = array_merge($condition_query, $custom_conditions);
            }

            if (!empty($condition_query)) {
                $condition_query = implode(" AND ", $condition_query);
                $query .= " WHERE {$condition_query}";
            }


            $query .= " LIMIT 1";
        }

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $result['total_data'] = $stmt->rowCount() ?: 1;
                $result['data'] = $data;

                unset($result['error']);
            }
        } catch (PDOException $e) {
            $error = $e->getMessage();
            $result['error'] = $error;
        }

        return $result;
    }

    /**
     *  INSERT query
     *  @param {string} $table, {array} $data, {array} $protected_columns
     *  @return {array} $result
     */
    public function dbInsert($table, $data = [], $protected_columns = []) {
        $result = [
            'total_data' => 0,
            'error' => null
        ];

        if (!is_string($table)) {
            return $result;
        }

        $master_column = $this->dbColumn($table);
        $column = [];
        $query = null;

        $time_char = ['CURRENT_TIMESTAMP()', 'NOW()'];
        $null_char = ['NULL', ''];
        $zero_char = ['0'];

        // remove invalid data
        $data = filter_data($data, $master_column, $protected_columns);

        if (!empty($data) && is_array($data)) {
            foreach ($data as $key => $val) {
                switch (true) {
                    case (in_array(strtoupper($val), $time_char)):
                        array_push($column, "{$key} = NOW()");
                        break;
                    case (in_array(strtoupper($val), $null_char)):
                        array_push($column, "{$key} = NULL");
                        break;
                    case (in_array($val, $zero_char)):
                        array_push($column, "{$key} = 0");
                        break;
                    default:
                        array_push($column, "{$key} = {$this->conn->quote($val)}");
                        break;
                }
            }

            $column = implode(", ", $column);
            $query = "INSERT INTO {$table} SET {$column}";
        }

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            if ($stmt->rowCount()) {
                $result['total_data'] = $stmt->rowCount();
                $result['data']['id'] = $this->conn->lastInsertId();

                unset($result['error']);
            }
        } catch (PDOException $e) {
            $error = $e->getMessage();
            $result['error'] = $error;
        }

        return $result;
    }

    /**
     *  UPDATE query
     *  @param {string} $table, {array} $data, {array} $conditions, {array} $protected_columns
     *  @return {array} $result
     */
    public function dbUpdate($table, $data = [], $conditions = [], $protected_columns = []) {
        $result = [
            'total_data' => 0,
            'error' => null
        ];

        if (!is_string($table)) {
            return $result;
        }

        $master_column = $this->dbColumn($table);
        $query = "UPDATE {$table}";
        $column_query = [];
        $condition_query = [];

        $time_char = ['CURRENT_TIMESTAMP()', 'NOW()'];
        $null_char = ['NULL', ''];
        $zero_char = ['0'];

        // remove invalid data
        $data = filter_data($data, $master_column, $protected_columns);
        // remove invalid column from conditions
        $conditions = filter_column($conditions, $master_column);

        if (!empty($data) && is_array_assoc($data)) {
            foreach ($data as $key => $val) {
                switch (true) {
                    case (in_array(strtoupper($val), $time_char)):
                        array_push($column_query, "{$key} = NOW()");
                        break;
                    case (in_array(strtoupper($val), $null_char)):
                        array_push($column_query, "{$key} = NULL");
                        break;
                    case (in_array($val, $zero_char)):
                        array_push($column_query, "{$key} = 0");
                        break;
                    default:
                        array_push($column_query, "{$key} = {$this->conn->quote($val)}");
                        break;
                }
            }
        }

        if (!empty($conditions) && is_array_assoc($conditions)) {
            foreach ($conditions as $key => $val) {
                switch (true) {
                    case (in_array(strtoupper($val), $time_char)):
                        array_push($condition_query, "{$key} = NOW()");
                        break;
                    case (in_array(strtoupper($val), $null_char)):
                        array_push($condition_query, "{$key} IS NULL");
                        break;
                    case (in_array($val, $zero_char)):
                        array_push($condition_query, "{$key} = 0");
                        break;
                    default:
                        array_push($condition_query, "{$key} = {$this->conn->quote($val)}");
                        break;
                }
            }
        }

        // update query is unsafe without data and condition
        if (empty($column_query) || empty($condition_query)) {
            return $result;
        }

        $column_query = implode(", ", $column_query);
        $query .= " SET {$column_query}";

        $condition_query = implode(" AND ", $condition_query);
        $query .= " WHERE {$condition_query}";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            if ($stmt->rowCount()) {
                $result['total_data'] = $stmt->rowCount();
                $result['data'] = $conditions;

                unset($result['error']);
            }
        } catch (PDOException $e) {
            $error = $e->getMessage();
            $result['error'] = $error;
        }

        return $result;
    }

    /**
     *  DELETE query
     *  @param {string} $table, {array} $conditions
     *  @return {array} $result
     */
    public function dbDelete($table, $conditions = []) {
        $result = [
            'total_data' => 0,
            'error' => null
        ];

        if (!is_string($table)) {
            return $result;
        }

        $master_column = $this->dbColumn($table);
        $query = "DELETE FROM {$table}";
        $condition_query = [];

        // remove invalid column from conditions
        $conditions = filter_column($conditions, $master_column);

        if (!empty($conditions) && is_array_assoc($conditions)) {
            foreach ($conditions as $key => $val) {
                switch (true) {
                    case (in_array(strtoupper($val), $time_char)):
                        array_push($condition_query, "{$key} = NOW()");
                        break;
                    case (in_array(strtoupper($val), $null_char)):
                        array_push($condition_query, "{$key} IS NULL");
                        break;
                    case (in_array($val, $zero_char)):
                        array_push($condition_query, "{$key} = 0");
                        break;
                    default:
                        array_push($condition_query, "{$key} = {$this->conn->quote($val)}");
                        break;
                }
            }
        }

        // delete query is unsafe without condition
        if (empty($condition_query)) {
            return $result;
        }

        $condition_query = implode(" AND ", $condition_query);
        $query .= " WHERE {$condition_query}";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
    
            if ($stmt->rowCount()) {
                $result['total_data'] = $stmt->rowCount();
                $result['data'] = $conditions;
    
                unset($result['error']);
            }
        } catch (PDOException $e) {
            $error = $e->getMessage();
            $result['error'] = $error;
        }
    
        return $result;
    }

    /**
     *  INSERT MULTIPLE query
     *  @param {string} $table, {array} $data, {array} $protected_columns
     *  @return {array} $result
     */
    public function dbInsertMany($table, $data = [], $protected_columns = []) {
        $result = [
            'total_data' => 0,
            'error' => null
        ];

        if (!is_string($table) || empty($data) || !is_array($data)) {
            return $result;
        }

        $master_column = $this->dbColumn($table);

        $time_char = ['CURRENT_TIMESTAMP()', 'NOW()'];
        $null_char = ['NULL', ''];
        $zero_char = ['0'];

        // remove invalid data
        $data = filter_data($data, $master_column, $protected_columns);

        if (empty($data) || count($data) == count($data, COUNT_RECURSIVE)) {
            return $result;
        }

        $query = "INSERT INTO {$table}";
        $column = array_keys($data[0]);
        $values = [];

        for ($i = 0; $i < count($data); $i++) {
            if ($column != array_keys($data[$i])) {
                return $result;
            }

            $temp_values = [];

            foreach ($data[$i] as $key => $val) {
                switch (true) {
                    case (in_array(strtoupper($val), $time_char)):
                        array_push($temp_values, "NOW()");
                        break;
                    case (in_array(strtoupper($val), $null_char)):
                        array_push($temp_values, "NULL");
                        break;
                    case (in_array($val, $zero_char)):
                        array_push($temp_values, "0");
                        break;
                    default:
                        array_push($temp_values, $this->conn->quote($val));
                        break;
                }
            }

            $temp_values = implode(", ", $temp_values);
            array_push($values, "({$temp_values})");
        }

        // insert query is unsafe without values
        if (empty($values)) {
            return $result;
        }

        $column = implode(", ", $column);
        $values = implode(", ", $values);
        $query .= " ({$column}) VALUES {$values}";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            if ($stmt->rowCount()) {
                $total_data = $stmt->rowCount();
                $last_id = $this->conn->lastInsertId();
                $inserted = [];

                for ($id = $last_id; $id < ($total_data + $last_id); $id++) {
                    array_push($inserted, ['id' => (string) $id]);
                }

                $result['total_data'] = $total_data;
                $result['data'] = $inserted;

                unset($result['error']);
            }
        } catch (PDOException $e) {
            $error = $e->getMessage();
            $result['error'] = $error;
        }

        return $result;
    }

    /**
     *  INSERT MULTIPLE DUPLICATE UPDATE query
     *  @param {string} $table, {array} $data, {array} $protected_columns
     *  @return {array} $result
     */
    public function dbInsertManyUpdate($table, $data = [], $protected_columns = []) {
        $result = [
            'total_data' => 0,
            'error' => null
        ];

        if (!is_string($table) || empty($data) || !is_array($data)) {
            return $result;
        }

        $master_column = $this->dbColumnDetail($table);

        $time_char = ['CURRENT_TIMESTAMP()', 'NOW()'];
        $null_char = ['NULL', ''];
        $zero_char = ['0'];

        // remove invalid data
        $data = filter_data($data, $master_column, $protected_columns);

        if (empty($data) || count($data) == count($data, COUNT_RECURSIVE)) {
            return $result;
        }

        $query = "INSERT INTO {$table}";
        $column = array_keys($data[0]);
        $update = [];
        $duplicates = [];
        $values = [];

        foreach ($master_column as $key => $val) {
            if ($val['column_key'] === 'PRI' || $val['column_key'] === 'UNI') {
                array_push($duplicates, $key);
            }
        }

        for ($i = 0; $i < count($data); $i++) {
            if ($column != array_keys($data[$i])) {
                return $result;
            }

            $temp_values = [];

            foreach ($data[$i] as $key => $val) {
                switch (true) {
                    case (in_array(strtoupper($val), $time_char)):
                        array_push($temp_values, "NOW()");
                        break;
                    case (in_array(strtoupper($val), $null_char)):
                        array_push($temp_values, "NULL");
                        break;
                    case (in_array($val, $zero_char)):
                        array_push($temp_values, "0");
                        break;
                    default:
                        array_push($temp_values, $this->conn->quote($val));
                        break;
                }
            }

            $temp_values = implode(", ", $temp_values);
            array_push($values, "({$temp_values})");
        }

        // insert query is unsafe without values
        if (empty($values)) {
            return $result;
        }

        if (!empty($duplicates)) {
            for ($j = 0; $j < count($column); $j++) {
                if (!in_array($column[$j], $duplicates)) {
                    array_push($update, "{$column[$j]}=VALUES({$column[$j]})");
                }
            }

            if (!empty($update)) {
                if (in_array('created', $column)) {
                    array_push($update, "updated=VALUES(created)");
                }

                if (in_array('created_by', $column)) {
                    array_push($update, "updated_by=VALUES(created_by)");
                }
            }
        }

        $column = implode(", ", $column);
        $values = implode(", ", $values);
        $query .= " ({$column}) VALUES {$values}";

        if (!empty($update)) {
            $update = implode(", ", $update);
            $query .= " ON DUPLICATE KEY UPDATE {$update}";
        }

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            if ($stmt->rowCount()) {
                $result['total_data'] = $stmt->rowCount();
                $result['data'] = $data;

                unset($result['error']);
            }
        } catch (PDOException $e) {
            $error = $e->getMessage();
            $result['error'] = $error;
        }

        return $result;
    }
}