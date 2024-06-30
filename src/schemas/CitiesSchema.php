<?php

class CitiesSchema {

    /**
     *  schema for json validation
     *  @return {array} schema
     */
    public static function detail() {
        $schema = [
            "type" => "object",
            "properties" => [
                "id" => [
                    "type" => "integer",
                    "minimum" => 1,
                    "required" => true
                ]
            ]
        ];

        return $schema;
    }

    /**
     *  schema for json validation
     *  @return {array} schema
     */
    public static function insert() {
        $schema = [
            "type" => "object",
            "properties" => [
                "name" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 50,
                    "required" => true
                ],
                "province_id" => [
                    "type" => "integer",
                    "minimum" => 1,
                    "required" => true
                ],
                "is_active" => [
                    "type" => "string",
                    "enum" => ["0","1"]
                ]
            ]
        ];

        return $schema;
    }

    /**
     *  schema for json validation
     *  @return {array} schema
     */
    public static function update() {
        $schema = [
            "type" => "object",
            "properties" => [
                "name" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 50
                ],
                "province_id" => [
                    "type" => "integer",
                    "minimum" => 1
                ],
                "is_active" => [
                    "type" => "string",
                    "enum" => ["0","1"]
                ]
            ]
        ];

        return $schema;
    }

    /**
     *  schema for json validation
     *  @return {array} schema
     */
    public static function insertMany() {
        $schema = [
            "type" => "array",
            "items" => [
                "type" => "object",
                "properties" => [
                    "name" => [
                        "type" => "string",
                        "minLength" => 1,
                        "maxLength" => 50,
                        "required" => true
                    ],
                    "province_id" => [
                        "type" => "integer",
                        "minimum" => 1,
                        "required" => true
                    ],
                    "is_active" => [
                        "type" => "string",
                        "enum" => ["0","1"]
                    ]
                ]
            ]
        ];

        return $schema;
    }

    /**
     *  schema for json validation
     *  @return {array} schema
     */
    public static function insertManyUpdate() {
        $schema = [
            "type" => "array",
            "items" => [
                "type" => "object",
                "properties" => [
                    "id" => [
                        "type" => "integer",
                        "minimum" => 1,
                        "required" => true
                    ],
                    "name" => [
                        "type" => "string",
                        "minLength" => 1,
                        "maxLength" => 50,
                        "required" => true
                    ],
                    "province_id" => [
                        "type" => "integer",
                        "minimum" => 1,
                        "required" => true
                    ],
                    "is_active" => [
                        "type" => "string",
                        "enum" => ["0","1"]
                    ]
                ]
            ]
        ];

        return $schema;
    }

}