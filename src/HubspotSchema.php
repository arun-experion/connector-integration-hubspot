<?php

namespace Connector\Integrations\Hubspot;

use Connector\Exceptions\InvalidExecutionPlan;
use Connector\Schema\Builder;
use Connector\Schema\Builder\RecordProperty;
use Connector\Schema\Builder\RecordType;
use Connector\Schema\IntegrationSchema;
use Connector\Type\JsonSchemaFormats;
use Connector\Type\JsonSchemaTypes;
use HubSpot\Client\Crm\Schemas\ApiException as SchemasApiException;
use HubSpot\Client\Crm\Properties\ApiException as PropertiesApiException;
use HubSpot\Discovery\Discovery;

class HubspotSchema extends IntegrationSchema
{
    /**
     * @param \HubSpot\Discovery\Discovery $client
     * 
     * @throws InvalidExecutionPlan
     */
    public function __construct(Discovery $client)
    {
        // Get name of CRM objects from getObjectSchema() and store the data returned 
        $crmObjects = $this->getObjectSchema($client);

        // Get properties from combineProperties() and store the data returned 
        $combinedObjectProperties = $this->combineProperties($client, $crmObjects);

        // Initialize the schema builder
        $builder = new Builder("http://formassembly.com/integrations/hubspot", "Hubspot");

        // Iterating through each of the standard and custom CRM Objects
        if (!empty($crmObjects)) 
        {
            foreach ($crmObjects as $object) 
            {
                $recordType = new RecordType($object);
                $recordType->title = $object;

                // For setting the properties
                if (!empty($combinedObjectProperties)) 
                {
                    foreach ($combinedObjectProperties[$object] as $property) 
                    {
                        $property = json_decode($property, true);
                        $recordType->addProperty($this->getHubspotObjectFields($property));
                    }
                } else 
                {
                    $exception = new InvalidExecutionPlan();
                    throw new InvalidExecutionPlan($exception->getMessage());
                }
                $builder->addRecordType($recordType);
            }

            parent::__construct($builder->toArray());
        } else {
            $exception = new InvalidExecutionPlan();
            throw new InvalidExecutionPlan($exception->getMessage());
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
     * @throws SchemasApiException If there's an error making API calls to retrieve CRM object schemas.
     */
    public function getObjectSchema(Discovery $client): array
    {
        // $standardCRMObjects contains standard objects from HubSpot
        $standardCRMObjects = Config::STANDARD_CRM_OBJECTS;

        // Making an api call to crm/v3/schemas to get all the custom objects 
        try {
            $apiResponse = $client->crm()->schemas()->coreApi()->getAll(false);

            if (!empty($apiResponse['results']) && is_array($apiResponse['results'])) {
                foreach ($apiResponse['results'] as $results) {
                    // Finding all the "fullyQualifiedName" inside "results" array from response.
                    $customCRMObjects[] = $results['fully_qualified_name'];
                }
                // Sorting the custom CRM objects by fully_qualified_name in ascending order
                sort($customCRMObjects);
                // $crmObjects contains standard and custom objects from HubSpot
                $crmObjects = array_merge($standardCRMObjects, $customCRMObjects);
                return $crmObjects;
            } else {
                $exception = new InvalidExecutionPlan();
                throw new InvalidExecutionPlan($exception->getMessage());
            }
        } catch (SchemasApiException $e) {
            throw new SchemasApiException($e->getMessage());
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
     * @throws PropertiesApiException If there's an error making API calls to retrieve properties.
     */
    public function combineProperties(Discovery $client, array $crmObjects): array
    {
        $combinedProperties = [];

        foreach ($crmObjects as $objectType) {
            try {
                // Get the properties for standard and custom objects by a get request to /crm/v3/properties/{fullyQualifiedName} with all the names found
                $apiResponse = $client->crm()->properties()->coreApi()->getAll($objectType);
                if (!empty($apiResponse['results']) && is_array($apiResponse['results'])) {
                    foreach ($apiResponse['results'] as $result) {
                        // Storing properties of standard and custom objects
                        $combinedProperties[$objectType][] = $result;
                    }
                } else {
                    $exception = new InvalidExecutionPlan();
                    throw new InvalidExecutionPlan($exception->getMessage());
                }
            } catch (PropertiesApiException $e) {
                throw new PropertiesApiException($e->getMessage());
            }
        }
        return $combinedProperties;
    }

    /**
     * Retrieves a RecordProperty object representing HubSpot object fields based on provided property data.
     *
     * @param array $property An array containing property data
     * @return Builder\RecordProperty A RecordProperty object representing HubSpot object fields.
     */
    public function getHubspotObjectFields(array $property): RecordProperty
    {
        $attributes = [
            "name" => $property['name'],
            "title" => $property['label'],
            "type" => $this->getDataTypeFromProperty($property['type']),
            "format" => $this->getFormatFromProperty($property['fieldType'])
        ];

        if ($property['type'] === 'enumeration') {
            foreach ($property['options'] as $options) {
                $attributes['oneOf'][] = [
                    'const' => $options['value'],
                    'title' => $options['label']
                ];
            }
        }

        if($property['modificationMetadata']['readOnlyValue'] === true) {
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
            case 'integer':
                $type = JsonSchemaTypes::Integer;
                break;
            case 'boolean':
                $type = JsonSchemaTypes::Boolean;
                break;
            default:
                $type = JsonSchemaTypes::String;
        }
        return $type;
    }

    /**
     * Determines the JSON schema format based on the provided property field type.
     *
     * @param string $propertyFieldType The field type of the property to determine JSON schema format for.
     * @return JsonSchemaFormats The corresponding JSON schema format.
     */
    public function getFormatFromProperty(string $propertyFieldType): JsonSchemaFormats
    {
        switch ($propertyFieldType) {
            case 'date':
                $format = JsonSchemaFormats::Date;
                break;
            case 'dateTime':
                $format = JsonSchemaFormats::DateTime;
                break;
            case 'time':
                $format = JsonSchemaFormats::Time;
                break;
            default:
                $format = JsonSchemaFormats::None;
        }
        return $format;
    }
}
