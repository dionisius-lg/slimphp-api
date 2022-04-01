<?php

use App\Validator;

class ProductCategoriesController extends AppController
{
    public function __construct(Slim\Container $ci)
    {
        parent::__construct($ci);
        $this->table = 'product_categories';
        $this->jwt = $this->ci->get('globalSettings')['jwt'];
    }

    /**
     *  getAll method
     *  get all data
     */
    public function getAll($request, $response)
    {
        $decoded = jwtDecode(
            $request->getHeaderLine('authorization'),
            $this->jwt['key'],
            $this->jwt['algorithm']
        );

        $params    = $request->getQueryParams();
        $column    = $this->checkColumn($this->db['database']['dbname'], $this->table);
        $sort      = ['ASC', 'DESC'];
        $error     = ['order' => true, 'group' => true];
        $condition = [];

        $clause = [
            'order' => 'id',
            'group' => 'id',
            'sort'  => $sort[0],
            'limit' => 20,
            'page'  => 1,
            'start' => null,
            'end'   => null
        ];

        foreach ($params as $key => $val) {
            if (!empty($val) || $val === '0') {
                $clause[$key] = trim($val);
            }
        }

        if (!is_numeric($clause['limit']) || !is_numeric($clause['page']) || !in_array(strtoupper($clause['sort']), $sort)) {
            $handler = $this->ci->get('badRequestHandler');
            return $handler($request, $response);
        }

        // customize here ----------------------------------------------------------

        $column_like = [
            'name',
        ];

        $column_date = [
            'create_date',
            'update_date',
        ];

        $column_deselect = [
            
        ];

        $join_column = [
            "create_user.username AS create_username",
            "create_user.fullname AS create_fullname",
            "update_user.username AS update_username",
            "update_user.fullname AS update_fullname",
        ];

        $join_table = [
            "LEFT JOIN users AS create_user ON create_user.id = {$this->table}.create_user_id",
            "LEFT JOIN users AS update_user ON update_user.id = {$this->table}.update_user_id",
        ];

        if ((string) (int) $clause['start'] === $clause['start'] && (string) (int) $clause['end'] === $clause['end']) {
            $start_date = date('Y-m-d', $clause['start']);
            $end_date   = date('Y-m-d', $clause['end']);

            if ($clause['start'] > $clause['end']) {
                $end_date = $start_date;
            }

            array_push($condition, "DATE({$this->table}.create_date) BETWEEN '{$start_date}' AND '{$end_date}'");
            $clause = array_diff_key($clause, array_flip(['start', 'end']));
        }

        if (array_key_exists('x_id', $clause)) {
            if (is_numeric($clause['x_id'])) {
                array_push($condition, "{$this->table}.id <> {$clause['x_id']}");
            }

            unset($clause['x_id']);
        }

        // end of customize here ----------------------------------------------------------

        foreach ($clause as $key => $val) {
            if ((!empty($val) || $val == '0') && in_array($key, $column)) {
                if (in_array($key, $column_like)) {
                    array_push($condition, "{$this->table}.{$key} LIKE '%{$val}%'");
                } elseif (in_array($key, $column_date)) {
                    if ((string) (int) $val === $val) {
                        $date = date('Y-m-d', $val);
                        array_push($condition, "DATE('{$this->table}.{$key}') = '{$date}'");
                    }
                } else {
                    array_push($condition, "{$this->table}.{$key} = '{$val}'");
                }

                unset($clause[$key]);
            }
        }

        $join = [
            'column' => $join_column,
            'table'  => $join_table
        ];

        $result = $this->parentGetAll($this->table, $clause, $condition, $column, $column_deselect, $join);

        return $response->withJson($result)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($result['response_code']);
    }

    /**
     *  getDetail method
     *  get detail data
     */
    public function getDetail($request, $response, $args)
    {
        $decoded = jwtDecode(
            $request->getHeaderLine('authorization'),
            $this->jwt['key'],
            $this->jwt['algorithm']
        );

        $id     = $args['id'];
        $column = $this->checkColumn($this->db['database']['dbname'], $this->table);

        // customize here ----------------------------------------------------------

        $column_deselect = [
            
        ];

        $join_column = [
            "create_user.username AS create_username",
            "create_user.fullname AS create_fullname",
            "update_user.username AS update_username",
            "update_user.fullname AS update_fullname",
        ];

        $join_table = [
            "LEFT JOIN users AS create_user ON create_user.id = {$this->table}.create_user_id",
            "LEFT JOIN users AS update_user ON update_user.id = {$this->table}.update_user_id",
        ];

        $condition = [
            
        ];

        // end of customize here ----------------------------------------------------------

        if (!empty($column)) {
            if (!empty($column_deselect) && is_array($column_deselect)) {
                $column = array_diff($column, $column_deselect);
            }

            $column = array_values($column);

            for ($i = 0; $i < count($column); $i++) {
                if (!strpos($column[$i], ".")) {
                    $column[$i] = "{$this->table}.{$column[$i]}";
                }
            }

            $column = implode(", ", $column);
        } else {
            $column = "{$this->table}.*";
        }

        if (!empty($join_column) && is_array($join_column)) {
            $column .= ", ".implode(", ", $join_column);
        }

        if (!empty($join_table) && is_array($join_table)) {
            $join = implode(" ", $join_table);
        } else {
            $join = null;
        }

        if (!empty($condition) && is_array($condition)) {
            $condition = " AND ".implode(" AND ", $condition);
        } else {
            $condition = null;
        }

        $stmt = $this->con->prepare("SELECT {$column} FROM {$this->table} {$join} WHERE {$this->table}.id = :id {$condition}");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $count = $stmt->rowCount();

        if ($count == 0) {
            $handler = $this->ci->get('notFoundHandler');
            return $handler($request, $response);
        }

        $result = [
            'request_time'   => $this->request_time,
            'execution_time' => executionTime($this->request_time),
            'response_code'  => 200,
            'status'         => 'success',
            'total_data'     => $count,
            'data'           => $stmt->fetch(PDO::FETCH_ASSOC)
        ];

        return $response->withJson($result)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($result['response_code']);
    }

    /**
     *  create method
     *  create new data
     */
    public function create($request, $response)
    {
        $decoded = jwtDecode(
            $request->getHeaderLine('authorization'),
            $this->jwt['key'],
            $this->jwt['algorithm']
        );

        $data_temp = $request->getParsedBody();
        $column    = $this->checkColumn($this->db['database']['dbname'], $this->table);
        $protected = ['id', 'update_date', 'update_user_id'];
        $data      = [];

        if (empty($data_temp)) {
            $handler = $this->ci->get('badRequestHandler');
            return $handler($request, $response);
        }

        foreach ($data_temp as $key => $val) {
            if (!in_array($key, $column) || in_array($key, $protected)) {
                $handler = $this->ci->get('badRequestHandler');
                return $handler($request, $response, "The property of {$key} is not allowed");
            } else {
                if (!empty($val) || $val === '0') {
                    $data[$key] = $val;
                }
            }
        }

        $validator = new Validator();
        $validator->set('ProductCategories', 'create');

        if (!$validator->validate($data)) {
            $handler = $this->ci->get('badRequestHandler');
            return $handler($request, $response, $validator->getErrors()[0]);
        }

        if (array_key_exists('name', $data)) {
            $check = $this->parentCheck($this->table, ['name' => $data['name']]);

            if ($check > 0) {
                $handler = $this->ci->get('badRequestHandler');
                return $handler($request, $response, 'Name already exist');
            }
        }

        if (!array_key_exists('create_date', $data)) {
            $data['create_date'] = date('Y-m-d H:i:s');
        }

        if (!array_key_exists('create_user_id', $data)) {
            $data['create_user_id'] = $decoded['data']['id'];
        }

        $data['is_active'] = 1;

        $inserted = $this->parentInsert($this->table, $data);

        if ($inserted) {
            $result = [
                'request_time'   => $this->request_time,
                'execution_time' => executionTime($this->request_time),
                'response_code'  => 201,
                'status'         => 'success',
                'data'           => ['id' => $this->con->lastInsertId()]
            ];

            return $response->withJson($result)
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($result['response_code']);
        } else {
            $handler = $this->ci->get('errorHandler');
            return $handler($request, $response);
        }
    }

    /**
     *  create method
     *  create new data
     */
    public function createMultiple($request, $response)
    {
        $decoded = jwtDecode(
            $request->getHeaderLine('authorization'),
            $this->jwt['key'],
            $this->jwt['algorithm']
        );

        $data_temp = $request->getParsedBody();
        $column    = $this->checkColumnWithType($this->db['database']['dbname'], $this->table);
        $protected = ['id', 'update_date', 'update_user_id'];
        $data      = [];

        if (empty($data_temp)) {
            $handler = $this->ci->get('badRequestHandler');
            return $handler($request, $response);
        }

        if (count($data_temp) == count($data_temp, COUNT_RECURSIVE)) {
            $handler = $this->ci->get('badRequestHandler');
            return $handler($request, $response);
        }

        $column = array_diff_key($column, array_flip($protected));

        for ($x = 0; $x < count($data_temp); $x++) {
            $temp_key  = array_keys($data_temp[$x]);
            $data_x    = array_replace(array_flip($temp_key), $data_temp[$x]);

            foreach ($data_x as $key => $val) {
                if (!array_key_exists($key, $column) || in_array($key, $protected)) {
                    $handler = $this->ci->get('badRequestHandler');
                    return $handler($request, $response, "The property of {$key} is not allowed");
                } else {
                    if (!empty($val) || $val === '0') {
                        $data[$x][$key] = $val;
                    }
                }
            }
        }

        if (empty($data) || count($data_temp) == count($data_temp, COUNT_RECURSIVE)) {
            $handler = $this->ci->get('badRequestHandler');
            return $handler($request, $response);
        }

        $validator = new Validator();
        $validator->set('ProductCategories', 'create');

        $names = [];

        for ($y = 0; $y < count($data); $y++) {
            $data[$y]['is_active'] = 1;

            $key    = array_keys($data[$y]);
            $data_y = array_replace(array_flip($key), $data[$y]);

            if (!$validator->validate($data_y)) {
                $handler = $this->ci->get('badRequestHandler');
                return $handler($request, $response, $validator->getErrors()[0]);
            }

            if (array_key_exists('name', $data_y)) {
                $check = $this->parentCheck($this->table, ['name' => $data_y['name']]);

                if ($check > 0 ) {
                    $handler = $this->ci->get('badRequestHandler');
                    return $handler($request, $response, 'Name already exist');
                }

                if (in_array($data_y['name'], $names)) {
                    $handler = $this->ci->get('badRequestHandler');
                    return $handler($request, $response, 'Duplicate entry on name');
                }

                array_push($names, $data_y['name']);
            }

            if (!array_key_exists('create_date', $data_y)) {
                $data[$y]['create_date'] = date('Y-m-d H:i:s');
            }

            if (!array_key_exists('create_user_id', $data_y)) {
                $data[$y]['create_user_id'] = $decoded['data']['id'];
            }
        }

        $inserted = $this->parentInsertMany($this->table, $column, $data);

        if ($inserted) {
            $inserted_min = $this->con->lastInsertId();
            $inserted_max = $inserted + $inserted_min;
            $inserted_id  = [];

            for ($z = $inserted_min; $z < $inserted_max; $z++) {
                array_push($inserted_id, ['id' => (string) $z]);
            }

            $result = [
                'request_time'   => $this->request_time,
                'execution_time' => executionTime($this->request_time),
                'response_code'  => 201,
                'status'         => 'success',
                'total_data'     => (int) $inserted_max,
                'data'           => $inserted_id
            ];

            return $response->withJson($result)
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($result['response_code']);
        } else {
            $handler = $this->ci->get('errorHandler');
            return $handler($request, $response);
        }
    }

    /**
     *  update method
     *  update existing data by id
     */
    public function update($request, $response, $args)
    {
        $decoded = jwtDecode(
            $request->getHeaderLine('authorization'),
            $this->jwt['key'],
            $this->jwt['algorithm']
        );

        $id        = $args['id'];
        $data_temp = $request->getParsedBody();
        $column    = $this->checkColumn($this->db['database']['dbname'], $this->table);
        $protected = ['id', 'create_date', 'create_user_id'];
        $data      = [];

        if (empty($data_temp)) {
            $handler = $this->ci->get('badRequestHandler');
            return $handler($request, $response);
        }

        foreach ($data_temp as $key => $val) {
            if (!in_array($key, $column) || in_array($key, $protected)) {
                $handler = $this->ci->get('badRequestHandler');
                return $handler($request, $response, "The property of {$key} is not allowed");
            } else {
                if (!empty($val) || $val === '0') {
                    $data[$key] = $val;
                }
            }
        }

        $validator = new Validator();
        $validator->set('ProductCategories', 'update')->validate($data);

        if (!$validator->validate($data)) {
            print_r($validator->getErrors()); exit;
            $handler = $this->ci->get('badRequestHandler');
            return $handler($request, $response, $validator->getErrors()[0]);
        }

        $check = $this->parentCheck($this->table, ['id' => $id]);

        if ($check == 0) {
            $handler = $this->ci->get('notFoundHandler');
            return $handler($request, $response);
        }

        if (array_key_exists('name', $data)) {
            $check = $this->parentCheck($this->table, ['name' => $data['name'], 'x_id' => $id]);

            if ($check > 0) {
                $handler = $this->ci->get('badRequestHandler');
                return $handler($request, $response, 'Name already exist');
            }
        }

        if (!array_key_exists('update_date', $data)) {
            $data['update_date'] = date('Y-m-d H:i:s');
        }

        if (!array_key_exists('update_user_id', $data)) {
            $data['update_user_id'] = $decoded['data']['id'];
        }

        $updated = $this->parentUpdate($this->table, $data, ['id' => $id]);

        if ($updated) {
            $result = [
                'request_time'   => $this->request_time,
                'execution_time' => executionTime($this->request_time),
                'response_code'  => 200,
                'status'         => 'success',
                'data'           => ['id' => $id]
            ];

            return $response->withJson($result)
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($result['response_code']);
        } else {
            $handler = $this->ci->get('errorHandler');
            return $handler($request, $response);
        }
    }

    /**
     *  delete method
     *  delete existing data by id
     */
    public function delete($request, $response, $args)
    {
        $decoded = jwtDecode(
            $request->getHeaderLine('authorization'),
            $this->jwt['key'],
            $this->jwt['algorithm']
        );

        $id    = $args['id'];
        $check = $this->parentCheck($this->table, ['id' => $id]);

        if ($check == 0) {
            $handler = $this->ci->get('notFoundHandler');
            return $handler($request, $response);
        }

        $deleted = $this->parentDelete($this->table, ['id' => $id]);

        if ($deleted) {
            $result = [
                'request_time'   => $this->request_time,
                'execution_time' => executionTime($this->request_time),
                'response_code'  => 201,
                'status'         => 'success',
                'data'           => ['id' => $id]
            ];

            return $response->withJson($result)
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($result['response_code']);
        } else {
            $handler = $this->ci->get('errorHandler');
            return $handler($request, $response);
        }
    }
}
