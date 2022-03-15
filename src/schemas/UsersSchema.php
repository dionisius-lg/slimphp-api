<?php

use App\Validator as AppValidator;

class UsersSchema extends AppValidator
{
	public function __construct()
	{
		parent::__construct();
	}

    /**
	 *  create method
	 *  validate create data
	 */
	public function create()
	{
        return [
            "type" => "object",
            "properties" => [
                "username" => [
                    "type" => "string",
                    "minLength" => 4,
                    "maxLength" => 8,
                    "required" => true
                ],
                "password" => [
                    "type" => "string",
                    "minLength" => 4,
                    "maxLength" => 8,
                    // "required" => true
                ],
                // "email" => [
                //     // "oneOf" => [
                //     //     [ "type" => "string", "enum" => ["0", "1"] ],
                //     //     [ "type" => "integer", "enum" => [0, 1] ],
                //     // ],
                //     "type" => "number",
                //     "minimum" => 2,
                //     "required" => true
                // ],
                "email" => [
                    "type" => "string",
                    "minLength" => 0,
                    "maxLength" => 200,
                    "format" => "email",
                    "required" => true
                ],
                "birth_date" => [
                    "type" => "string",
                    "format" => "date"
                ],
                "fullname" => [
                    "type" => "string",
                    "minLength" => 4,
                    "maxLength" => 100
                ],
            ]
        ];
    }

    /**
	 *  update method
	 *  validate update data
	 */
	public function update()
	{
        return [
            "type" => "object",
            "properties" => [
                "username" => [
                    "type" => "string",
                    "minLength" => 4,
                    "maxLength" => 8,
                    "required" => true
                ],
                "email" => [
                    "type" => "string",
                    "minLength" => 0,
                    // "maxLength" => 100,
                    "format" => "email",
                    "required" => true
                ],
                "birth_date" => [
                    "type" => "string",
                    "format" => "date"
                ],
                "fullname" => [
                    "type" => "string",
                    "minLength" => 4,
                    "maxLength" => 100
                ],
            ]
        ];
    }
}