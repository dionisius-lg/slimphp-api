<?php

class FilesSchema {

    /**
     *  schema for json validation
     *  @return {array} schema
     */
    public static function download() {
        $schema = [
            "type" => "object",
            "properties" => [
                "encrypted" => [
                    "type" => "string",
                    "minLength" => 1,
                    "required" => true
                ]
            ]
        ];

        return $schema;
    }

}