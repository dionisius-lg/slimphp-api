<?php

namespace App;

// use JsonSchema\SchemaStorage;
use JsonSchema\Validator as JsonValidator;
// use JsonSchema\Constraints\Factory;
// use JsonSchema\Constraints\Constraint;

class Validator
{
    protected $schema;
    protected $valid;
    protected $errors;

    /**
	 *  __construct method
	 *  variable initialization
	 */
    public function __construct()
    {
        $this->schema = false;
        $this->valid  = false;
        $this->errors = [];
    }

    /**
	 *  set method
	 *  set validation schema
	 *  @param boolean $getSchema, boolean $getMethod
	 *  @return array $schema
	 */
    public function set($getSchema = false, $getMethod = false)
    {
        if ($getSchema && $getMethod) {
            $fileName  = __DIR__ . '/schemas/' . $getSchema . '.php';
            $className = '\\' . $getSchema;

            if (file_exists($fileName)) {
                require_once $fileName;

                if (class_exists($className)) {
                    $className = new $className();

                    if (method_exists($className, $getMethod)) {
                        $this->schema = $className->$getMethod();
                    }
                }
            }
        }

        return $this;
    }

    /**
	 *  validate method
	 *  validate data
	 *  @param array $data
	 *  @return boolean $valid
	 */
    public function validate($data = [])
    {
        $validator = new JsonValidator();
        $schema    = $this->schema;

        if ($schema && !empty($data) && is_array($data)) {
            $schema = !is_object($schema) ? array2object($schema) : $schema;
            $data   = !is_object($data) ? array2object($data) : $data;

            $validator->coerce($data, $schema);

            if (!$validator->isValid()) {
                $this->errors = [];

                foreach ($validator->getErrors() as $error) {
                    switch ($error['message']) {
                        case (strpos($error['message'], "Must be") !== false):
                            $error['message'] = "Invalid {$error['property']}. {$error['message']}";
                            break;
                        case (strpos($error['message'], "Invalid date ") !== false):
                            $error['message'] = str_ireplace("Invalid date \"{$data->$error['property']}\"", "", $error['message']);
                            $error['message'] = "Invalid {$error['property']}{$error['message']}";
                            break;
                        default:
                            $error['message'] = $error['message'];
                            break;
                    }
    
                    array_push($this->errors, $error['message']);
                }
            }

            $this->valid = $validator->isValid();
        }

        return $this->isValid();
    }

    /**
	 *  isValid method
	 *  @return boolean $valid
	 */
    public function isValid()
    {
        return $this->valid;
    }

    /**
	 *  getErrors method
	 *  @return array $errors
	 */
    public function getErrors()
    {
        return $this->errors;
    }
}