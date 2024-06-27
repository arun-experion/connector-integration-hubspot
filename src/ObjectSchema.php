<?php

use HubSpot\Factory;
use HubSpot\Client\Crm\Schemas\Model\ObjectSchemaEgg;
use HubSpot\Client\Crm\Schemas\Model\ObjectTypePropertyCreate;
use HubSpot\Client\Crm\Schemas\ApiException;
// use HubSpot\Client\CrmObjectSchemas\Schemas\Model\ObjectTypePropertyCreate;
// use HubSpot\Client\CrmObjectSchemas\Schemas\ApiException;
// use HubSpot\Client\CrmObjectSchemas\Schemas\Model\ObjectSchemaEgg;

$client = Factory::createWithAccessToken('YOUR_ACCESS_TOKEN');

$objectTypePropertyCreate1 = new ObjectTypePropertyCreate([
    'label' => 'My object property',
    'name' => 'my_object_property'
]);
$labels1 = [
    'plural' => 'My objects',
    'singular' => 'My object'
];
$objectSchemaEgg = new ObjectSchemaEgg([
    'secondary_display_properties' => ['string'],
    'required_properties' => ['my_object_property'],
    'searchable_properties' => ['string'],
    'primary_display_property' => 'my_object_property',
    'name' => 'my_object',
    'description' => 'string',
    'associated_objects' => ['CONTACT'],
    'properties' => [$objectTypePropertyCreate1],
    'labels' => $labels1,
]);
try {
    $apiResponse = $client->crmObjectSchemas()->schemas()->coreApi()->create($objectSchemaEgg);
    var_dump($apiResponse);
} catch (ApiException $e) {
    echo "Exception when calling core_api->create: ", $e->getMessage();
}