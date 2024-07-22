<?php

namespace Tests;

use Connector\Integrations\Hubspot\Config;
use Connector\Integrations\Hubspot\HubspotOrderByClause;
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
        $this->assertGreaterThanOrEqual(4, count($schema->schema['items']), 'The items array should contain at least 4 arrays.');

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
    /**
     * Test the custom objects .
     * 
     * This function verifies that the 'custom' object are present in the schema.
     * Check expected properties contain atleast one required Property.
     */
    function testDiscoverCustomObject()
    {
        $integration = new Integration();
        $schema = $integration->discover()->schema;

        //Decode the expected file for easier comparison.
        $expectedSchema = json_decode(file_get_contents(__DIR__ . "/schemas/DiscoverResult.json"), true);

        //Get the custom objects name from expected results
        $expectedKeys = array_keys($expectedSchema['items']);
        $expectedcustomObject1 = isset($expectedKeys[4]) ? $expectedKeys[4] : null;
        $expectedcustomObject2 = isset($expectedKeys[5]) ? $expectedKeys[5] : null;
        $expectedcustomObject3 = isset($expectedKeys[6]) ? $expectedKeys[6] : null;

        //Get the custom objects name from discover()
        $actualKeys = (array_keys($schema['items']));
        $actualCustomObject1 = isset($actualKeys[4]) ? $actualKeys[4] : null;
        $actualCustomObject2 = isset($actualKeys[5]) ? $actualKeys[5] : null;
        $actualCustomObject3 = isset($actualKeys[6]) ? $actualKeys[6] : null;
        // Assert that the keys match the expected values
        $this->assertEquals($actualCustomObject1, $expectedcustomObject1, "Item  should contain a " . $expectedKeys[4] . " object.");
        $this->assertEquals($actualCustomObject2, $expectedcustomObject2, "Item  should contain a " . $expectedKeys[5] . " object.");
        $this->assertEquals($actualCustomObject3, $expectedcustomObject3, "Item  should contain a " . $expectedKeys[6] . " object.");
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
        $this->assertEquals($recordType, 'companies');

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
        $this->assertTrue($mapping->hasItem('name') || $mapping->hasItem('domain'));
        $response = $integration->load($recordLocator, $mapping, null);
        // Check if recordType is in standard custom objects
        $recordType = $response->getRecordKey()->recordType;
        $this->assertEquals($recordType, 'companies', "Record type should be one of companies");
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
            "phone" => "(555) 555-5555",
            "company" => "HubSpot",
            "website" => "hubspot.com"
        ]);

        //Check the missing required fields
        $this->assertTrue($mapping->hasItem('email') || $mapping->hasItem('firstname') || $mapping->hasItem('lastname'));
        $response = $integration->load($recordLocator, $mapping, null);
        // Check if recordType is in standard custom objects
        $recordType = $response->getRecordKey()->recordType;
        $this->assertEquals($recordType, 'contacts', "Record type should be one of contacts");
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
        $this->assertTrue($mapping->hasItem('dealstage'));
        $this->assertTrue($mapping->hasItem('dealname'));

        $response = $integration->load($recordLocator, $mapping, null);
        // Check if recordType is in standard custom objects
        $recordType = $response->getRecordKey()->recordType;
        $this->assertEquals($recordType, 'deals', "Record type should be one of deals");
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
        $this->assertTrue($mapping->hasItem('subject'));
        $this->assertTrue($mapping->hasItem('hs_pipeline_stage'));

        $response = $integration->load($recordLocator, $mapping, null);
        // Check if recordType is in standard custom objects
        $recordType = $response->getRecordKey()->recordType;
        $this->assertEquals($recordType, 'tickets', "Record type should be one of tickets");
    }

    /**
     * Test the load functionality for Custom Objects.
     */
    function testLoadCustomObjects()
    {
        $integration = new Integration();
        $schema = json_decode(file_get_contents(__DIR__ . "/schemas/DiscoverResult.json"), true);
        $hubspotSchema = $integration->discover()->schema;
        //Decode the excepted custom schema
        $customSchema = json_decode(file_get_contents(__DIR__ . '/mocks/testLoad/0-POST-CustomObjects.json'), true);
        //Get the name of the excepted custom schema
        $customObject = array_keys($customSchema['items'])[0];
        //fetch the required properties of excepted schema
        foreach ($customSchema['items'][$customObject]['properties'] as $propertyKey => $propertyValue) {
            if (isset($propertyValue['required'])) {
                $mappingArray[] = $propertyKey;
            }
        }
        //initialize an array for mapping
        $baseData = [];
        //set the mapping parameters according to the required properties
        foreach ($mappingArray as $field) {
            if (!array_key_exists($field, $baseData)) {
                if ($customSchema['items'][$customObject]['properties'][$field]['type'] === 'number') {
                    // Generate a random number for number type fields
                    $baseData[$field] = rand(0, 9999);
                } else {
                    // Generate a random string for other types
                    $baseData[$field] = uniqid();
                }
            }
        }

        //Get the list of all Objects from actual schema
        foreach ($hubspotSchema['items'] as $key => $value) {
            $listObject[] = $key;
        }

        //Fetch the required properties of actual custom schema
        foreach ($hubspotSchema['items'][$customObject]['properties'] as $propertyKey => $propertyValue) {
            if (isset($propertyValue['required'])) {
                $requiredProperties[] = $propertyKey;
            }
        }
        $integration->setSchema(new IntegrationSchema($schema));
        $integration->begin();
        $recordLocator = new RecordLocator(["recordType" => $customObject]);

        $mapping = new Mapping($baseData);

        $response = $integration->load($recordLocator, $mapping, null);

        $recordType = $response->getRecordKey()->recordType;
        //Check if the recordType in load is present in list of objects
        $this->assertContains($recordType, $listObject);

        // Check that all required properties are present in the mapping
        foreach ($requiredProperties as $requiredProperty) {
            $this->assertTrue($mapping->hasItem($requiredProperty), "Mapping is missing required property: $requiredProperty");
        }
    }

    /**
     * Test the extract functionality of the Integration class.
     *
     * This test case verifies that the Integration class correctly extracts records
     *  It ensures that the response contains
     * the expected number of records and that the records contain the correct data.
     */
    function testExtract()
    {
        $integration = new Integration();
        $schema = json_decode(file_get_contents(__DIR__ . "/schemas/DiscoverResult.json"), true);
        $integration->setSchema(new IntegrationSchema($schema));
        $integration->begin();

        // Define the query condition for extracting records
        $query = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'examplehubspot.com']];
        // Create a new orderBy clause object for ordering the results
        $orderBy = new HubspotOrderByClause();
        // Create a record locator with the record type, query, and orderBy clause
        $recordLocator = new RecordLocator(["recordType" => 'companies', "query" => $query, 'orderBy' => $orderBy]);
        // Define the mapping for the fields to be extracted
        $mapping = new Mapping(["domain" => null, "name" => null]);

        // Call the extract method 
        $response = $integration->extract($recordLocator, $mapping, null);

        // Assert that the number of records in the response is less than or equal to 100
        $this->assertLessThanOrEqual(100, $response->getRecordset()->count());
        // Assert that the record type in the response is "companies"
        $this->assertEquals("companies", $response->getRecordKey()->recordType);
        $this->assertEquals("21936653466", $response->getRecordKey()->recordId);
        $this->assertEquals("examplehubspot.com", $response->getRecordset()->records[0]->data['properties']->domain);
        $this->assertEquals("Example Hubspot", $response->getRecordset()->records[0]->data['properties']->name);
    }
}
