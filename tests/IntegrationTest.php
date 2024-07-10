<?php

namespace Tests;

use Connector\Integrations\Hubspot\Config;
use Connector\Integrations\Hubspot\Integration;
use Connector\Schema\IntegrationSchema;
use Connector\Type\JsonSchemaFormats;
use Connector\Type\JsonSchemaTypes;
use HubSpot\Factory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Connector\Integrations\Hubspot\Integration
 */
final class IntegrationTest extends TestCase
{
    /**
     * @var array Configuration for OAuth, including access token.
     */
    public array $oauthConfig = [];

    /**
     * Set up the test environment by initializing the OAuth configuration.
     */
    protected function setUp(): void
    {
        $this->oauthConfig = [
            'access_token' => Config::HUBSPOT_ACCESS_TOKEN
        ];
    }

    /**
     * Test the unauthorized access to the HubSpot API.
     */
    function testUnauthorizedAccess()
    {
        $client = Factory::createWithAccessToken($this->oauthConfig['access_token']);
        // Attempt to retrieve the first 10 companies using the client
        $apiResponse = $client->crm()->companies()->basicApi()->getPage(10, false);
        // Assert that the response is a valid JSON string
        $this->assertJson($apiResponse);
        $this->assertArrayHasKey('results', $apiResponse);
    }

    /**
     * Test the discover functionality of the integration.
     */
    function testDiscover()
    {
        $integration = new Integration();
        $schema = $integration->discover()->schema;
        // Reformat to PRETTY_PRINT for easier comparison when test fails.
        $jsonSchema = json_decode(json_encode($schema, JSON_PRETTY_PRINT), true);
        //Decode the expected file for easier comparison.
        $expectedSchema = json_decode(file_get_contents(__DIR__ . "/schemas/DiscoverResult.json"), true);
        // Compare the JSON schema with the expected schema stored in a file
        $this->assertTrue($expectedSchema === $jsonSchema, "Schema is different than expected.");
    }

    /**
     * Test the JSON schema returned by the HubspotSchema class.
     * 
     * This function verifies that the JSON schema contains the required keys 
     * and the correct structure, ensuring that the schema meets the expected format.
     */
    function testDiscoverReturnsJsonSchema()
    {
        $integration = new Integration();
        $schema = $integration->discover();
        $this->assertInstanceOf(IntegrationSchema::class, $schema);
        $this->assertJson($schema->json);
        // Check if the given key exists in the JSON schema
        $this->assertArrayHasKey('$schema', $schema->schema);
        $this->assertArrayHasKey('$id', $schema->schema);
        $this->assertArrayHasKey('title', $schema->schema);
        $this->assertArrayHasKey('type', $schema->schema);
        $this->assertEquals('array', $schema->schema['type']);
        $this->assertArrayHasKey('items', $schema->schema);

        // Verify that the 'items' array contains at least 4 elements
        //To Ensure that all standard objects are present in schema
        $this->assertGreaterThanOrEqual(count(Config::STANDARD_CRM_OBJECTS), count($schema->schema['items']), 'The items array should contain at least 4 arrays.');

        // Check if 'items' array contains standard objects key
        $this->assertArrayHasKey('companies', $schema->schema['items'], "Item  should contain a 'companies' key.");
        $this->assertArrayHasKey('contacts', $schema->schema['items'], "Item  should contain a 'contacts' key.");
        $this->assertArrayHasKey('deals', $schema->schema['items'], "Item  should contain a 'deals' key.");
        $this->assertArrayHasKey('tickets', $schema->schema['items'], "Item  should contain a 'tickets' key.");
    }

    /**
     * Test the company definition in the JSON schema.
     * 
     * This function verifies that the 'companies' object in the schema contains 
     * the expected properties and that these properties have the correct data types.
     */
    function testDiscoverReturnsCompanyDefinition()
    {
        $integration = new Integration();
        $schema = $integration->discover();

        //Check if  default properties exist in companies and also have expected datatypes
        $this->assertTrue($schema->hasProperty('companies', 'name'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('companies', 'name')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('companies', 'name')->format);

        $this->assertTrue($schema->hasProperty('companies', 'domain'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('companies', 'domain')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('companies', 'domain')->format);
    }

    /**
     * Test the contacts definition in the JSON schema.
     * 
     * This function verifies that the 'contacts' object in the schema contains 
     * the expected properties and that these properties have the correct data types.
     */
    function testDiscoverReturnsContactDefinition()
    {
        $integration = new Integration();
        $schema = $integration->discover();

        //Check if the  default properties exist in contacts and also have expected datatypes
        $this->assertTrue($schema->hasProperty('contacts', 'firstname'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contacts', 'firstname')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contacts', 'firstname')->format);

        $this->assertTrue($schema->hasProperty('contacts', 'lastname'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contacts', 'lastname')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contacts', 'lastname')->format);

        $this->assertTrue($schema->hasProperty('contacts', 'email'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contacts', 'email')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contacts', 'email')->format);
    }

    /**
     * Test the deals definition in the JSON schema.
     * 
     * This function verifies that the 'deals' object in the schema contains 
     * the expected properties and that these properties have the correct data types.
     */
    function testDiscoverReturnsDealDefinition()
    {
        $integration = new Integration();
        $schema = $integration->discover();

        //Check if the  default properties exist in deals and also have expected datatypes
        $this->assertTrue($schema->hasProperty('deals', 'dealname'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('deals', 'dealname')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('deals', 'dealname')->format);


        $this->assertTrue($schema->hasProperty('deals', 'amount'));
        $this->assertEquals(JsonSchemaTypes::Number, $schema->getDataType('deals', 'amount')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('deals', 'amount')->format);


        $this->assertTrue($schema->hasProperty('deals', 'closedate'));
        $this->assertEquals(JsonSchemaTypes::Number, $schema->getDataType('deals', 'closedate')->type);
        $this->assertEquals(JsonSchemaFormats::Date, $schema->getDataType('deals', 'closedate')->format);


        $this->assertTrue($schema->hasProperty('deals', 'pipeline'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('deals', 'pipeline')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('deals', 'pipeline')->format);

        $this->assertTrue($schema->hasProperty('deals', 'dealstage'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('deals', 'dealstage')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('deals', 'dealstage')->format);
    }
    /**
     * Test the tickets definition in the JSON schema.
     * 
     * This function verifies that the 'tickets' object in the schema contains 
     * the expected properties and that these properties have the correct data types.
     */
    function testDiscoverReturnsTicketDefinition()
    {

        $integration = new Integration();
        $schema = $integration->discover();

        //Check if the  default properties exist in deals and also have expected datatypes
        $this->assertTrue($schema->hasProperty('tickets', 'content'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('tickets', 'content')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('tickets', 'content')->format);


        $this->assertTrue($schema->hasProperty('tickets', 'hs_pipeline'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('tickets', 'hs_pipeline')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('tickets', 'hs_pipeline')->format);

        $this->assertTrue($schema->hasProperty('tickets', 'hs_pipeline_stage'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('tickets', 'hs_pipeline_stage')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('tickets', 'hs_pipeline_stage')->format);

        $this->assertTrue($schema->hasProperty('tickets', 'hs_ticket_category'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('tickets', 'hs_ticket_category')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('tickets', 'hs_ticket_category')->format);

        $this->assertTrue($schema->hasProperty('tickets', 'hs_ticket_priority'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('tickets', 'hs_ticket_priority')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('tickets', 'hs_ticket_priority')->format);

        $this->assertTrue($schema->hasProperty('tickets', 'subject'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('tickets', 'subject')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('tickets', 'subject')->format);
    }
}
