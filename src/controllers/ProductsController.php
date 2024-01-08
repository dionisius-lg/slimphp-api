<?php

use Psr\Container\ContainerInterface;

class ProductsController extends ApiController
{
    /**
     *  __construct method
     *  variable initialization
     *  @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
        $this->table = 'products';
    }

    /**
     *  getAll method
     *  get all data
     */
    public function getAll($request, $response)
    {
        $decoded = $request->getAttribute('decoded');
        $condition_custom = [];
        $condition = array_merge(['limit' => 20], $request->getQueryParams());

        $column_select = [

        ];

        $column_deselect = [

        ];

        $column_custom = [
            "product_categories.name AS product_category",
        ];

        $join = [
            "LEFT JOIN product_categories ON product_categories.id = {$this->table}.product_category_id",
        ];

        $group_by = [
            "{$this->table}.id",
        ];

        $order_custom = [

        ];

        if (array_key_exists('xxx_id', $condition)) {
            if (is_numeric($condition['xxx_id'])) {
                array_push($condition_custom, "{$this->table}.id <> {$condition['xxx_id']}");
            }

            unset($condition['xxx_id']);
        }

        if (array_key_exists('start', $condition)) {
            if ((string) (int) $condition['start'] === $condition['start']) {
                $start = date('Y-m-d', $condition['start']);
                $end = $start;

                if (array_key_exists('end', $condition)) {
                    if ((string) (int) $condition['end'] === $condition['end']) {
                        $end = date('Y-m-d', $condition['end']);
                    }

                    unset($condition['end']);
                }

                array_push($condition_custom, "DATE({$this->table}.created) BETWEEN '{$start}' AND '{$end}'");
                unset($condition['start']);
            }
        }

        if (array_key_exists('name', $condition)) {
            if (!empty($condition['name'])) {
                array_push($condition_custom, "{$this->table}.name LIKE '%{$condition['name']}%'");
            }

            unset($condition['name']);
        }

        if (array_key_exists('product_category', $condition)) {
            if (!empty($condition['product_category'])) {
                array_push($condition_custom, "product_categories.name LIKE '%{$condition['product_category']}%'");
            }

            unset($condition['product_category']);
        }
 
        $result = $this->getData($this->table, $condition, $condition_custom, $column_select, $column_deselect, $column_custom, $join, $group_by, $order_custom);

        if ($result['total'] > 0) {
            $handler = $this->ci->get('successHandler');
            return $handler($request, $response, $result);
        }

        $handler = $this->ci->get('notFoundHandler');
        return $handler($request, $response);
    }

    /**
     *  getDetail method
     *  get detail data
     */
    public function getDetail($request, $response, $args)
    {
        $decoded = $request->getAttribute('decoded');
        $condition_custom = [];
        $condition = array_merge(['limit' => 1], $args);

        $column_select = [

        ];

        $column_deselect = [

        ];

        $column_custom = [
            "product_categories.name AS product_category",
        ];

        $join = [
            "LEFT JOIN product_categories ON product_categories.id = {$this->table}.product_category_id",
        ];

        $group_by = [
            "{$this->table}.id",
        ];

        $order_custom = [

        ];

        $result = $this->getData($this->table, $condition, $condition_custom, $column_select, $column_deselect, $column_custom, $join, $group_by, $order_custom);

        if ($result['total'] > 0) {
            $result['data'] = $result['data'][0];
            
            if (array_key_exists('paging', $result)) {
                unset($result['paging']);
            }

            $handler = $this->ci->get('successHandler');
            return $handler($request, $response, $result);
        }

        $handler = $this->ci->get('notFoundHandler');
        return $handler($request, $response);
    }

    /**
     *  insert method
     *  insert new data
     */
    public function insert($request, $response)
    {
        $decoded = $request->getAttribute('decoded');
        $body = $request->getParsedBody();
        $protected = ['id'];

        if (array_key_exists('name', $body)) {
            $check = $this->checkData($this->table, ['name' => $body['name']]);

            if ($check > 0) {
                $handler = $this->ci->get('badRequestHandler');
                return $handler($request, $response, 'Name already exist');
            }
        }

        if (!array_key_exists('created', $body)) {
            $body['created'] = date('Y-m-d H:i:s');
        }

        if (!array_key_exists('created_by', $body)) {
            $body['created_by'] = $decoded['id'];
        }

        $inserted = $this->insertData($this->table, $body, $protected);

        if ($inserted) {
            $result = [
                'code'   => 201,
                'total'  => 1,
                'data'   => ['id' => $this->conn->lastInsertId()],
            ];

            $handler = $this->ci->get('successHandler');
            return $handler($request, $response, $result);
        }

        $handler = $this->ci->get('badRequestHandler');
        return $handler($request, $response, 'Invalid data');
    }

    /**
     *  update method
     *  update existing data by given id
     */
    public function update($request, $response, $args)
    {
        $decoded = $request->getAttribute('decoded');
        $body = $request->getParsedBody();
        $protected = ['id'];

        $check = $this->checkData($this->table, ['id' => $args['id']]);

        if ($check == 0) {
            $handler = $this->ci->get('notFoundHandler');
            return $handler($request, $response);
        }

        if (array_key_exists('name', $body)) {
            $check = $this->checkData($this->table, ['name' => $body['name'], 'xxx_id' => $args['id']]);

            if ($check > 0) {
                $handler = $this->ci->get('badRequestHandler');
                return $handler($request, $response, 'Name already exist');
            }
        }

        if (!array_key_exists('updated', $body)) {
            $body['updated'] = date('Y-m-d H:i:s');
        }

        if (!array_key_exists('updated_by', $body)) {
            $body['updated_by'] = $decoded['id'];
        }

        $updated = $this->updateData($this->table, $body, ['id' => $args['id']], $protected);

        if ($updated) {
            $result = [
                'total' => 1,
                'data'  => ['id' => $args['id']],
            ];

            $handler = $this->ci->get('successHandler');
            return $handler($request, $response, $result);
        }

        $handler = $this->ci->get('badRequestHandler');
        return $handler($request, $response, 'Invalid data');
    }

    /**
     *  delete method
     *  delete existing data by given id
     */
    public function delete($request, $response, $args)
    {
        $decoded = $request->getAttribute('decoded');
        $body = $request->getParsedBody();

        $check = $this->checkData($this->table, ['id' => $args['id']]);

        if ($check == 0) {
            $handler = $this->ci->get('notFoundHandler');
            return $handler($request, $response);
        }

        $deleted = $this->deleteData($this->table, ['id' => $args['id']]);

        if ($deleted) {
            $result = [
                'total' => 1,
                'data'  => ['id' => $args['id']],
            ];

            $handler = $this->ci->get('successHandler');
            return $handler($request, $response, $result);
        }

        $handler = $this->ci->get('badRequestHandler');
        return $handler($request, $response, 'Invalid data');
    }

    /**
     *  insertMany method
     *  insert new many data
     */
    public function insertMany($request, $response)
    {
        $decoded = $request->getAttribute('decoded');
        $body = $request->getParsedBody();
        $protected = ['id'];

        $data = [];
        $names = [];

        for ($i = 0; $i < count($body); $i++) {
            if (array_key_exists('name', $body[$i]) && !empty($body[$i]['name'])) {
                $check = $this->checkData($this->table, ['name' => $body[$i]['name']]);

                if ($check > 0 || in_array($body[$i]['name'], $names)) {
                    continue;
                }

                array_push($names, $body[$i]['name']);
            }

            if (!array_key_exists('created', $body[$i])) {
                $body[$i]['created'] = date('Y-m-d H:i:s');
            }

            if (!array_key_exists('created_by', $body[$i])) {
                $body[$i]['created_by'] = $decoded['id'];
            }

            array_push($data, $body[$i]);
        }

        if (empty($data)) {
            $handler = $this->ci->get('badRequestHandler');
            return $handler($request, $response, 'Empty data');
        }

        $inserted = $this->insertManyData($this->table, $data, $protected);

        if ($inserted) {
            $inserted_min = $this->conn->lastInsertId();
            $inserted_max = $inserted + $inserted_min;
            $inserted_ids = [];

            for ($j = $inserted_min; $j < $inserted_max; $j++) {
                array_push($inserted_ids, ['id' => (string) $j]);
            }

            $result = [
                'code'   => 201,
                'total'  => count($inserted_ids),
                'data'   => $inserted_ids,
            ];

            $handler = $this->ci->get('successHandler');
            return $handler($request, $response, $result);
        }

        $handler = $this->ci->get('badRequestHandler');
        return $handler($request, $response, 'Invalid data');
    }

    /**
     *  insertManyUpdate method
     *  insert many on duplicate update data
     */
    public function insertManyUpdate($request, $response)
    {
        $decoded = $request->getAttribute('decoded');
        $body = $request->getParsedBody();
        $protected = [];
        $master_column = $this->checkColumnDetail($this->conf['database']['dbname'], $this->table);

        $unique = null;
        $data = [];
        $names = [];

        foreach ($master_column as $key => $val) {
            if (($val['column_key'] === 'PRI' || $val['column_key'] === 'UNI') && !in_array($key, $protected)) {
                $unique = $key;
                break;
            }
        }

        for ($i = 0; $i < count($body); $i++) {
            if (array_key_exists('name', $body[$i]) && !empty($body[$i]['name']) && array_key_exists($unique, $body[$i])) {
                $check = $this->checkData($this->table, ['name' => $body[$i]['name'], 'xxx_' . $unique => $body[$i][$unique]]);

                if ($check > 0 || in_array($body[$i]['name'], $names)) {
                    continue;
                }

                array_push($names, $body[$i]['name']);
            }

            if (!array_key_exists('created', $body[$i])) {
                $body[$i]['created'] = date('Y-m-d H:i:s');
            }

            if (!array_key_exists('created_by', $body[$i])) {
                $body[$i]['created_by'] = $decoded['id'];
            }

            array_push($data, $body[$i]);
        }

        if (empty($data)) {
            $handler = $this->ci->get('badRequestHandler');
            return $handler($request, $response, 'Empty data');
        }

        $inserted = $this->insertDuplicateUpdateData($this->table, $data);

        if ($inserted) {
            $inserted_min = $this->conn->lastInsertId();
            $inserted_max = $inserted + $inserted_min;
            $inserted_ids = [];

            for ($j = $inserted_min; $j < $inserted_max; $j++) {
                array_push($inserted_ids, ['id' => (string) $j]);
            }

            $result = [
                'code'   => 200,
                'total'  => count($inserted_ids),
                'data'   => $inserted_ids,
            ];

            $handler = $this->ci->get('successHandler');
            return $handler($request, $response, $result);
        }

        $handler = $this->ci->get('badRequestHandler');
        return $handler($request, $response, 'Invalid data');
    }
}