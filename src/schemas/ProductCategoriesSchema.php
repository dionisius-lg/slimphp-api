<?php

class ProductCategoriesSchema
{
    /**
     * insert method
     * return schema properties
     */
    public static function insert()
    {
        $properties = [
            'name' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 100,
                'required' => true,
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
            'name' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 100,
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
            'name' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 100,
                'required' => true,
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
            'name' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 100,
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