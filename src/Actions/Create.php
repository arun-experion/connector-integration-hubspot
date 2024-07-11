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
use GuzzleHttp\Client;
use HubSpot\Client\Crm\Schemas\ApiException as SchemasApiException;

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

        // Setting the required properties for standard and custom objects
        switch ($this->recordLocator->recordType) {
            case "companies":
                $requiredProperties = Config::REQUIRED_COMPANIES_PROPERTIES;
                break;
            case "contacts":
                $requiredProperties = Config::REQUIRED_CONTACTS_PROPERTIES;
                break;
            case "deals":
                $requiredProperties = Config::REQUIRED_DEALS_PROPERTIES;
                break;
            case "tickets":
                $requiredProperties = Config::REQUIRED_TICKETS_PROPERTIES;
                break;
            default:
                $requiredProperties = [ $this->recordLocator->recordType => $this->findRequiredProperties($client)];
        }
        
        if($this->recordLocator->recordType == 'companies' || $this->recordLocator->recordType == 'contacts') {
            // For companies and contacts not all fields in $requiredProperties are mandatory 
            $mappingKeys = array_keys($this->mappingAsArray());
            $requiredKeys = $requiredProperties[$this->recordLocator->recordType];
            
            // Checking any of the required keys are there in mapping
            try{
                if(!empty(array_intersect($requiredKeys, $mappingKeys))){
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
                } else {
                    throw new InvalidExecutionPlan("Validation error: Required keys are missing");
                }
            } catch(InvalidExecutionPlan $e){
                throw new InvalidExecutionPlan($e->getMessage());
            }

        } 
        else {
            // For deals, tickets and custom objects all fields in $requiredProperties are mandatory 
            $mappingKeys = array_keys($this->mappingAsArray());
            $requiredKeys = $requiredProperties[$this->recordLocator->recordType];

            // Checking if all the required keys are there in mapping
            try{
                if($requiredKeys === array_intersect($requiredKeys, $mappingKeys)){
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
                } else {
                    throw new InvalidExecutionPlan("Validation error: Required keys are missing");
                }
            } catch(InvalidExecutionPlan $e){
                throw new InvalidExecutionPlan($e->getMessage());
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

    /**
     * Fetches the required properties for a custom CRM object
     *
     * @param Discovery $client The Discovery client used to make API calls.
     * 
     * @return array
     * 
     * @throws InvalidExecutionPlan
     * @throws SchemasApiException If exception arises during calling of core_api->getById()
     */
    public function findRequiredProperties(Discovery $client): array
    {
        // Making an api call to crm/v3/schemas to get required properties for the record
        try {
            $apiResponse = $client->crm()->schemas()->coreApi()->getById($this->recordLocator->recordType);
            $apiResponse = json_decode($apiResponse, true);
            if (!empty($apiResponse) && is_array($apiResponse)) {
                $requiredProperties = $apiResponse['requiredProperties'];
                return $requiredProperties;
            } else {
                throw new InvalidExecutionPlan("No response found for crm/v3/schemas/", $this->recordLocator->recordType);
            }
        } catch (SchemasApiException $e) {
            throw new SchemasApiException("Exception when calling core_api->getById: ", $e->getMessage());
        }
    }
}