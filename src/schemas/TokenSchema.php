<?php

class TokenSchema {

    /**
     *  schema for json validation
     *  @return {array} schema
     */
    public static function auth() {
        $schema = [
            "type" => "object",
            "properties" => [
                "username" => [
                    "type" => "string",
                    "minLength" => 1,
                    "required" => true
                ],
                "password" => [
                    "type" => "string",
                    "minLength" => 1,
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
    public static function refreshAuth() {
        $schema = [
            "type" => "object",
            "properties" => [
                "token" => [
                    "type" => "string",
                    "minLength" => 1,
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
    public static function refreshAuth2() {
        $schema = [
            "type" => "array",
            "items" => [
                "type" => "object",
                "properties" => [
                    "token" => [
                        "type" => "string",
                        "minLength" => "1",
                        "required" => true
                    ]
                ]
            ]
        ];

        return $schema;
    }

}