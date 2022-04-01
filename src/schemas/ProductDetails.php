<?php

use App\Validator as AppValidator;

class ProductDetails extends AppValidator
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
                "product_id" => [
                    "type" => "integer",
                    "minLength" => 1,
                    "required" => true
                ],
                "product_code" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 100,
                    "required" => true
                ],
                "buy_price" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 100,
                    "required" => true
                ],
                "sell_price" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 100,
                    "required" => true
                ],
                "create_date" => [
                    "type" => "string",
                    "format" => "datetime"
                ],
                "create_user_id" => [
                    "type" => "integer",
                    "minimum" => 1
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
                "product_id" => [
                    "type" => "integer",
                    "minLength" => 1,
                    "required" => true
                ],
                "product_code" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 100,
                    "required" => true
                ],
                "buy_price" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 100,
                    "required" => true
                ],
                "sell_price" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 100,
                    "required" => true
                ],
                "update_date" => [
                    "type" => "string",
                    "format" => "datetime"
                ],
                "update_user_id" => [
                    "type" => "integer",
                    "minimum" => 1
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