<?php

namespace Connector\Integrations\Hubspot;

use Connector\Schema\Builder;
use Connector\Schema\Builder\RecordType;
use Connector\Schema\IntegrationSchema;
use HubSpot\Client\Crm\Schemas\ApiException as SchemasApiException;
use HubSpot\Client\Crm\Properties\ApiException as PropertiesApiException;
use HubSpot\Factory;

class HubspotSchema extends IntegrationSchema
{
    /**
     *  @var \HubSpot\Discovery\Discovery $client
     */
    private $client;

    public function __construct()
    {
        $this->client = Factory::createWithAccessToken(Config::HUBSPOT_ACCESS_TOKEN);

        // Get name of CRM objects from getObjectSchema() and store the data returned 
        $CRMObjects = $this->getObjectSchema($this->client);

        // Get properties from combineProperties() and store the data returned 
        $combinedObjectProperties = $this->combineProperties($this->client, $CRMObjects);

        // Initialize the schema builder
        $builder = new Builder("http://formassembly.com/integrations/hubspot", "Hubspot");

        // Iterating through each of the standard and custom CRM Objects
        if(!empty($CRMObjects)){
            foreach ($CRMObjects as $object) {
                $recordType = new RecordType($object);
                $recordType->title = $object;
    
                // For setting the properties
                if(!empty($combinedObjectProperties)){
                    foreach ($combinedObjectProperties as $key => $properties) {
                        if ($object == $key) {
                            foreach ($properties as $property) {
                                $property = json_decode($property, true);
                                $recordType->addProperty($property['name'], $property);
                                $recordType->setTags([$object]);
                            }
                        }
                        $builder->addRecordType($recordType);
                    }
                } else {
                    print_r("Empty CRM object properties");
                }  
            }
            $jsonStructure = $builder->toJSon();
    
            // DiscoverResult.json will contain the data in required JSON format
            file_put_contents(__DIR__ . '/../DiscoverResult.json', json_encode(json_decode($jsonStructure, true), JSON_PRETTY_PRINT));

            parent::__construct($builder->toArray());
        } else {
            print_r("No CRM objects Found");
        }
        
    }

    /**
     * Retrieves custom CRM objects from HubSpot.
     *
     * @param \HubSpot\Discovery\Discovery  $client 
     *
     * @return array An array containing all CRM objects.
     *
     * @throws SchemasApiException If there's an error making API calls to retrieve CRM object schemas.
     */
    public function getObjectSchema($client)
    {
        // $standardCRMObjects contains standard objects from HubSpot
        $standardCRMObjects = Config::STANDARD_CRM_OBJECTS;

        // Making an api call to crm/v3/schemas to get all the custom objects 
        try {
            $apiResponse = $client->crm()->schemas()->coreApi()->getAll(false);

            if (!empty($apiResponse['results']) && (isset($apiResponse['results']) && is_array($apiResponse['results']))) {
                foreach ($apiResponse['results'] as $results) {
                    // Finding all the "fullyQualifiedName" inside "results" array from response.
                    $customCRMObjects[] = $results['fully_qualified_name'];
                }

                // $CRMObjects contains standard and custom objects from HubSpot
                $CRMObjects = array_merge($standardCRMObjects, $customCRMObjects);
                return $CRMObjects;
            } else {
                return ["No results found in the response of crm/v3/schemas."];
            }
        } catch (SchemasApiException $e) {
            return ["Exception when calling core_api->get_all: ", $e->getMessage()];
        }
    }

    /**
     * Combines properties schema for both standard and custom CRM objects from HubSpot.
     *
     * @param \HubSpot\Discovery\Discovery $client
     * @param array $CRMObjects
     *
     * @return array An array containing properties schema for all CRM objects.
     *
     * @throws PropertiesApiException If there's an error making API calls to retrieve properties.
     */
    public function combineProperties($client, $CRMObjects)
    {
        $combinedProperties = [];

        foreach ($CRMObjects as $objectType) {
            try {
                // Get the properties for standard and custom objects by a get request to /crm/v3/properties/{fullyQualifiedName} with all the names found
                $apiResponse = $client->crm()->properties()->coreApi()->getAll($objectType);
                if (!empty($apiResponse['results']) && (isset($apiResponse['results']) && is_array($apiResponse['results']))) {
                    foreach ($apiResponse['results'] as $result) {
                        // Storing properties of standard and custom objects
                        $combinedProperties[$objectType][] = $result;
                    }
                } else {
                    return ["No results found in the response of /crm/v3/properties/{fullyQualifiedName}."];
                }
            } catch (PropertiesApiException $e) {
                return ["Exception when calling core_api->get_all: ", $e->getMessage()];
            }
        }
        return $combinedProperties;
    }
}