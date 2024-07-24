<?php

namespace Connector\Integrations\Hubspot\Actions;

use Connector\Integrations\Hubspot\HubspotRecordLocator;
use Connector\Mapping;
use Connector\Operation\Result;
use Connector\Record\RecordKey;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Connector\Exceptions\AbortedOperationException;

class Update
{
    /**
     * @var array $log
     */
    private array $log = [];

    /**
     * @var HubspotRecordLocator $recordLocator
     */
    private HubspotRecordLocator $recordLocator;

    /**
     * @var Mapping $mapping
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
     * @param Client $httpClient
     * 
     * @throws \Connector\Exceptions\AbortedOperationException
     * 
     * @return \Connector\Operation\Result
     */
    public function execute(Client $httpClient): Result
    {
        $result = new Result();

        try {
            // Providing an PATCH request to crm/v3/objects/{objectType}/{recordId}
            $httpClient->patch(
                $this->recordLocator->recordType . '/' . $this->recordLocator->recordId,
                [
                    "json" => ["properties" => $this->mappingAsArray()],
                ]
            );
        } catch (GuzzleException $exception) {
            throw new AbortedOperationException($exception->getMessage());
        }

        $key = $this->recordLocator->recordId;
        // Logging the result
        $this->log[] = 'Updated ' . $this->recordLocator->recordType . ' ' . $key;

        return $result->setLoadedRecordKey(new RecordKey($key, $this->recordLocator->recordType));
    }

    /**
     * Used to create 'properties' for the request body
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