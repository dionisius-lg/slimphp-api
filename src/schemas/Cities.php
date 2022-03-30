<?php

use App\Validator as AppValidator;

class Cities extends AppValidator
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
                "name" => [
                    "type" => "string",
                    "minLength" => 3,
                    "maxLength" => 40,
                    "required" => true
                ],
                "province_id" => [
                    "type" => "integer",
                    "minLength" => 1
                ],
                "is_active" => [
                    "oneOf" => [
                        [
                            "type" => "string",
                            "enum" => ["0", "1"]
                        ],
                        [
                            "type" => "integer",
                            "enum" => [0, 1]
                        ],
                    ],
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
                "name" => [
                    "type" => "string",
                    "minLength" => 3,
                    "maxLength" => 40,
                    "required" => true
                ],
                "is_active" => [
                    "oneOf" => [
                        [
                            "type" => "string",
                            "enum" => ["0", "1"]
                        ],
                        [
                            "type" => "integer",
                            "enum" => [0, 1]
                        ],
                    ],
                ],
            ]
        ];
    }
}