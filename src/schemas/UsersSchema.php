<?php

class UsersSchema {

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
                "username" => [
                    "type" => "string",
                    "minLength" => 4,
                    "maxLength" => 50,
                    "required" => true
                ],
                "password" => [
                    "type" => "string",
                    "minLength" => 4,
                    "maxLength" => 50
                ],
                "fullname" => [
                    "type" => "string",
                    "minLength" => 4,
                    "maxLength" => 100,
                    "required" => true
                ],
                "email" => [
                    "type" => "string",
                    "minLength" => 4,
                    "maxLength" => 100,
                    "format" => "email"
                ],
                "birth_place" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 50
                ],
                "birth_date" => [
                    "type" => "string",
                    "format" => "date"
                ],
                "phone" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 25
                ],
                "address" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 255
                ],
                "zip_code" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 6
                ],
                "province_id" => [
                    "type" => "integer",
                    "minimum" => 1
                ],
                "city_id" => [
                    "type" => "integer",
                    "minimum" => 1
                ],
                "user_level_id" => [
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
    public static function update() {
        $schema = [
            "type" => "object",
            "properties" => [
                "username" => [
                    "type" => "string",
                    "minLength" => 4,
                    "maxLength" => 50
                ],
                "fullname" => [
                    "type" => "string",
                    "minLength" => 4,
                    "maxLength" => 100
                ],
                "email" => [
                    "type" => "string",
                    "minLength" => 4,
                    "maxLength" => 100,
                    "format" => "email"
                ],
                "birth_place" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 50
                ],
                "birth_date" => [
                    "type" => "string",
                    "format" => "date"
                ],
                "phone" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 25
                ],
                "address" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 255
                ],
                "zip_code" => [
                    "type" => "string",
                    "minLength" => 1,
                    "maxLength" => 6
                ],
                "province_id" => [
                    "type" => "integer",
                    "minimum" => 1
                ],
                "city_id" => [
                    "type" => "integer",
                    "minimum" => 1
                ],
                "user_level_id" => [
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
                    "username" => [
                        "type" => "string",
                        "minLength" => 4,
                        "maxLength" => 50,
                        "required" => true
                    ],
                    "password" => [
                        "type" => "string",
                        "minLength" => 4,
                        "maxLength" => 50
                    ],
                    "fullname" => [
                        "type" => "string",
                        "minLength" => 4,
                        "maxLength" => 100,
                        "required" => true
                    ],
                    "email" => [
                        "type" => "string",
                        "minLength" => 4,
                        "maxLength" => 100,
                        "format" => "email"
                    ],
                    "birth_place" => [
                        "type" => "string",
                        "minLength" => 1,
                        "maxLength" => 50
                    ],
                    "birth_date" => [
                        "type" => "string",
                        "format" => "date"
                    ],
                    "phone" => [
                        "type" => "string",
                        "minLength" => 1,
                        "maxLength" => 25
                    ],
                    "address" => [
                        "type" => "string",
                        "minLength" => 1,
                        "maxLength" => 255
                    ],
                    "zip_code" => [
                        "type" => "string",
                        "minLength" => 1,
                        "maxLength" => 6
                    ],
                    "province_id" => [
                        "type" => "integer",
                        "minimum" => 1
                    ],
                    "city_id" => [
                        "type" => "integer",
                        "minimum" => 1
                    ],
                    "user_level_id" => [
                        "type" => "integer",
                        "minimum" => 1
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
                    "username" => [
                        "type" => "string",
                        "minLength" => 4,
                        "maxLength" => 50
                    ],
                    "fullname" => [
                        "type" => "string",
                        "minLength" => 4,
                        "maxLength" => 100
                    ],
                    "email" => [
                        "type" => "string",
                        "minLength" => 4,
                        "maxLength" => 100,
                        "format" => "email"
                    ],
                    "birth_place" => [
                        "type" => "string",
                        "minLength" => 1,
                        "maxLength" => 50
                    ],
                    "birth_date" => [
                        "type" => "string",
                        "format" => "date"
                    ],
                    "phone" => [
                        "type" => "string",
                        "minLength" => 1,
                        "maxLength" => 25
                    ],
                    "address" => [
                        "type" => "string",
                        "minLength" => 1,
                        "maxLength" => 255
                    ],
                    "zip_code" => [
                        "type" => "string",
                        "minLength" => 1,
                        "maxLength" => 6
                    ],
                    "province_id" => [
                        "type" => "integer",
                        "minimum" => 1
                    ],
                    "city_id" => [
                        "type" => "integer",
                        "minimum" => 1
                    ],
                    "user_level_id" => [
                        "type" => "integer",
                        "minimum" => 1
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