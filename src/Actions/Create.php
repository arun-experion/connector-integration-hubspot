<?php

namespace Connector\Integrations\Hubspot\Actions;

use Connector\Exceptions\InvalidExecutionPlan;
use Connector\Integrations\Hubspot\Config;
use Connector\Integrations\Hubspot\HubspotRecordLocator;
use Connector\Integrations\Hubspot\HubspotSchema;
use Connector\Mapping;
use Connector\Operation\Result;
use Connector\Record\RecordKey;
use GuzzleHttp\Exception\GuzzleException;
use HubSpot\Discovery\Discovery;
use GuzzleHttp\Client;

class Create
{
    /**
     * @var string $log
     */
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

    /**
     * @param \HubSpot\Discovery\Discovery $client
     * 
     * @return \Connector\Operation\Result
     * 
     * @throws InvalidExecutionPlan
     */
    public function execute(Discovery $client): Result
    {
        $httpClient = new Client();
        // Getting the HubspotSchema
        $hubspotSchema = new HubspotSchema($client);
        $hubspotSchemaArray = json_decode(json_encode($hubspotSchema), true);

        // $requiredProperties contains the items from 'required' key inside the HubspotSchema
        $requiredProperties = $hubspotSchemaArray['schema']['items'][$this->recordLocator->recordType]['required'] ?? [];

        if ($this->recordLocator->recordType == 'companies' || $this->recordLocator->recordType == 'contacts') {
            // For companies and contacts not all fields in $requiredProperties are mandatory 
            $mappingKeys = array_keys($this->mappingAsArray());

            // Checking any of the required keys are there in mapping
            if (!empty(array_intersect($requiredProperties, $mappingKeys))) {
                try {
                    $response = $httpClient->post(
                        Config::BASE_URL . 'crm/v' . Config::API_VERSION . '/objects/' . $this->recordLocator->recordType,
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
            } 
            else {
                $exceptionMessage = "Validation error: Required keys are missing: " . json_encode($requiredProperties);
                throw new InvalidExecutionPlan($exceptionMessage);
            }
        } 
        else {
            // For deals, tickets and custom objects all fields in $requiredProperties are mandatory 
            $mappingKeys = array_keys($this->mappingAsArray());

            // Checking if all the required keys are there in mapping
            if ($requiredProperties === array_intersect($requiredProperties, $mappingKeys)) {
                try {
                    $response = $httpClient->post(
                        Config::BASE_URL . 'crm/v' . Config::API_VERSION . '/objects/' . $this->recordLocator->recordType,
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
            } 
            else {
                $exceptionMessage = "Validation error: Required keys are missing: " . json_encode(array_values(array_diff($requiredProperties, $mappingKeys)));
                throw new InvalidExecutionPlan($exceptionMessage);
            }
        }

        // Return the ID of the created record.
        return (new Result())->setLoadedRecordKey(new RecordKey($response->id, $this->recordLocator->recordType));
    }

    /**
     * @return array
     */
    private function mappingAsArray(): array
    {
        $map = [];
        foreach ($this->mapping as $item) {
            $map[$item->key] = $item->value;
        }
        return $map;
    }
}