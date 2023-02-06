<?php

namespace App\Helpers;

use Google\Cloud\SecretManager\V1\Replication;
use Google\Cloud\SecretManager\V1\Replication\Automatic;
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\SecretPayload;
use Illuminate\Support\Facades\Log;
use \App\Models\GoogleSecret;

class GoogleSecretManagerHelper
{
    public $client;
    public $projectId;

    public function __construct()
    {
        putenv("GOOGLE_APPLICATION_CREDENTIALS=" . storage_path('app/secret.json'));
        $this->client = new SecretManagerServiceClient();
        $this->projectId = 'snapfeat-logi';
    }

    public function write($name, $email, $refreshToken)
    {
        Log::info($name);
        try {
            $parent = $this->client->projectName($this->projectId);
            $secret = $this->client->createSecret(
                $parent,
                $name,
                new Secret([
                    'replication' => new Replication([
                        'automatic' => new Automatic()
                    ])
                ])
            );

            $formattedParent = $this->client->secretName($this->projectId, $name);

            $version = $this->client->addSecretVersion($formattedParent, new SecretPayload([
                'data' => $refreshToken,
            ]));

            Log::info($version->getName());
            return $version->getName();
        } catch (\Exception $exception) {
            Log::info($exception);

            return false;
        }
    }

    public function get($secretName) {
        $formattedName = $this->client->secretVersionName($this->projectId, $secretName, 1);
        $response = $this->client->accessSecretVersion($formattedName);
        $response = $response->getPayload()->getData();

        return response($response);
    }

}
