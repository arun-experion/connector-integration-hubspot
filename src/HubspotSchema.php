<?php

namespace Connector\Integrations\Hubspot;

use Connector\Schema\Builder;
use Connector\Schema\IntegrationSchema;
use GuzzleHttp\Client;
use HubSpot\Factory;
use HubSpot\Client\Crm\Schemas\ApiException;
use HubSpot\Client\Crm\Schemas\Model\ObjectSchemaEgg;
use HubSpot\Client\Crm\Schemas\Model\ObjectTypePropertyCreate;
use Connector\Integrations\Hubspot\Config;

class HubspotSchema extends IntegrationSchema
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var bool Prevents use of concurrency when making API calls. Only needed for unit tests to request responses in a predictable order.
     */
    private bool $disableConcurrentRequests = false;

    /**
     * HubspotSchema constructor.
     *
     * @param array $schema
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function __construct(array $schema)
    {
        $builder = new Builder("http://formassembly.com/integrations/salesforce", "Salesforce");

        $hubspotSchema = [];
        $i =0;
        foreach ($schema['items'] as $key => $item) {
            if($i == 1){
                break;
            }
            $properties = $item['properties'];
            $hubSpotProperties = [];
            
            foreach ($properties as $property) {
                $hubSpotProperties[] = [
                    'name' => $property['name'],
                    'label' => $property['title'],
                    'type' => $property['type'] === 'string' ? 'string' : ($property['type'] === 'number' ? 'number' : 'unknown'),
                    'fieldType' => 'text',
                    'description' => $property['title'],
                    'groupName' => 'accountinformation'
                ];
            }

            $hubspotSchema[] = [
                'name' => $key,
                'labels' => $item['title'],
                'properties' => $hubSpotProperties
            ];
            $i++;
        }

        $client = Factory::createWithAccessToken(Config::HUBSPOT_ACCESS_TOKEN);

        // Create and send the schema to HubSpot
        foreach ($hubspotSchema as $schemaItem) {
            $objectTypePropertyCreate = new ObjectTypePropertyCreate([
                'label' => strtolower($schemaItem['labels']),
                'name' => strtolower($schemaItem['name'])
            ]);
            $labels = [
                'plural' => strtolower($schemaItem['labels']),
                'singular' => strtolower($schemaItem['labels'])
            ];

            $objectSchemaEgg = new ObjectSchemaEgg([
                'required_properties' => [$schemaItem['properties'][0]['name']], // Use the first property as required property
                'searchable_properties' => array_column($schemaItem['properties'], 'name'),
                'secondary_display_properties' => array_column($schemaItem['properties'], 'name'),
                'primary_display_property' => strtolower($schemaItem['name']),
                'name' => strtolower($schemaItem['name']),
                'description' => 'string',
                'properties' => [$objectTypePropertyCreate],
                'labels' => $labels,
            ]);

            try {
                $apiResponse = $client->crm()->schemas()->coreApi()->create($objectSchemaEgg);
                var_dump($apiResponse);
            } catch (ApiException $e) {
                echo "Exception when calling core_api->create: ", $e->getMessage();
            }
        }
    }
}
