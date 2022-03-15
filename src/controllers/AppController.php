<?php

// use Psr\Http\Message\RequestInterface as Request;
// use Psr\Http\Message\ResponseInterface as Response;

use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use JsonSchema\Constraints\Factory;
use JsonSchema\Constraints\Constraint;

class AppController
{
	protected $ci; //get slim container
	protected $db; //database name
	protected $con; //connection
	protected $table; //table name
	protected $request_time; //request time for response data

	/**
	 *  __construct method
	 *  variable initialization
	 *  @param Slim\Container $ci
	 */
	public function __construct(Slim\Container $ci)
	{
		$this->ci           = $ci;
		$this->db           = $this->ci->get('globalSettings');
		$this->con          = $this->dbconnect();
		$this->request_time = $_SERVER['REQUEST_TIME'];
	}

	/**
	 *  dbconnect method
	 *  database connection using pdo
	 */
    public function dbconnect()
    {
		$con = new PDO("mysql:host=".$this->db['database']['host'].";dbname=".$this->db['database']['dbname'].";charset=utf8", $this->db['database']['username'], $this->db['database']['password']);
		$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $con;
    }

	/**
	 *  checkColumn method
	 *  check table column
	 *  @param string $db, string $table
	 *  @return array $column
	 */
	public function checkColumn($db, $table)
	{
		$schema = $this->dbconnect()->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
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
	public function checkColumnWithType($db, $table)
	{
		$schema = $this->dbconnect()->prepare("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
		$schema->execute([$db, $table]);

		$column = [];
		while ($row = $schema->fetch(PDO::FETCH_ASSOC)) {
			$column[$row['COLUMN_NAME']] = $row['DATA_TYPE'];
			// $column[$row['COLUMN_NAME']] = "'{$row['DATA_TYPE']}'";
		}

		return $column;
	}

	/**
	 *  parentCheck method
	 *  check data from table
	 *  @param string $table, array $condition
	 *  @return bool $count
	 */
	public function parentCheck($table, $clause = [])
	{
		$table_condition = null;

		if (!empty($clause) && is_array($clause)) {
			$condition = [];

			foreach ($clause as $key => $val) {
				if (!empty($val) || $val === '0') {
					$term = $key == "x_id" ? "id <> '".addslashes($val)."'" : $key." = '".addslashes($val)."'";
					array_push($condition, $term);
				}
			}

			if (!empty($condition)) {
				$table_condition = " WHERE ".implode(" AND ", $condition);
			}
		}

		$stmt = $this->con->prepare("SELECT COUNT(*) FROM ".$table.$table_condition);
		$stmt->execute();

    	return $stmt->fetchColumn();
	}

	/**
	 *  parentGetAll method
	 *  get all data from table
	 *  @param string $table, array $clause, array $condition, array $column, array $join
	 *  @return array $result
	 */
	public function parentGetAll($table, $clause = [], $condition = [], $column = [], $column_deselect = [], $join = [])
	{
		$table_column    = "{$table}.*";
		$table_order     = "ORDER BY {$table}.id";
		$table_sort      = null;
		$table_condition = null;
		$table_join      = null;
		$table_limit     = null;

		if (!empty($column)) {
			if (!empty($column_deselect) && is_array($column_deselect)) {
				$column = array_diff($column, $column_deselect);
			}

			$column = array_values($column);

			for ($i = 0; $i < count($column); $i++) {
				if (!strpos($column[$i], ".")) {
					$column[$i] = "{$table}.{$column[$i]}";
				}
			}

			$table_order  = "ORDER BY {$column[0]}";
			$table_column = implode(", ", $column);
		}

		if (!empty($join) && is_array($join)) {
			if (array_key_exists('column', $join)) {
				if (!empty($join['column']) && is_array($join['column'])) {
					$table_column .= ", ".implode(", ", $join['column']);
				}
			}

			if (array_key_exists('table', $join))  {
				if (!empty($join['table']) && is_array($join['table'])) {
					$table_join = implode(" ", $join['table']);
				}
			}
		}

		if (array_key_exists('order', $clause) && !empty($clause['order']) && is_string($clause['order'])) {
			$column_order  = !strpos($clause['order'], '.') ? "{$table}.{$clause['order']}" : $clause['order'];
			$column_temp   = explode(", ", $table_column);
			$invalid_order = true;

			foreach ($column_temp as $column_temp) {
				if (stripos($column_temp, $column_order) !== false) {
					$invalid_order = false;
				}
			}

			if (!$invalid_order) {
				$table_order = "ORDER BY {$column_order}";
			}
		}

		if (array_key_exists('group', $clause) && !empty($clause['group']) && is_string($clause['group'])) {
			$column_group  = !strpos($clause['group'], '.') ? "{$table}.{$clause['group']}" : $clause['group'];
			$column_temp   = explode(", ", $table_column);
			$invalid_group = true;

			foreach ($column_temp as $column_temp) {
				if (stripos($column_temp, $column_group) !== false) {
					$invalid_group = false;
				}
			}

			if (!$invalid_group) {
				$table_group = "GROUP BY {$column_group}";
			}
		}

		if (array_key_exists('custom_group', $clause) && !empty($clause['custom_group']) && is_array($clause['custom_group'])) {
			$custom_group = implode(", ", $clause['custom_group']);
			$custom_group = (isset($table_group) && empty($table_group)) ? "GROUP BY {$custom_group}" : ", {$custom_group}";
			$table_group .= $custom_group;
		}

		if (isset($table_group)) {
			$table_order = "{$table_group} {$table_order}";
		}

		if (array_key_exists('sort', $clause) && !empty($clause['sort']) && is_string($clause['sort'])) {
			if (in_array(strtoupper($clause['sort']), ['ASC', 'DESC'])) {
				$table_sort =  !empty($table_order) ? strtoupper($clause['sort']) : null;
			}
		}

		if (!empty($condition) && is_array($condition)) {
			$table_condition = "WHERE ".implode(" AND ", $condition);
		}

		$query = "SELECT {$table_column} FROM {$table} {$table_join} {$table_condition} {$table_order} {$table_sort}";

		if (array_key_exists('limit', $clause)) {
			if (!empty($clause['limit']) && is_numeric($clause['limit'])) {
				$page   = (array_key_exists('page', $clause) && is_numeric($clause['page'])) ? $clause['page'] : 1;
				$offset = ($clause['limit'] * $page) - $clause['limit'];
				$query .= " LIMIT {$clause['limit']}";

				if (is_numeric($offset) && $offset >= 0) {
					$query .= " OFFSET {$offset}";
				}
			}
		}

		$stmt = $this->con->prepare($query);
		$stmt->execute();
		$count = "SELECT COUNT(*) FROM {$table} {$table_join} {$table_condition}";

		if (!empty($table_order)) {
			$count = "SELECT COUNT(*) FROM (SELECT COUNT(*) FROM {$table} {$table_join} {$table_condition} {$table_order}) AS {$table}";
		}

		$total = $this->con->query($count)->fetchColumn();
		$data  = [];

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$data[] = array_map('utf8_encode', $row);
		}

		if (!empty($clause['limit'])) {
			$page_last = ceil($total/$clause['limit']);
			$page_first = 1;
			$page_current = (int)$clause['page'];
			$page_next = $page_current + 1;
			$page_previous = $page_current - 1;
		}

		$result = [
			'request_time'   => $this->request_time,
			'execution_time' => executionTime($this->request_time),
			'response_code'  => 200,
			'status'         => 'success',
			'total_data'     => (int) $total,
			'data'           => $data
		];

		if (!empty($clause['limit'])) {
			$result['paging'] = [
				'current'  => $page_current,
				'next'     => ($page_next <= $page_last) ? $page_next : $page_current,
				'previous' => ($page_previous > 0) ? $page_previous : 1,
				'first'    => $page_first,
				'last'     => ($page_last > 0) ? $page_last : 1,
			];
		}

		return $result;
	}

	/**
	 *  parentInsert method
	 *  insert data to table
	 *  @param string $table, array $data
	 *  @return bool $inserted
	 */
	public function parentInsert($table, $data = [])
	{
		$table_column = [];
		$time_char    = ['CURRENT_TIMESTAMP()', 'NOW()'];

		if (!empty($data)) {
			foreach ($data as $key => $val) {
				$val = trim($val);
				$col = "{$key} = ";

				if (!empty($val) || $val === '0') {
					$col .= in_array($val, $time_char) ? addslashes($val) : "'".addslashes($val)."'";
				} else {
					$col .= "'NULL'";
				}

				array_push($table_column, $col);
			}
		}

		if (!empty($table_column) && is_array($table_column)) {
			$table_column = implode(", ", $table_column);

			$stmt = $this->con->prepare("INSERT INTO {$table} SET {$table_column}");
			$stmt->execute();

			return $stmt->rowCount();
		}

		return false;
	}

	/**
	 *  parentInsertMany method
	 *  insert multiple data to table
	 *  @param string $table, array $data
	 *  @return bool $inserted
	 */
	public function parentInsertMany($table, $column_type = [], $data = [])
	{
		$time_char = ['CURRENT_TIMESTAMP()', 'NOW()'];
		$values    = null;

		if (empty($column_type) || !is_array($column_type)) {
			return false;
		}

		if (empty($data) || !is_array($data)) {
			return false;
		}

		if (count($data) == count($data, COUNT_RECURSIVE)) {
			return false;
		}

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

		$temp_key = array_keys($data[0]);

		foreach ($data as $temp) {
			$reordered = array_replace(array_flip($temp_key), $temp);
			$temp_data = [];

			foreach ($reordered as $key => $val) {
				$temp_val = trim($temp[$key]);

				if (!empty($temp_val) || $temp_val === '0') {
					$temp_val = in_array($temp_val, $time_char) ? addslashes($temp_val) : "'".addslashes($temp_val)."'";
					$temp_data[$key] = $temp_val;
				}
			}

			if (!empty($temp_data)) {
				$temp_values = [];

				foreach ($column_type as $key => $val) {
					if (array_key_exists($key, $temp_data)) {
						array_push($temp_values, $temp_data[$key]);
					} elseif (in_array($val, ['int', 'tinyint'])) {
						array_push($temp_values, "0");
					} else {
						array_push($temp_values, "NULL");
					}
				}

				$temp_values = "(".implode(", ", $temp_values).")";
				$values      = !empty($values) ? "{$values}, {$temp_values}" : $temp_values;
			}
		}

		if (!empty($values)) {
			$column = implode(", ", array_keys($column_type));

			$stmt = $this->con->prepare("INSERT INTO {$table} ({$column}) VALUES {$values}");
			$stmt->execute();

			return $stmt->rowCount();
		}

		return false;
	}

	/**
	 *  parentInsertDuplicateUpdate method
	 *  insert multiple data to table
	 *  @param string $table, array $data
	 *  @return bool $inserted
	 */
	public function parentInsertDuplicateUpdate($table, $data = [])
	{
		$data_temp = '';

		if (!empty($data)) {
			$total_data = count($data);
			$key = '';
			$total_key = 0;
			$val_on_duplicate = '';

			if ($total_data > 0) {
				$keys = array_keys($data[0]);
				$key = implode(', ', $keys);
				$total_key = count($keys);

				$i = 1;
				foreach($keys as $duplicate_key) {
					$val_on_duplicate .= $duplicate_key.' = VALUES('.$duplicate_key.')';

					if ($i < $total_key) {
						$val_on_duplicate .= ', ';
					}

					$i++;
				}

			}

			$i = 1;
			$val = '';

			foreach ($data as $record) {
				$data_key = array_keys($record);
				$total_key = count($data_key);
				$ordered = array_replace(array_flip($keys), $record);
				$it = 0;
				$val .= ' (';

				foreach ($ordered as $data_key => $data_value) {
					@$value = $this->dbconnect()->quote($record[$data_key]);
					$val .= $value;
					$q = $total_key - $it;

					if ($q > 1) {
						$val .= ',';
					}

					$it++;
				}

				$val .= ')';

				if ($total_data > $i) {
					$val .= ',';
				}

				$i++;
			}
		}

		$null_char = ['\'NULL\'', '\'\''];
		$query_string = "INSERT INTO ".$table." (".$key.") VALUES".$val." ON DUPLICATE KEY UPDATE ".$val_on_duplicate;
		$query_string = str_replace($null_char, 'NULL', $query_string);
print_r($query_string); exit;
		$query = $this->con->prepare($query_string);
		$query->execute();

		$inserted = $query->rowCount();

		return $inserted;
	}

	/**
	 *  parentUpdate method
	 *  update data to table
	 *  @param string $table, array $data, array $condition
	 *  @return bool $updated
	 */
	public function parentUpdate($table, $data = [], $condition = [])
	{
		$table_column    = [];
		$table_condition = [];
		$time_char       = ['CURRENT_TIMESTAMP()', 'NOW()'];

		if (!empty($data) && is_array($data)) {
			foreach ($data as $key => $val) {
				$val  = trim($val);
				$temp = "{$key} = ";

				if (!empty($val) || $val === '0') {
					$temp .= in_array($val, $time_char) ? addslashes($val) : "'".addslashes($val)."'";
				} else {
					$temp .= "'NULL'";
				}

				array_push($table_column, $temp);
			}
		}

		if (!empty($condition) && is_array($condition)) {
			foreach ($condition as $key => $val) {
				$val  = trim($val);
				$temp = "{$key} = ";

				if (!empty($val) || $val === '0') {
					$temp .= in_array($val, $time_char) ? addslashes($val) : "'".addslashes($val)."'";
				}

				array_push($table_condition, $temp);
			}
		}

		if (!empty($table_column) && is_array($table_column) && !empty($table_condition) && is_array($table_condition)) {
			$table_column    = implode(", ", $table_column);
			$table_condition = implode(" AND ", $table_condition);

			$stmt = $this->con->prepare("UPDATE {$table} SET {$table_column} WHERE {$table_condition}");

			return $stmt->execute();
		}

		return false;
	}

	/**
	 *  function parentDelete
	 *  delete data from table
	 *  @param string $table, array $condition
	 *  @return bool $deleted
	 */
	public function parentDelete($table, $condition = [])
	{
		$table_condition = [];
		$time_char       = ['CURRENT_TIMESTAMP()', 'NOW()'];

		if (!empty($condition) && is_array($condition)) {
			foreach ($condition as $key => $val) {
				$val  = trim($val);
				$temp = "{$key} = ";

				if (!empty($val) || $val === '0') {
					$temp .= in_array($val, $time_char) ? addslashes($val) : "'".addslashes($val)."'";
				}

				array_push($table_condition, $temp);
			}
		}

		if (!empty($table_condition) && is_array($table_condition)) {
			$table_condition = implode(" AND ", $table_condition);

			$stmt = $this->con->prepare("DELETE FROM {$table} WHERE {$table_condition}");
			$stmt->execute();

			return $stmt->rowCount();
		}

		return false;
	}

	/**
	 *  function callProcedure
	 *  call procedure
	 *  @param string $name, array $parameter
	 *  @return array $result
	 */
	public function callProcedure($name, $parameter = [])
	{
		$start_date = $parameter[0];
		$end_date   = $parameter[1];
		$sort       = $parameter[2];
		// $limit      = $parameter[3];
		// $page       = $parameter[4];
		// $offset     = ($limit * $page) - $limit;

		$query = "CALL ".$name."(?, ?, ?)";
		$data = $this->con->prepare($query);
		$data->execute([$start_date, $end_date, $sort]);

		$data_temp = [];

		while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
			$data_temp[] = array_map('utf8_encode', $row);
		}

		// $page_last     = ceil(count($data_temp)/$limit);
		// $page_first    = 1;
		// $page_current  = (int)$page;
		// $page_next     = $page_current + 1;
		// $page_previous = $page_current - 1;

		$result = [
			'request_time'   => $this->request_time,
			'execution_time' => executionTime($this->request_time),
			'response_code'  => 200,
			'status'         => 'success',
			'total_data'     => count($data_temp),
			'data'           => $data_temp
		];

		// $result['paging'] = [
		// 	'current'  => $page_current,
		// 	'next'     => ($page_next <= $page_last) ? $page_next : $page_current,
		// 	'previous' => ($page_previous > 0) ? $page_previous : 1,
		// 	'first'    => $page_first,
		// 	'last'     => ($page_last > 0) ? $page_last : 1,
		// ];

		return $result;
	}
}
