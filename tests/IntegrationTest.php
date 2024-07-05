<?php

namespace Tests;

use Connector\Integrations\Hubspot\Config;
use Connector\Integrations\Hubspot\Integration;
use Connector\Schema\IntegrationSchema;
use Connector\Type\JsonSchemaFormats;
use Connector\Type\JsonSchemaTypes;
use Exception;
use GuzzleHttp\Psr7\Response;
use HubSpot\Factory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Connector\Integrations\Hubspot\Integration
 */
final class IntegrationTest extends TestCase
{

    public array $oauthConfig = [];

    protected function setUp(): void
    {
        $this->oauthConfig = [
            'access_token' => Config::HUBSPOT_ACCESS_TOKEN
        ];
    }

    function testUnauthorizedAccess()
    {
        $client = Factory::createWithAccessToken($this->oauthConfig['access_token']);
        $apiResponse = $client->crm()->companies()->basicApi()->getPage(10, false);
        $this->assertJson($apiResponse);
        $this->assertArrayHasKey('results', $apiResponse);
    }

    /**
     * Test the discover functionality of the integration.
     */
    function testDiscover()
    {
        $integration = new Integration($this->oauthConfig);
        //$schema= $integration->discover();
        $schema = '
        {
    "$schema": "https://formassembly.com/connector/1.0/schema-integration",
    "$id": "http://formassembly.com/integrations/salesforce",
    "title": "Hubspot",
    "type": "array",
    "items": {
        "Account": {
            "type": "object",
            "properties": {
                "Id": {
                    "name": "Id",
                    "title": "Account ID",
                    "type": "string",
                    "format": "",
                    "maxLength": 18,
                    "readOnly": 1,
                    "pk": 1
    }
                    }
    },
                    "Companies":{
                    "type": "object",
            "properties": {
                "name": {
                    "name": "name",
                    "title": "Company name",
                    "type": "string",
                    "format": "",
                    "maxLength": 18,
                    "readOnly": 1,
                    "pk": 1
    },
                "domain": {
                    "name": "domain",
                    "title": "Hosting domain",
                    "type": "string",
                    "format": "",
                    "maxLength": 18,
                    "readOnly": 1,
                    "pk": 1
    }
    }},
                    "Contacts":{},
                    "Deals":{},
                    "Tickets":{}
                    }}';
        $this->assertJson($schema);
        $this->assertTrue(file_get_contents(__DIR__ . "/testDiscover.json") === $schema, "Schema is different than excepted.");
    }
    function testDiscoverReturnsJsonSchema()
    {
        $integration = new Integration($this->oauthConfig);
        // $schema = $integration->discover();
        $schema ='
        {
    "$schema": "https://formassembly.com/connector/1.0/schema-integration",
    "$id": "http://formassembly.com/integrations/salesforce",
    "title": "Hubspot",
    "type": "array",
    "items": {
        "Account": {
            "type": "object",
            "properties": {
                "Id": {
                    "name": "Id",
                    "title": "Account ID",
                    "type": "string",
                    "format": "",
                    "maxLength": 18,
                    "readOnly": 1,
                    "pk": 1
    }}},
                    "Companies":{
                    "type": "object",
            "properties": {
                "name": {
                    "name": "name",
                    "title": "Company name",
                    "type": "string",
                    "format": "",
                    "maxLength": 18,
                    "readOnly": 1,
                    "pk": 1
    },
                "domain": {
                    "name": "domain",
                    "title": "Hosting domain",
                    "type": "string",
                    "format": "",
                    "maxLength": 18,
                    "readOnly": 1,
                    "pk": 1
    }
    }},
                    "Contacts":{},
                    "Deals":{},
                    "Tickets":{}
                    }}'
        ;
        //$this->assertInstanceOf(IntegrationSchema::class, $schema);
        $this->assertJson($schema);

        $schemaArray = json_decode($schema,true);

        $this->assertArrayHasKey('$schema', $schemaArray);
        $this->assertArrayHasKey('$id', $schemaArray);
        $this->assertEquals('Hubspot', $schemaArray['title']);
        $this->assertArrayHasKey('type', $schemaArray);
        $this->assertEquals('array', $schemaArray['type']);
        $this->assertArrayHasKey('items', $schemaArray);
        // Check that there are all the 4 standarad objects
        $this->assertGreaterThanOrEqual(4, count($schemaArray['items']));
        $this->assertArrayHasKey('Companies', $schemaArray['items']);
        $this->assertArrayHasKey('Contacts', $schemaArray['items']);
        $this->assertArrayHasKey('Deals', $schemaArray['items']);
        $this->assertArrayHasKey('Tickets', $schemaArray['items']);

        // Ensure that "Companies", "Contacts", "Deals", and "Tickets" arrays are not empty
        $this->assertNotEmpty($schemaArray['items']['Companies'], "Companies array is empty.");
        $this->assertNotEmpty($schemaArray['items']['Contacts'], "Contacts array is empty.");
        $this->assertNotEmpty($schemaArray['items']['Deals'], "Deals array is empty.");
        $this->assertNotEmpty($schemaArray['items']['Tickets'], "Tickets array is empty.");


    }
    function testDiscoverReturnsCompanyDefinition()
    {
        $integration = new Integration($this->oauthConfig);
        //$schema = $integration->discover();
        $schema ='
        {
    "$schema": "https://formassembly.com/connector/1.0/schema-integration",
    "$id": "http://formassembly.com/integrations/salesforce",
    "title": "Hubspot",
    "type": "array",
    "items": {
        "Account": {
            "type": "object",
            "properties": {
                "Id": {
                    "name": "Id",
                    "title": "Account ID",
                    "type": "string",
                    "format": "",
                    "maxLength": 18,
                    "readOnly": 1,
                    "pk": 1
    }}},
                    "Companies":{
                    "type": "object",
            "properties": {
                "name": {
                    "name": "name",
                    "title": "Company name",
                    "type": "string",
                    "format": "",
                    "maxLength": 18,
                    "readOnly": 1,
                    "pk": 1
    },
                "domain": {
                    "name": "domain",
                    "title": "Hosting domain",
                    "type": "string",
                    "format": "",
                    "maxLength": 18,
                    "readOnly": 1,
                    "pk": 1
    }
    }},
                    "Contacts":{},
                    "Deals":{},
                    "Tickets":{}
                    }}'
        ;
        $schemaObject = json_decode($schema);
    
        $companyProperties = $schemaObject->items->Companies->properties;
    
        // Assertions for 'name' property
        $this->assertTrue(property_exists($companyProperties, 'name'));
        $this->assertEquals('string', $companyProperties->name->type);
        $this->assertEquals('', $companyProperties->name->format);
    
        // Assertions for 'domain' property
        $this->assertTrue(property_exists($companyProperties, 'domain'));
        $this->assertEquals('string', $companyProperties->domain->type);
        $this->assertEquals('', $companyProperties->domain->format);
    

        // $this->assertTrue($schema->hasProperty('company', 'name'));
        // $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('company', 'name')->type);
        // $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('company', 'name')->format);

        // $this->assertTrue($schema->hasProperty('company', 'domain'));
        // $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('company', 'domain')->type);
        // $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('company', 'domain')->format);

        }

    function testDiscoverReturnsContactDefinition()
    {
        $integration = new Integration($this->oauthConfig);
        $integration->setAuthorization(json_encode([
            "accessToken" => getenv('OAUTH_ACCESS_TOKEN'),
            "refreshToken" => getenv('OAUTH_REFRESH_TOKEN'),
            "expires" => (int) getenv('OAUTH_EXPIRES')
        ]));

        $schema = $integration->discover();

        // Not an exhaustive list of properties.

        $this->assertTrue($schema->hasProperty('contact', 'email'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contact', 'email')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contact', 'email')->format);

        $this->assertTrue($schema->hasProperty('contact', 'firstname'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contact', 'firstname')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contact', 'firstname')->format);

        $this->assertTrue($schema->hasProperty('contact', 'lastname'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contact', 'lastname')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contact', 'lastname')->format);

        $this->assertTrue($schema->hasProperty('contact', 'phone'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contact', 'phone')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contact', 'phone')->format);

        $this->assertTrue($schema->hasProperty('contact', 'jobtitle'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contact', 'jobtitle')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contact', 'jobtitle')->format);
    }

    function testDiscoverReturnsDealDefinition()
    {
        $integration = new Integration($this->oauthConfig);
        $integration->setAuthorization(json_encode([
            "accessToken" => getenv('OAUTH_ACCESS_TOKEN'),
            "refreshToken" => getenv('OAUTH_REFRESH_TOKEN'),
            "expires" => (int) getenv('OAUTH_EXPIRES')
        ]));

        $schema = $integration->discover();

        // Not an exhaustive list of properties.

        $this->assertTrue($schema->hasProperty('deal', 'hs_acv'));
        $this->assertEquals(JsonSchemaTypes::Number, $schema->getDataType('deal', 'hs_acv')->type);

        $this->assertTrue($schema->hasProperty('deal', 'hs_arr'));
        $this->assertEquals(JsonSchemaTypes::Number, $schema->getDataType('deal', 'hs_arr')->type);

        $this->assertTrue($schema->hasProperty('deal', 'hs_closed_won_date'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('deal', 'hs_closed_won_date')->type);
        $this->assertEquals(JsonSchemaFormats::DateTime, $schema->getDataType('deal', 'hs_closed_won_date')->format);

        $this->assertTrue($schema->hasProperty('deal', 'dealname'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('deal', 'dealname')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('deal', 'dealname')->format);
    }
}
