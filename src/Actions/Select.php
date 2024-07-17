<?php

namespace Connector\Integrations\Hubspot\Actions;

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
     * 
     * @return \Connector\Operation\Result
     */
    public function execute(): Result
    {
        $httpClient = new Client();

        $result = new Result();
        $recordset = new Recordset();

        $selectFields = array_map(function (Mapping\Item $item) {
            // $item->value contains the data
            return $item->key;
        }, $this->mapping->items);

        // Mock request body
        // $requestBody = [
        //     "filterGroups" => [
        //         [
        //             "filters" => [
        //                 [
        //                     "propertyName" => "make",
        //                     "operator" => "EQ",
        //                     "value" => "Nissan"
        //                 ],
        //                 [
        //                     "propertyName" => "model",
        //                     "operator" => "EQ",
        //                     "value" => "Frontier"
        //                 ]
        //             ]
        //         ],
        //         [
        //             "filters" => [
        //                 [
        //                     "propertyName" => "year",
        //                     "operator" => "EQ",
        //                     "value" => "2019"
        //                 ]
        //             ]
        //         ]
        //     ],
        //     "properties" => $selectFields,
        //     "limit" => 100
        // ];

        $requestBody = HubspotRequestBodyBuilder::toRequestBody($this->recordLocator->query, $selectFields);
        print_r($requestBody);die;
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
                $totalResults = $response->total;
            } catch (GuzzleException $exception) {
                throw new InvalidArgumentException($exception->getMessage());
            }
            $count = 0;
            foreach ($response->results as $record) {
                $key = new RecordKey($record->id, $this->recordLocator->recordType );
                $attr = (array) $record;
                $recordset[] = new Record($key, $attr);
                
                // Count the number of records
                $count++;
            }
        }

        return $result
            ->setExtractedRecordSet($recordset)
            ->setLoadedRecordKey($recordset->records[0]->getKey() ?? null);
    }
}