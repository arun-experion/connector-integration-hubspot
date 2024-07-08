<?php

namespace Connector\Integrations\Hubspot;

use Connector\Schema\Builder;
use Connector\Schema\Builder\RecordType;
use Connector\Schema\IntegrationSchema;
use HubSpot\Client\Crm\Schemas\ApiException;
use HubSpot\Factory;

class HubspotSchema extends IntegrationSchema
{
    /**
     * @var string Holds the generated HubSpot schema
     */
    private $HubSpotSchema;

    public function __construct()
    {
        $this->HubSpotSchema = $this->buildJson();
    }

    /**
     * Builds a JSON structure based on CRM object schema and properties.
     *
     * @return array An associative array in the required JSON format
     */
    public function buildJson(): array
    {
        $client = Factory::createWithAccessToken(Config::HUBSPOT_ACCESS_TOKEN);

        // Get name of CRM objects from getObjectSchema() and store the data returned 
        $CRMObjects = $this->getObjectSchema($client);

        // Get properties from combineProperties() and store the data returned 
        $combinedObjectProperties = $this->combineProperties($client, $CRMObjects);

        // Initialize the schema builder
        $builder = new Builder("http://formassembly.com/integrations/hubspot", "Hubspot");

        // Iterating through each of the standard and custom CRM Objects
        foreach ($CRMObjects as $object) {
            $recordType = new RecordType($object);
            $recordType->title = $object;

            // For setting the properties
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
        }
        $jsonStructure = $builder->toJSon();

        // Decode jsonStructure to an array
        $arrayJson = json_decode($jsonStructure, true);
        // DiscoverResult.json will contain the data in required JSON format
        file_put_contents(__DIR__ . '/../DiscoverResult.json', json_encode($arrayJson, JSON_PRETTY_PRINT));

        return $arrayJson;
    }

    /**
     * Retrieves custom CRM objects from HubSpot.
     *
     * @param \HubSpot\Discovery\Discovery  $client 
     *
     * @return array An array containing all CRM objects.
     *
     * @throws ApiException If there's an error making API calls to retrieve CRM object schemas.
     */
    public function getObjectSchema($client)
    {
        // $standardCRMObjects contains standard objects from HubSpot
        $standardCRMObjects = Config::STANDARD_CRM_OBJECTS;

        // Making an api call to crm/v3/schemas to get all the custom objects 
        try {
            $apiResponse = $client->crm()->schemas()->coreApi()->getAll(false);

            if (isset($apiResponse['results']) && is_array($apiResponse['results'])) {
                foreach ($apiResponse['results'] as $results) {
                    // Finding all the "name" inside "results" array from response.
                    $customCRMObjects[] = $results['name'];
                }

                // $CRMObjects contains standard and custom objects from HubSpot
                $CRMObjects = array_merge($standardCRMObjects, $customCRMObjects);
                return $CRMObjects;
            } else {
                return ["No results found in the response of crm/v3/schemas."];
            }
        } catch (ApiException $e) {
            return ["Exception when calling core_api->get_all: ", $e->getMessage()];
        }
    }

    /**
     * Combines properties schema for both standard and custom CRM objects from HubSpot.
     *
     * @param \\HubSpot\Discovery\Discovery $client
     * @param array $CRMObjects
     *
     * @return array An array containing properties schema for all CRM objects.
     *
     * @throws ApiException If there's an error making API calls to retrieve properties.
     */
    public function combineProperties($client, $CRMObjects)
    {
        $combinedProperties = [];

        foreach ($CRMObjects as $objectType) {
            try {
                // Get the properties for standard and custom objects by a get request to /crm/v3/properties/{name} with all the names found
                $apiResponse = $client->crm()->properties()->coreApi()->getAll($objectType);
                if (isset($apiResponse['results']) && is_array($apiResponse['results'])) {
                    foreach ($apiResponse['results'] as $result) {
                        // Storing properties of standard and custom objects
                        $combinedProperties[$objectType][] = $result;
                    }
                } else {
                    return ["No results found in the response of /crm/v3/properties/{name}."];
                }
            } catch (ApiException $e) {
                return ["Exception when calling core_api->get_all: ", $e->getMessage()];
            }
        }
        return $combinedProperties;
    }
}