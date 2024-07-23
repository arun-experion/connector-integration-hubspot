<?php

namespace Connector\Integrations\Hubspot\Actions;

use Connector\Exceptions\AbortedOperationException;
use Connector\Exceptions\InvalidMappingException;
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
     * @var array $log
     */
    private array $log = [];

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
     * @throws InvalidMappingException
     * @throws \Connector\Exceptions\AbortedOperationException
     */
    public function execute(Discovery $client): Result
    {
        $httpClient = new Client();
        // Getting the HubspotSchema
        $hubspotSchema = new HubspotSchema($client);
        $hubspotSchemaArray = json_decode(json_encode($hubspotSchema), true);

        if (!in_array($this->recordLocator->recordType, ['contacts', 'companies', 'deals', 'tickets'])) {
            foreach ($hubspotSchemaArray['schema']['items'][$this->recordLocator->recordType]['properties'] as $property) {
                // $requiredProperties contain the items from 'required' key inside the properties of fields
                if (array_key_exists('required', $property)) {
                    $requiredProperties[] = $property['name'];
                }
            }
        } else {
            $requiredProperties = [];
        }

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
                throw new AbortedOperationException($exception->getMessage());
            }
        } else {
            $exceptionMessage = "Validation error: Required keys are missing: " . json_encode(array_values(array_diff($requiredProperties, $mappingKeys)));
            throw new InvalidMappingException($exceptionMessage);
        }

        $this->log[] = 'Created ' . $this->recordLocator->recordType . ' ' . $this->recordLocator->recordId;
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

    /**
     * @return array
     */
    public function getLog(): array
    {
        return $this->log;
    }
}