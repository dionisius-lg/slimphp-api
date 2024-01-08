<?php

class UsersSchema
{
    /**
     * insert method
     * return schema properties
     */
    public static function insert()
    {
        $properties = [
            'username' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 50,
                'required' => true,
            ],
            'password' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 50,
            ],
            'fullname' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 100,
                'required' => true,
            ],
            'email' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 100,
                'format' => 'email',
            ],
            'birth_place' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 50,
            ],
            'birth_date' => [
                'type' => 'string',
                'format' => 'date',
            ],
            'phone' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 25,
            ],
            'address' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 255,
            ],
            'zip_code' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 6,
            ],
            'province_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'city_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'user_level_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'is_active' => [
                'type' => 'string',
                'enum' => ['0','1'],
            ],
        ];

        return [
            'type' => 'object',
            'properties' => $properties
        ];
    }

    /**
     * update method
     * return schema properties
     */
    public static function update()
    {
        $properties = [
            'username' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 50,
            ],
            'fullname' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 100,
            ],
            'email' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 100,
                'format' => 'email',
            ],
            'birth_place' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 50,
            ],
            'birth_date' => [
                'type' => 'string',
                'format' => 'date',
            ],
            'phone' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 25,
            ],
            'address' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 255,
            ],
            'zip_code' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 6,
            ],
            'province_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'city_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'user_level_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'is_active' => [
                'type' => 'string',
                'enum' => ['0','1'],
            ],
        ];

        return [
            'type' => 'object',
            'properties' => $properties
        ];
    }

    /**
     * insertMany method
     * return schema properties
     */
    public static function insertMany()
    {
        $properties = [
            'username' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 50,
                'required' => true,
            ],
            'password' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 50,
            ],
            'fullname' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 100,
                'required' => true,
            ],
            'email' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 100,
                'format' => 'email',
            ],
            'birth_place' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 50,
            ],
            'birth_date' => [
                'type' => 'string',
                'format' => 'date',
            ],
            'phone' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 25,
            ],
            'address' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 255,
            ],
            'zip_code' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 6,
            ],
            'province_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'city_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'user_level_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'is_active' => [
                'type' => 'string',
                'enum' => ['0','1'],
            ],
        ];

        return [
            'type' => 'array',
            'items' => [
                'properties' => $properties
            ],
        ];
    }

    /**
     * insertManyUpdate method
     * return schema properties
     */
    public static function insertManyUpdate()
    {
        $properties = [
            'id' => [
                'type' => 'integer',
                'minimum' => 1,
                'required' => true,
            ],
            'username' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 50,
            ],
            'fullname' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 100,
            ],
            'email' => [
                'type' => 'string',
                'minLength' => 4,
                'maxLength' => 100,
                'format' => 'email',
            ],
            'birth_place' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 50,
            ],
            'birth_date' => [
                'type' => 'string',
                'format' => 'date',
            ],
            'phone' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 25,
            ],
            'address' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 255,
            ],
            'zip_code' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 6,
            ],
            'province_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'city_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'user_level_id' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
            'is_active' => [
                'type' => 'string',
                'enum' => ['0','1'],
            ],
        ];

        return [
            'type' => 'array',
            'items' => [
                'properties' => $properties
            ],
        ];
    }
}