<?php

use \Psr\Container\ContainerInterface as Container;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Upload\Storage\FileSystem;
use \Upload\File;
use \Upload\Validation\Mimetype;
use \Upload\Validation\Size;

class UserFilesController extends Controller {

    /**
     *  variable initialization
     *  @param {Container} $cont
     */
    public function __construct(Container $cont)  {
        parent::__construct($cont);
        $this->table = 'user_files';
    }

    /**
     *  get all data
     *  @param {Request} $req, {Response} $res
     *  @return {array} $handler
     */
    public function getAll(Request $req, Response $res) {
        $conditions = $req->getQueryParams();
        $custom_conditions = $column_select = $column_deselect = $custom_columns = $join = $group_by = $custom_orders = [];

        if (array_key_exists('start', $conditions)) {
            if ((string) (int) $conditions['start'] === $conditions['start']) {
                $start = date('Y-m-d', $conditions['start']);
                $end = $start;

                if (array_key_exists('end', $conditions)) {
                    if ((string) (int) $conditions['end'] === $conditions['end']) {
                        $end = date('Y-m-d', $conditions['end']);
                    }

                    unset($conditions['end']);
                }

                array_push($custom_conditions, "DATE({$this->table}.created) BETWEEN '{$start}' AND '{$end}'");
                unset($conditions['start']);
            }
        }

        if (array_key_exists('source', $conditions)) {
            array_push($custom_conditions, "{$this->table}.source LIKE '%{$conditions['source']}%'");
            unset($conditions['source']);
        }

        $result = $this->dbGetAll($this->table, $conditions, $custom_conditions, $column_select, $column_deselect, $custom_columns, $join, $group_by, $custom_orders);

        if ($result['total_data'] > 0) {
            $handler = $this->cont->get('successHandler');
            return $handler($req, $res, $result);
        }

        $handler = $this->cont->get('notFoundDataHandler');
        return $handler($req, $res);
    }

    /**
     *  get detail data by given arguments
     *  @param {Request} $req, {Response} $res, {array} $args
     *  @return {array} $handler
     */
    public function getDetail(Request $req, Response $res, $args) {
        $conditions = $args;
        $custom_conditions = $column_select = $column_deselect = $custom_columns = $join = [];

        if (empty($conditions)) {
            $handler = $this->cont->get('badRequestHandler');
            return $handler($req, $res);
        }

        $result = $this->dbGetDetail($this->table, $conditions, $custom_conditions, $column_select, $column_deselect, $custom_columns, $join);

        if ($result['total_data'] > 0) {
            $handler = $this->cont->get('successHandler');
            return $handler($req, $res, $result);
        }

        $handler = $this->cont->get('notFoundDataHandler');
        return $handler($req, $res);
    }

    /**
     *  insert new data
     *  @param {Request} $req, {Response} $res
     *  @return {array} $handler
     */
    public function insert(Request $req, Response $res) {
        $decoded = $req->getAttribute('decoded');
        $data = $req->getParsedBody();
        $protected = ['id'];

        // check available source
        $count = $this->dbCount($this->table, ['source' => $data['source']]);

        if ($count > 0) {
            $handler = $this->cont->get('badRequestHandler');
            return $handler($req, $res, 'Source already exist');
        }

        if (!array_key_exists('created', $data)) {
            $data['created'] = date('Y-m-d H:i:s');
        }

        if (!array_key_exists('created_by', $data)) {
            $data['created_by'] = $decoded['id'];
        }

        $result = $this->dbInsert($this->table, $data, $protected);

        if ($result['total_data'] > 0) {
            $handler = $this->cont->get('successCreatedHandler');
            return $handler($req, $res, $result);
        }

        $handler = $this->cont->get('badRequestHandler');
        return $handler($req, $res, $result['error'] ?: 'Invalid data');
    }

    /**
     *  update existing data by given arguments
     *  @param {Request} $req, {Response} $res, {array} $args
     *  @return {array} $handler
     */
    public function update(Request $req, Response $res, $args) {
        $decoded = $req->getAttribute('decoded');
        $data = $req->getParsedBody();
        $conditions = $args;
        $protected = ['id'];

        if (empty($conditions)) {
            $handler = $this->cont->get('badRequestHandler');
            return $handler($req, $res);
        }

        if (array_key_exists('id', $conditions)) {
            // check exist data
            $count = $this->dbCount($this->table, ['id' => $conditions['id']]);

            if ($count == 0) {
                $handler = $this->cont->get('notFoundDataHandler');
                return $handler($req, $res);
            }

            if (array_key_exists('source', $data)) {
                // check available source
                $count = $this->dbCount($this->table, [], ["source {$data['source']}", "id <> {$conditions['id']}"]);
    
                if ($count > 0) {
                    $handler = $this->cont->get('badRequestHandler');
                    return $handler($req, $res, 'Source already exist');
                }
            }
        }

        if (!array_key_exists('updated', $data)) {
            $data['updated'] = date('Y-m-d H:i:s');
        }

        if (!array_key_exists('updated_by', $data)) {
            $data['updated_by'] = $decoded['id'];
        }

        $result = $this->dbUpdate($this->table, $data, $conditions, $protected);

        if ($result['total_data'] > 0) {
            $handler = $this->cont->get('successHandler');
            return $handler($req, $res, $result);
        }

        $handler = $this->cont->get('badRequestHandler');
        return $handler($req, $res, $result['error'] ?: 'Invalid data');
    }

    /**
     *  delete existing data by given arguments
     *  @param {Request} $req, {Response} $res, {array} $args
     *  @return {array} $handler
     */
    public function delete(Request $req, Response $res, $args) {
        $decoded = $req->getAttribute('decoded');
        $data = $req->getParsedBody();

        if (empty($args)) {
            $handler = $this->cont->get('badRequestHandler');
            return $handler($req, $res);
        }

        $conditions = [];

        foreach ($args as $key => $val) {
            $conditions[$key] = $val;
        }

        $result = $this->dbDelete($this->table, $conditions);

        if ($result['total_data'] > 0) {
            $handler = $this->cont->get('successHandler');
            return $handler($req, $res, $result);
        }

        $handler = $this->cont->get('badRequestHandler');
        return $handler($req, $res, $result['error'] ?: 'Invalid data');
    }

    /**
     *  insert new multiple data
     *  @param {Request} $req, {Response} $res
     *  @return {array} $handler
     */
    public function insertMany(Request $req, Response $res) {
        $decoded = $req->getAttribute('decoded');
        $body = $req->getParsedBody();
        $protected = [];

        if (!is_array_multi($body)) {
            $handler = $this->cont->get('badRequestHandler');
            return $handler($req, $res, 'Invalid data');
        }

        $data = [];
        $sources = [];

        for ($i = 0; $i < count($body); $i++) {
            if (array_key_exists('source', $body[$i]) && !empty($body[$i]['source'])) {
                // check available source
                $count = $this->dbCount($this->table, ['source' => $body[$i]['source']]);

                if ($count > 0 || in_array($body[$i]['source'], $sources)) {
                    continue;
                }

                array_push($sources, $body[$i]['source']);
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
            $handler = $this->cont->get('badRequestHandler');
            return $handler($req, $res, 'Invalid data');
        }

        $result = $this->dbInsertMany($this->table, $data, $protected);

        if ($result['total_data'] > 0) {
            $handler = $this->cont->get('successCreatedHandler');
            return $handler($req, $res, $result);
        }

        $handler = $this->cont->get('badRequestHandler');
        return $handler($req, $res, $result['error'] ?: 'Invalid data');
    }

    /**
     *  insert new multiple data update on duplicate
     *  @param {Request} $req, {Response} $res
     *  @return {array} $handler
     */
    public function insertManyUpdate(Request $req, Response $res) {
        $decoded = $req->getAttribute('decoded');
        $body = $req->getParsedBody();
        $protected = [];

        if (!is_array_multi($body)) {
            $handler = $this->cont->get('badRequestHandler');
            return $handler($req, $res, 'Invalid data');
        }

        $master_column = $this->dbColumnDetail($this->table);
        $unique_key = null;

        foreach ($master_column as $key => $val) {
            if (($val['column_key'] === 'PRI' || $val['column_key'] === 'UNI') && !in_array($key, $protected)) {
                $unique = $key;
                break;
            }
        }

        $data = [];
        $sources = [];

        for ($i = 0; $i < count($body); $i++) {
            if (array_key_exists($unique_key, $body[$i])) {
                if (array_key_exists('source', $body[$i]) && !empty($body[$i]['source'])) {
                    // check available source
                    $count = $this->dbCount($this->table, ['source' => $body[$i]['source']], ["{$this->table}.{$unique_key} <> {$body[$i][$unique_key]}"]);

                    if ($count > 0 || in_array($body[$i]['source'], $sources)) {
                        continue;
                    }

                    array_push($sources, $body[$i]['source']);
                }
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
            $handler = $this->cont->get('badRequestHandler');
            return $handler($req, $res, 'Invalid data');
        }

        $result = $this->dbInsertManyUpdate($this->table, $data, $protected);

        if ($result['total_data'] > 0) {
            $handler = $this->cont->get('successCreatedHandler');
            return $handler($req, $res, $result);
        }

        $handler = $this->cont->get('badRequestHandler');
        return $handler($req, $res, $result['error'] ?: 'Invalid data');
    }

}