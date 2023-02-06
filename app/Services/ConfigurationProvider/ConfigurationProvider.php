<?php

namespace App\Services\ConfigurationProvider;

class ConfigurationProvider
{
    public static function getConfig()
    {
        return [
            [
                "projectId" => "loginplatform",
                "protected-by" => [
                    'token' => [
                        'token' => 'mpW8vzGpAs',
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
