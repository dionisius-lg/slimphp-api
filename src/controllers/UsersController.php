<?php

use \Psr\Container\ContainerInterface as Container;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Upload\Storage\FileSystem;
use \Upload\File;
use \Upload\Validation\Mimetype;
use \Upload\Validation\Size;

class UsersController extends Controller {

    /**
     *  variable initialization
     *  @param {Container} $cont
     */
    public function __construct(Container $cont)  {
        parent::__construct($cont);
        $this->table = 'users';
        $this->table_provinces = 'provinces';
        $this->table_cities = 'cities';
        $this->table_user_levels = 'user_levels';
        $this->table_user_files = 'user_files';
        $this->table_files = 'files';
    }

    /**
     *  get all data
     *  @param {Request} $req, {Response} $res
     *  @return {array} $handler
     */
    public function getAll(Request $req, Response $res) {
        $conditions = $req->getQueryParams();
        $custom_conditions = $column_select = $column_deselect = $custom_columns = $join = $group_by = $custom_orders = [];

        array_push($column_deselect, 'password');
        array_push($custom_columns, "{$this->table_provinces}.name AS province");
        array_push($custom_columns, "{$this->table_cities}.name AS city");
        array_push($custom_columns, "{$this->table_user_levels}.name AS user_level");
        array_push($join, "LEFT JOIN {$this->table_provinces} ON {$this->table_provinces}.id = {$this->table}.province_id");
        array_push($join, "LEFT JOIN {$this->table_cities} ON {$this->table_cities}.id = {$this->table}.city_id");
        array_push($join, "LEFT JOIN {$this->table_user_levels} ON {$this->table_user_levels}.id = {$this->table}.user_level_id");
        array_push($group_by, "{$this->table}.id");

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

        if (array_key_exists('username', $conditions)) {
            array_push($custom_conditions, "{$this->table}.username LIKE '%{$conditions['username']}%'");
            unset($conditions['username']);
        }

        if (array_key_exists('fullname', $conditions)) {
            array_push($custom_conditions, "{$this->table}.fullname LIKE '%{$conditions['fullname']}%'");
            unset($conditions['fullname']);
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

        array_push($column_deselect, 'password');
        array_push($custom_columns, "{$this->table_provinces}.name AS province");
        array_push($custom_columns, "{$this->table_cities}.name AS city");
        array_push($custom_columns, "{$this->table_user_levels}.name AS user_level");
        array_push($join, "LEFT JOIN {$this->table_provinces} ON {$this->table_provinces}.id = {$this->table}.province_id");
        array_push($join, "LEFT JOIN {$this->table_cities} ON {$this->table_cities}.id = {$this->table}.city_id");
        array_push($join, "LEFT JOIN {$this->table_user_levels} ON {$this->table_user_levels}.id = {$this->table}.user_level_id");

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

        // check available username
        $count = $this->dbCount($this->table, ['username' => $data['username']]);

        if ($count > 0) {
            $handler = $this->cont->get('badRequestHandler');
            return $handler($req, $res, 'Username already exist');
        }

        if (array_key_exists('password', $data)) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
        }

        if (array_key_exists('email', $data)) {
            // check available email
            $count = $this->dbCount($this->table, ['email' => $data['email']]);

            if ($count > 0) {
                $handler = $this->cont->get('badRequestHandler');
                return $handler($req, $res, 'Email already exist');
            }
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

            if (array_key_exists('username', $data)) {
                // check available username
                $count = $this->dbCount($this->table, [], ["username {$data['username']}", "id <> {$conditions['id']}"]);
    
                if ($count > 0) {
                    $handler = $this->cont->get('badRequestHandler');
                    return $handler($req, $res, 'Username already exist');
                }
            }

            if (array_key_exists('email', $data)) {
                // check available email
                $count = $this->dbCount($this->table, [], ["email {$data['email']}", "id <> {$conditions['id']}"]);
    
                if ($count > 0) {
                    $handler = $this->cont->get('badRequestHandler');
                    return $handler($req, $res, 'Email already exist');
                }
            }
        }

        if (array_key_exists('password', $data)) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
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
        $usernames = [];
        $emails = [];

        for ($i = 0; $i < count($body); $i++) {
            if (array_key_exists('username', $body[$i]) && !empty($body[$i]['username'])) {
                // check available username
                $count = $this->dbCount($this->table, ['username' => $body[$i]['username']]);

                if ($count > 0 || in_array($body[$i]['username'], $usernames)) {
                    continue;
                }

                array_push($usernames, $body[$i]['username']);
            }

            if (array_key_exists('email', $body[$i]) && !empty($body[$i]['email'])) {
                // check available email
                $count = $this->dbCount($this->table, ['email' => $body[$i]['email']]);

                if ($count > 0 || in_array($body[$i]['email'], $emails)) {
                    continue;
                }

                array_push($emails, $body[$i]['email']);
            }

            if (array_key_exists('password', $body[$i]) && !empty($body[$i]['password'])) {
                $body[$i]['password'] = password_hash($body[$i]['password'], PASSWORD_BCRYPT, ['cost' => 10]);
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
        $usernames = [];
        $emails = [];

        for ($i = 0; $i < count($body); $i++) {
            if (array_key_exists($unique_key, $body[$i])) {
                if (array_key_exists('username', $body[$i]) && !empty($body[$i]['username'])) {
                    // check available username
                    $count = $this->dbCount($this->table, ['username' => $body[$i]['username']], ["{$this->table}.{$unique_key} <> {$body[$i][$unique_key]}"]);

                    if ($count > 0 || in_array($body[$i]['username'], $usernames)) {
                        continue;
                    }

                    array_push($usernames, $body[$i]['username']);
                }

                if (array_key_exists('email', $body[$i]) && !empty($body[$i]['email'])) {
                    // check available email
                    $count = $this->dbCount($this->table, ['email' => $body[$i]['email']], ["{$this->table}.{$unique_key} <> {$body[$i][$unique_key]}"]);

                    if ($count > 0 || in_array($body[$i]['email'], $emails)) {
                        continue;
                    }

                    array_push($emails, $body[$i]['email']);
                }
            }

            if (array_key_exists('password', $body[$i]) && !empty($body[$i]['password'])) {
                $body[$i]['password'] = password_hash($body[$i]['password'], PASSWORD_BCRYPT, ['cost' => 10]);
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

    /**
     *  insert new file photo
     *  @param {Request} $req, {Response} $res, {array} $args
     *  @return {array} $handler
     */
    public function insertPhoto(Request $req, Response $res, $args) {
        $decoded = $req->getAttribute('decoded');
        $data = $req->getParsedBody();
        $protected = ['id'];
        $user_id = $args['id'];

        $custom_conditions = $column_select = $column_deselect = [];
        $custom_columns = [
            "{$this->table_files}.filename",
            "{$this->table_files}.path",
            "{$this->table_files}.size",
            "{$this->table_files}.mime",
        ];
        $join = [
            "LEFT JOIN {$this->table_files} ON {$this->table_files}.id = {$this->table_user_files}.file_id"
        ];

        // check exist data
        $user_file = $this->dbGetDetail(
            $this->table_user_files,
            ['user_id' => $user_id, 'name' => 'photo'],
            $custom_conditions,
            $column_select,
            $column_deselect,
            $custom_columns,
            $join
        );

        $path = "{$this->conf['dir']['files']}/users/{$user_id}";
        $path = preg_replace('/(\/+)/','/', $path);

        // create directory if not exist
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $storage = new FileSystem($path, true);
        $file = new File('file', $storage);
        $filename = $file->getName();

        if (strlen($filename > 200)) {
            $filename = substr($filename, 0, 200);
        }

        $filename .= uniqid();
        $file->setName($filename);

        // validate file upload
        // mimetype list => http://www.iana.org/assignments/media-types/media-types.xhtml
        $file->addValidations([
            // Ensure file is on mimetype validation
            new Mimetype(['image/jpg', 'image/jpeg', 'image/png']),
            // Ensure file is no larger than size validation (use "B", "K", M", or "G")
            new Size('1M'),
        ]);

        // example data about the file that has been uploaded
        // $file_data = [
        //     'name' => $file->getNameWithExtension(),
        //     'extension' => $file->getExtension(),
        //     'mime' => $file->getMimetype(),
        //     'size' => $file->getSize(),
        //     'md5' => $file->getMd5(),
        //     'dimensions' => $file->getDimensions()
        // ];

        $file_data = [
            'filename' => $file->getNameWithExtension(),
            'path' => $path,
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ];

        try {
            $file->upload();
        } catch (\Exception $e) {
            $handler = $this->cont->get('badRequestHandler');
            return $handler($req, $res, $e->getMessage());
        }

        switch (true) {
            case ($user_file['total_data'] > 0):
                // remove last user file if exist
                if (file_exists("{$user_file['data']['path']}/{$user_file['data']['filename']}")) {
                    unlink("{$user_file['data']['path']}/{$user_file['data']['filename']}");
                }

                $file_result = $this->dbUpdate($this->table_files, $file_data, ['id' =>  $user_file['data']['file_id']]);
                break;
            default:
                $file_result = $this->dbInsert($this->table_files, $file_data, $protected);
                break;
        }

        // remove file that has been uploaded if data insert failed
        if ($file_result['total_data'] == 0) {
            if (file_exists("{$file_data['path']}/{$file_data['filename']}")) {
                unlink("{$file_data['path']}/{$file_data['filename']}");
            }

            $handler = $this->cont->get('badRequestHandler');
            return $handler($req, $res, $file_result['error'] ?: 'Invalid data');
        }

        switch (true) {
            case ($user_file['total_data'] > 0):
                $result = $this->dbUpdate($this->table_user_files, [
                    'file_id' => $file_result['data']['id']
                ], ['id' => $user_file['data']['id']]);
                break;
            default:
                $result = $this->dbInsert($this->table_user_files, [
                    'name' => 'photo',
                    'user_id' => $user_id,
                    'file_id' => $file_result['data']['id'],
                    'created_by' => $decoded['id'] 
                ]);
                break;
        }

        if ($result['total_data'] > 0) {
            $result['data']['file_id'] = $file_result['data']['id'];
            $handler = $this->cont->get('successCreatedHandler');
            return $handler($req, $res, $result);
        }

        $handler = $this->cont->get('badRequestHandler');
        return $handler($req, $res, $result['error'] ?: 'Invalid data');
    }

}