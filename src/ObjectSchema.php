<?php
require __DIR__ . '/../vendor/autoload.php';


use HubSpot\Client\Crm\Schemas\ApiException;
use HubSpot\Client\Crm\Schemas\Model\ObjectSchemaEgg;
use HubSpot\Client\Crm\Schemas\Model\ObjectTypePropertyCreate;
use HubSpot\Factory;

// use HubSpot\Client\CrmObjectSchemas\Schemas\Model\ObjectTypePropertyCreate;
// use HubSpot\Client\CrmObjectSchemas\Schemas\ApiException;
// use HubSpot\Client\CrmObjectSchemas\Schemas\Model\ObjectSchemaEgg;

$access_token = 'pat-na1-1d681633-88b2-4bc5-a23f-8205986b4736';
$client = Factory::createWithAccessToken($access_token);

$objectTypePropertyCreate1 = new ObjectTypePropertyCreate([
    "name" => "date_received",
    "label" => "Date received",
]);

$labels1 = [
    'plural' => 'CarSpot',
    'singular' => 'CarSpot'
];
$objectSchemaEgg = new ObjectSchemaEgg([
    'required_properties' => ["date_received"],
    'searchable_properties' => ["date_received"],
    'primary_display_property' => 'date_received',
    'name' => 'CarSpot',
    'description' => 'Created a schema from code. Cars keeps track of cars currently or previously held in our inventory.',
    'properties' => [$objectTypePropertyCreate1],
    'labels' => $labels1,
]);
try {
    // $apiResponse = $client->crmObjectSchemas()->schemas()->coreApi()->create($objectSchemaEgg);
    $apiResponse = $client->crm()->schemas()->coreApi()->create($objectSchemaEgg);
    var_dump($apiResponse);
} catch (Exception $e) {
    echo "Exception when calling core_api->create: ", $e->getMessage();
}