<?php

namespace App\Helpers;

use Google\Cloud\SecretManager\V1\Replication;
use Google\Cloud\SecretManager\V1\Replication\Automatic;
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\SecretPayload;
use Google\Protobuf\FieldMask;
use Illuminate\Support\Facades\Log;
use \App\Models\GoogleSecret;
use Mockery\Exception;

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

    public function editLabels($secret, $labels) {
        $secretName = $this->client->secretName($this->projectId, $secret);
        if ($labels) {
            $newLabels = [];
            foreach ($labels as $key=>$label) {
                $newLabels[$key] = is_array($label) ? implode('-', $label) : $label;
            }
            try {

                $secret = (new Secret())
                    ->setName($secretName)
                    ->setLabels($newLabels);
                $updateMask = (new FieldMask())
                    ->setPaths(['labels']);
                $response = $this->client->updateSecret($secret, $updateMask);

                return $response->getLabels()['merchant-ids'];
            } catch (\Exception $exception) {
                Log::info($exception);
            }
        }
    }

    public function addVersion($secret, $payload, $labels = []) {
        $secretName = $this->client->secretName($this->projectId, $secret);
        if ($labels) {
            try {
                $edit = $this->client->getSecret($secretName);
                $edit->setLabels($labels);
            } catch (\Exception $exception) {

            }
        }
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

    public function getAll($filter = [], $fullName = false) {
        $parent = $this->client->projectName($this->projectId);
        $secrets = [];
        foreach ($this->client->listSecrets($parent, $filter) as $secret) {
            $response = $this->client->accessSecretVersion($secret->getName().'/versions/latest');

            $labels = [];
            foreach($secret->getLabels()->getIterator() as $key => $label) {
                $labels[$key] = $label;
            }


            $response = $response->getPayload()->getData();
            $secretName = $secret->getName();
            if (!$fullName) $secretName = explode('/', $secretName)[3];
            $secrets[] = ['secret'=>$secretName, 'payload' =>$response, 'labels' => $labels];
        }

        return $secrets;
    }

}
