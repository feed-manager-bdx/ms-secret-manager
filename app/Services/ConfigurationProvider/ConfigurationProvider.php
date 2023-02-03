<?php

namespace App\Services\ConfigurationProvider;

class ConfigurationProvider
{
    public static function getConfig()
    {
        return [
            [
                "projectId" => "saaslowprices",
                "protected-by" => [
                    'token' => [
                        'token' => 'teH1IPdu6h2y',
                        'interval' => 100000
                    ]
                ]
            ],
        ];
    }

    public static function getJson() {
        $json = storage_path('/app');
        $json .='/secret.json';
        return $json;
    }
}
