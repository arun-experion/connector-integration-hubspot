<?php

namespace Connector\Integrations\Hubspot\Actions;

use Connector\Exceptions\InvalidExecutionPlan;
use Connector\Integrations\Hubspot\Config;
use Connector\Integrations\Hubspot\HubspotRecordLocator;
use Connector\Mapping;
use Connector\Operation\Result;
use Connector\Record\RecordKey;
use GuzzleHttp\Exception\GuzzleException;
use HubSpot\Discovery\Discovery;
use Exception;
use GuzzleHttp\Client;

class Create
{
    private string $log;

    /**
     * @var \Connector\Integrations\Hubspot\HubspotRecordLocator
     */
    private HubspotRecordLocator $recordLocator;
    /**
     * @var \Connector\Mapping
     */
    private Mapping $mapping;
    /**
     * @var \Connector\Record\RecordKey|null
     */
    private ?RecordKey $scope;

    public function __construct(HubspotRecordLocator $recordLocator, Mapping $mapping, ?RecordKey $scope)
    {
        $this->recordLocator = $recordLocator;
        $this->mapping = $mapping;
        $this->scope = $scope;
    }

    public function isBatchable(): bool
    {
        return true;
    }

    /**
     * @param \HubSpot\Discovery\Discovery $client
     * 
     * @throws Exception
     */
    public function execute(Discovery $client)
    {
        $httpClient = new Client();
        try {
            $response = $httpClient->post( Config::BASE_URL . 'crm/v' . Config::API_VERSION . '/objects/' . $this->recordLocator->recordType,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . Config::HUBSPOT_ACCESS_TOKEN,
                        'Content-Type' => 'application/json',
                    ],
                    "json" => ["properties" => $this->mappingAsArray()],
                ]
            );

            $response = json_decode($response->getBody());
        } catch (GuzzleException $exception) {
            throw new InvalidExecutionPlan($exception->getMessage());
        }

        // Return the ID of the created record.
        return (new Result())->setLoadedRecordKey(new RecordKey($response->id, $this->recordLocator->recordType));
    }

    private function mappingAsArray(): array
    {
        $map = [];
        foreach ($this->mapping as $item) {
            $map[$item->key] = $item->value;
        }
        return $map;
    }
}