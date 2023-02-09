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

    public function write($name, $payload, $labels = [], $annotations = [])
    {
        //check if secret already exists
        $secret = $this->get($name);
        if ($secret == null) {
            try {
                $secretParam =[
                    'replication' => new Replication([
                        'automatic' => new Automatic()
                    ]),
                ];
                if ($labels) $secretParam['labels'] = $labels;
                if ($annotations) $secretParam['annotations'] = $annotations;

                $parent = $this->client->projectName($this->projectId);
                $secret = $this->client->createSecret(
                    $parent,
                    $name,
                    new Secret($secretParam)
                );
                //$secret->la
                $formattedParent = $this->client->secretName($this->projectId, $name);
                $version = $this->client->addSecretVersion($formattedParent, new SecretPayload([
                    'data' => $payload,
                ]));

                return $secret->getName();
            } catch (\Exception $exception) {
                Log::info($exception);

                return false;
            }
        }
        else {
            $secretName = $this->addVersion($name, $payload);

            return $secretName;
        }
    }

    public function addVersion($secret, $payload) {
        $secretName = $this->client->secretName($this->projectId, $secret);
        $version = $this->client->addSecretVersion($secretName, new SecretPayload([
            'data' => $payload,
        ]));

        return $version->getName();
    }

    public function get($secretName, $version = 'latest') {
        try {
            $formattedName = $this->client->secretVersionName($this->projectId, $secretName, $version);
            $response = $this->client->accessSecretVersion($formattedName);
            $response = $response->getPayload()->getData();
        } catch(\Exception $exception) {
            Log::info($exception);
            return null;
        }

        return response($response);
    }

    public function getAll($filter = '') {
        $parent = $this->client->projectName($this->projectId);
        $secrets = [];
        foreach ($this->client->listSecrets($parent) as $secret) {
            if (str_contains($secret->getName(), $filter)) {
                //$formattedName = $this->client->secretVersionName($this->projectId, $secret->getName(), 1);
                $response = $this->client->accessSecretVersion($secret->getName().'/versions/1');
                $response = $response->getPayload()->getData();
                $secrets[$secret->getName()] = $response;
            }
        }

        return $secrets;
    }

}
