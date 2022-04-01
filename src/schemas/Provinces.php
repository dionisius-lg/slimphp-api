<?php

use App\Validator as AppValidator;

class Provinces extends AppValidator
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
                    "minLength" => 1,
                    "maxLength" => 100,
                    "required" => true
                ],
                "is_active" => [
                    "oneOf" => [
                        [
                            "type" => "string",
                            "enum" => [0, 1]
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
                    "minLength" => 1,
                    "maxLength" => 100,
                    "required" => true
                ],
                "is_active" => [
                    "oneOf" => [
                        [
                            "type" => "string",
                            "enum" => [0, 1]
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