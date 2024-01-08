<?php

class TokenSchema
{
    /**
     * generate method
     * return schema properties
     */
    public static function generate()
    {
        $properties = [
            'username' => [
                'type' => 'string',
                'required' => true,
            ],
            'password' => [
                'type' => 'string',
                'required' => true,
            ],
        ];

        return [
            'type' => 'object',
            'properties' => $properties
        ];
    }

    /**
     * refresh method
     * return schema properties
     */
    public static function refresh()
    {
        $properties = [
            'token' => [
                'type' => 'string',
                'required' => true,
            ],
        ];

        return [
            'type' => 'object',
            'properties' => $properties
        ];
    }
}