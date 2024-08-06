<?php

namespace Connector\Integrations\Hubspot;

use Connector\Exceptions\InvalidExecutionPlan;
use Connector\Exceptions\InvalidSchemaException;
use Connector\Schema\Builder;
use Connector\Schema\Builder\RecordProperty;
use Connector\Schema\Builder\RecordType;
use Connector\Schema\IntegrationSchema;
use Connector\Type\JsonSchemaFormats;
use Connector\Type\JsonSchemaTypes;
use HubSpot\Client\Crm\Schemas\ApiException as SchemasApiException;
use HubSpot\Client\Crm\Properties\ApiException as PropertiesApiException;
use HubSpot\Discovery\Discovery;
use HubSpot\Factory;

class HubspotSchema extends IntegrationSchema
{
    /**
     *  @var \HubSpot\Discovery\Discovery $client
     */
    private $client;

    /**
     * @param \HubSpot\Discovery\Discovery $client
     * 
     * @throws InvalidExecutionPlan
     */
    public function __construct()
    {
        // Creating an \HubSpot\Discovery\Discovery to access functions in Hubspot library
        $this->client = Factory::createWithAccessToken(Config::HUBSPOT_ACCESS_TOKEN);
        
        // Get name of CRM objects from getObjectSchema() and store the data returned 
        $crmObjects = $this->getObjectSchema($this->client);

        // Get properties from combineProperties() and store the data returned 
        $combinedObjectProperties = $this->combineProperties($this->client, $crmObjects);

        // Initialize the schema builder
        $builder = new Builder("http://formassembly.com/integrations/hubspot", "Hubspot");

        // Iterating through each of the standard and custom CRM Objects
        if (!empty($crmObjects)) {
            // $crmObjects contains the keys fully_qualified_name which is the object name and required_properties
            foreach ($crmObjects as $object) {
                $recordType = new RecordType($object['fully_qualified_name']);
                $recordType->title = $object['fully_qualified_name'];

                // For setting the properties
                if (!empty($combinedObjectProperties)) {
                    foreach ($combinedObjectProperties[$object['fully_qualified_name']] as $property) {
                        $property = json_decode($property, true);
                        $recordType->addProperty($this->getHubspotObjectFields($property, $object));
                    }
                } 
                $builder->addRecordType($recordType);
            }
            
            parent::__construct($builder->toArray());
        } 
    }

    /**
     * Retrieves custom CRM objects from HubSpot.
     *
     * @param \HubSpot\Discovery\Discovery $client 
     *
     * @return array An array containing all CRM objects.
     *
     * @throws InvalidExecutionPlan
     * @throws InvalidSchemaException If there's an error making API calls to retrieve CRM object schemas.
     */
    public function getObjectSchema(Discovery $client): array
    {
        // $standardCRMObjects contains standard objects from HubSpot
        $standardCRMObjects = [
            ["fully_qualified_name" => "contacts"],
            ["fully_qualified_name" => "companies"],
            ["fully_qualified_name" => "deals"],
            ["fully_qualified_name" => "tickets"]
        ];

        // Making an api call to crm/v3/schemas to get all the custom objects 
        try {
            $apiResponse = $client->crm()->schemas()->coreApi()->getAll(false);

            if (!empty($apiResponse['results']) && is_array($apiResponse['results'])) {
                foreach ($apiResponse['results'] as $key => $results) {
                    // $customCRMObjects will conatin the custom object name and its required properties
                    $customCRMObjects[$key]['fully_qualified_name'] = $results['fully_qualified_name'];
                    $customCRMObjects[$key]["required_properties"] = $results['required_properties'];
                }
                // $crmObjects contains standard and custom objects along with its required properties from HubSpot
                $crmObjects = array_merge($standardCRMObjects, $customCRMObjects);
                return $crmObjects;
            } else {
                throw new InvalidExecutionPlan("Empty results array");
            }
        } catch (SchemasApiException $e) {
            throw new InvalidSchemaException($e->getMessage());
        }
    }

    /**
     * Combines properties schema for both standard and custom CRM objects from HubSpot.
     *
     * @param \HubSpot\Discovery\Discovery $client
     * @param array $crmObjects
     *
     * @return array An array containing properties schema for all CRM objects.
     *
     * @throws InvalidExecutionPlan
     * @throws InvalidSchemaException If there's an error making API calls to retrieve properties.
     */
    public function combineProperties(Discovery $client, array $crmObjects): array
    {
        $combinedProperties = [];
        foreach ($crmObjects as $objectType) {
            try {
                // Get the properties for standard and custom objects by a get request to /crm/v3/properties/{fullyQualifiedName} with all the names found
                $apiResponse = $client->crm()->properties()->coreApi()->getAll($objectType['fully_qualified_name']);
                if (!empty($apiResponse['results']) && is_array($apiResponse['results'])) {
                    foreach ($apiResponse['results'] as $result) {
                        // Storing properties of standard and custom objects
                        $combinedProperties[$objectType['fully_qualified_name']][] = $result;
                    }
                } else {
                    throw new InvalidExecutionPlan("Empty results array");
                }
            } catch (PropertiesApiException $e) {
                throw new InvalidSchemaException($e->getMessage());
            }
        }
        return $combinedProperties;
    }

    /**
     * Retrieves a RecordProperty object representing HubSpot object fields based on provided property data.
     *
     * @param array $property An array containing property data
     * @param array $object An array containing object and its required properties
     * @return Builder\RecordProperty A RecordProperty object representing HubSpot object fields.
     */
    public function getHubspotObjectFields(array $property, array $object): RecordProperty
    {
        $attributes = [
            "name" => $property['name'],
            "title" => $property['label'],
            "type" => $this->getDataTypeFromProperty($property['type']),
            "format" => $this->getFormatFromProperty($property['type'])
        ];

        // If the object contains a required_properties key and if the field is a required key
        if(array_key_exists('required_properties', $object) && in_array($property['name'], $object['required_properties'])){
            // Providing a required key into the property
            $attributes = array_merge($attributes, ["required" => true]);
        }
        
        if ($property['type'] === 'enumeration') {
            foreach ($property['options'] as $options) {
                $attributes['oneOf'][] = [
                    'const' => $options['value'],
                    'title' => $options['label']
                ];
            }
        }

        if ($property['modificationMetadata']['readOnlyValue'] === true) {
            $attributes['readOnly'] = 1;
        }

        return new Builder\RecordProperty($property['name'], $attributes);
    }

    /**
     * Determines the JSON schema type based on the provided property type.
     *
     * @param string $propertyType The type of the property to determine JSON schema type for.
     * @return JsonSchemaTypes The corresponding JSON schema type.
     */
    public function getDataTypeFromProperty(string $propertyType): JsonSchemaTypes
    {
        switch ($propertyType) {
            case 'number':
                $type = JsonSchemaTypes::Number;
                break;
            case 'bool':
                $type = JsonSchemaTypes::Boolean;
                break;
            default:
                $type = JsonSchemaTypes::String;
        }
        return $type;
    }

    /**
     * Determines the JSON schema format based on the provided property type.
     *
     * @param string $propertyType The type of the property to determine JSON schema format for.
     * @return JsonSchemaFormats The corresponding JSON schema format.
     */
    public function getFormatFromProperty(string $propertyType): JsonSchemaFormats
    {
        switch ($propertyType) {
            case 'date':
                $format = JsonSchemaFormats::Date;
                break;
            case 'datetime':
                $format = JsonSchemaFormats::DateTime;
                break;
            default:
                $format = JsonSchemaFormats::None;
        }
        return $format;
    }
}