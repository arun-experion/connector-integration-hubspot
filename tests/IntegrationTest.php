<?php

namespace Tests;

use Connector\Integrations\Hubspot\Config;
use Connector\Integrations\Hubspot\Integration;
use Connector\Mapping;
use Connector\Record\RecordLocator;
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
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('deals', 'closedate')->type);
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
    /*
     * Test the load functionality of the integration.
     * Verifies the RecordType and checks the format of response
     */
    function testLoadStandardObjects()
    {
        $integration = new Integration();
        $schema = json_decode(file_get_contents(__DIR__ . "/schemas/DiscoverResult.json"), true);
        $integration->setSchema(new IntegrationSchema($schema));
        $integration->begin();

        $recordLocator = new RecordLocator(["recordType" => 'companies']);

        $mapping = new Mapping([
            "name" => "Hubspot",
            "domain" => "hubspot.com",
            "city" => "Cambridge",
            "phone" => "555-555-555",
            'industry' => "ACCOUNTING",
            "state" => "Massachusetts"
        ]);

        $response = $integration->load($recordLocator, $mapping, null);

        // Check if recordId is present
        $recordId = $response->getRecordKey()->recordId;
        $this->assertNotEmpty($recordId, "Record ID should not be empty");

        // Check if recordType is in standard custom objects
        $recordType = $response->getRecordKey()->recordType;
        $this->assertContains($recordType, Config::STANDARD_CRM_OBJECTS, "Record type should be one of " . implode(', ', Config::STANDARD_CRM_OBJECTS));

        // Check if URL is in the correct format and contains the recordId
        $expectedUrlFormat = "https://api.hubapi.com/crm/v3/objects/" . $recordType . "/" . $recordId;
        $actualUrl = $response->getRecordset()->records[0]->data['FormAssemblyConnectorResult:Url'];
        $this->assertEquals($expectedUrlFormat, $actualUrl, "URL format is incorrect");
        // Check if FormAssemblyConnectorResult:Id contains the recordId
        $formAssemblyId = $response->getRecordset()->records[0]->data['FormAssemblyConnectorResult:Id'];
        $this->assertEquals($recordId, $formAssemblyId, "FormAssemblyConnectorResult:Id should contain the recordId");
    }

    /**
     * Test the load functionality for Company.
     * validation of the required fields in the mapping
     */
    function testLoadCompany()
    {
        $integration = new Integration();
        $schema = json_decode(file_get_contents(__DIR__ . "/schemas/DiscoverResult.json"), true);
        $integration->setSchema(new IntegrationSchema($schema));
        $integration->begin();

        $recordLocator = new RecordLocator(["recordType" => 'companies']);

        $mapping = new Mapping([
            'name' => 'Hubspot',
            "city" => "Cambridge",
            "phone" => "555-555-555",
            'industry' => "ACCOUNTING",
            "state" => "Massachusetts"
        ]);
        //Check the missing required fields
        $this->assertTrue($mapping->hasItem(Config::REQUIRED_COMPANIES_PROPERTIES['companies'][0]) || $mapping->hasItem(Config::REQUIRED_COMPANIES_PROPERTIES['companies'][1]));
        $response = $integration->load($recordLocator, $mapping, null);
        // Check if recordType is in standard custom objects
        $recordType = $response->getRecordKey()->recordType;
        $this->assertEquals($recordType, Config::STANDARD_CRM_OBJECTS[1], "Record type should be one of companies");
    }

    /**
     * Test the load functionality for Contacts.
     * validation of the required fields in the mapping
     */
    function testLoadContacts()
    {
        $integration = new Integration();
        $schema = json_decode(file_get_contents(__DIR__ . "/schemas/DiscoverResult.json"), true);
        $integration->setSchema(new IntegrationSchema($schema));
        $integration->begin();

        $recordLocator = new RecordLocator(["recordType" => 'contacts']);
        //Email should be a unique key
        $email = "exampleHubspot" . rand(100, 999) . "@email.com";
        $mapping = new Mapping([
            "email" => $email,
            "phone" =>  "(555) 555-5555",
            "company" =>  "HubSpot",
            "website" =>  "hubspot.com"
        ]);

        //Check the missing required fields
        $this->assertTrue($mapping->hasItem(Config::REQUIRED_CONTACTS_PROPERTIES['contacts'][0]) || $mapping->hasItem(Config::REQUIRED_CONTACTS_PROPERTIES['contacts'][1]) || $mapping->hasItem(Config::REQUIRED_CONTACTS_PROPERTIES['contacts'][2]));
        $response = $integration->load($recordLocator, $mapping, null);
        // Check if recordType is in standard custom objects
        $recordType = $response->getRecordKey()->recordType;
        $this->assertEquals($recordType, Config::STANDARD_CRM_OBJECTS[0], "Record type should be one of contacts");
    }

    /**
     * Test the load functionality for Deals.
     * validation of the required fields in the mapping
     */
    function testLoadDeals()
    {
        $integration = new Integration();
        $schema = json_decode(file_get_contents(__DIR__ . "/schemas/DiscoverResult.json"), true);
        $integration->setSchema(new IntegrationSchema($schema));
        $integration->begin();

        $recordLocator = new RecordLocator(["recordType" => 'deals']);

        $mapping = new Mapping([
            "amount" => "1500.00",
            "closedate" => "2019-12-07T16:50:06.678Z",
            "dealname" => "New deal",
            "pipeline" => "default",
            "dealstage" => "contractsent"
        ]);

        //Check the missing required fields
        $this->assertTrue($mapping->hasItem(Config::REQUIRED_DEALS_PROPERTIES['deals'][0]));
        $this->assertTrue($mapping->hasItem(Config::REQUIRED_DEALS_PROPERTIES['deals'][1]));
        $response = $integration->load($recordLocator, $mapping, null);
        // Check if recordType is in standard custom objects
        $recordType = $response->getRecordKey()->recordType;
        $this->assertEquals($recordType, Config::STANDARD_CRM_OBJECTS[2], "Record type should be one of deals");
    }

    /**
     * Test the load functionality for Tickets.
     * validation of the required fields in the mapping
     */
    function testLoadTickets()
    {
        $integration = new Integration();
        $schema = json_decode(file_get_contents(__DIR__ . "/schemas/DiscoverResult.json"), true);
        $integration->setSchema(new IntegrationSchema($schema));
        $integration->begin();

        $recordLocator = new RecordLocator(["recordType" => 'tickets']);

        $mapping = new Mapping([
            "hs_pipeline" => "0",
            "hs_pipeline_stage" => "1",
            "hs_ticket_priority" => "HIGH",
            "subject" => "troubleshoot report"
        ]);

        //Check the missing required fields
        $this->assertTrue($mapping->hasItem(Config::REQUIRED_TICKETS_PROPERTIES['tickets'][0]));
        $this->assertTrue($mapping->hasItem(Config::REQUIRED_TICKETS_PROPERTIES['tickets'][1]));
        $response = $integration->load($recordLocator, $mapping, null);
        // Check if recordType is in standard custom objects
        $recordType = $response->getRecordKey()->recordType;
        $this->assertEquals($recordType, Config::STANDARD_CRM_OBJECTS[3], "Record type should be one of tickets");
    }
}
