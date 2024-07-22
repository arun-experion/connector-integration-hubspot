<?php

namespace Connector\Integrations\Hubspot\Actions;

use Connector\Exceptions\RecordNotFound;
use Connector\Integrations\Hubspot\Config;
use Connector\Integrations\Hubspot\HubspotRecordLocator;
use Connector\Integrations\Hubspot\HubspotRequestBodyBuilder;
use Connector\Mapping;
use Connector\Operation\Result;
use Connector\Record;
use Connector\Record\RecordKey;
use Connector\Record\Recordset;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use InvalidArgumentException;

class Select
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
     * @throws RecordNotFound
     * @throws InvalidArgumentException
     * @return \Connector\Operation\Result
     */
    public function execute(): Result
    {
        $httpClient = new Client();

        $result = new Result();
        $recordset = new Recordset();

        $selectFields = array_map(function (Mapping\Item $item) {
            // Returning the field names
            return $item->key;
        }, $this->mapping->items);

        $requestBody = HubspotRequestBodyBuilder::toRequestBody($this->recordLocator->query, $selectFields, $this->recordLocator->orderBy);
        
        if ($requestBody) {
            try {
                $response = $httpClient->post(Config::BASE_URL . 'crm/v' . Config::API_VERSION . '/objects/' . $this->recordLocator->recordType . '/search', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . Config::HUBSPOT_ACCESS_TOKEN,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $requestBody
                ]);
                $response = json_decode($response->getBody()); 
                if($response->total === 0){
                    throw new RecordNotFound("No records found for the given query");
                }
            } catch (GuzzleException $exception) {
                throw new InvalidArgumentException($exception->getMessage());
            }
            
            foreach ($response->results as $record) {
                $key = new RecordKey($record->id, $this->recordLocator->recordType );
                $attr = (array) $record;
                $recordset[] = new Record($key, $attr);
            }
        }
        
        return $result
            ->setExtractedRecordSet($recordset)
            ->setLoadedRecordKey($recordset[0]->getKey() ?? "null");
    }
}
