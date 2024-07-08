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
        $schema= $integration->discover()->json;
        // Reformat to PRETTY_PRINT for easier comparison when test fails.
        $jsonSchema=json_encode(json_decode($schema,true), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );;
        // Assert that the encoded JSON schema is a valid JSON string
        $this->assertJson($jsonSchema);
        // Compare the JSON schema with the expected schema stored in a file
        $this->assertTrue(file_get_contents(__DIR__ . "DiscoverResult.json") === $jsonSchema, "Schema is different than excepted.");
    }
   
    /**
     * Test the JSON schema returned by the HubspotSchema class.
     * 
     * This function verifies that the JSON schema contains the required keys 
     * and the correct structure, ensuring that the schema meets the expected format.
    */
    function testDiscoverReturnsJsonSchema() {
        $integration = new Integration();
        $schema = $integration->discover();
        $this->assertInstanceOf(IntegrationSchema::class, $schema);
        $this->assertJson($schema->json);
        // Check if the given key exists in the JSON schema
        $this->assertArrayHasKey('$schema',$schema->schema);
        $this->assertArrayHasKey('$id',$schema->schema);
        $this->assertArrayHasKey('title',$schema->schema);
        $this->assertArrayHasKey('type',$schema->schema);
        $this->assertEquals('array',$schema->schema['type']);
        $this->assertArrayHasKey('items',$schema->schema);

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
    function testDiscoverReturnsCompanyDefinition() {
        $integration = new Integration();
        $schema = $integration->discover();

        //Check if  default properties exist in companies and also have expected datatypes
        $this->assertTrue($schema->hasProperty('companies','name'));
        $this->assertEquals('string',$schema->schema['items']['companies']['properties']['name']['type']);

        $this->assertTrue($schema->hasProperty('companies','domain'));
        $this->assertEquals('string',$schema->schema['items']['companies']['properties']['domain']['type']);

    }

    /**
     * Test the contacts definition in the JSON schema.
     * 
     * This function verifies that the 'contacts' object in the schema contains 
     * the expected properties and that these properties have the correct data types.
     */
    function testDiscoverReturnsContactDefinition()
    {
        $integration = new Integration($this->oauthConfig);
        $schema = $integration->discover();

       //Check if the  default properties exist in contacts and also have expected datatypes
       $this->assertTrue($schema->hasProperty('contacts', 'firstname'));
       $this->assertEquals('string',$schema->schema['items']['contacts']['properties']['firstname']['type']);

       $this->assertTrue($schema->hasProperty('contacts', 'lastname'));
       $this->assertEquals('string',$schema->schema['items']['contacts']['properties']['lastname']['type']);

       $this->assertTrue($schema->hasProperty('contacts', 'email'));
       $this->assertEquals('string',$schema->schema['items']['contacts']['properties']['email']['type']);

 }

 /**
 * Test the deals definition in the JSON schema.
 * 
 * This function verifies that the 'deals' object in the schema contains 
 * the expected properties and that these properties have the correct data types.
  */
    function testDiscoverReturnsDealDefinition()
    {
        $integration = new Integration($this->oauthConfig);
        $schema = $integration->discover();

        //Check if the  default properties exist in deals and also have expected datatypes
        $this->assertTrue($schema->hasProperty('deals', 'dealname'));
        $this->assertEquals('string',$schema->schema['items']['deals']['properties']['dealname']['type']);
        
        $this->assertTrue($schema->hasProperty('deals', 'amount'));
        $this->assertEquals('number',$schema->schema['items']['deals']['properties']['amount']['type']);
        
        $this->assertTrue($schema->hasProperty('deals', 'closedate'));
        $this->assertEquals('datetime',$schema->schema['items']['deals']['properties']['closedate']['type']);
        
        $this->assertTrue($schema->hasProperty('deals', 'pipeline'));
        $this->assertEquals('enumeration',$schema->schema['items']['deals']['properties']['pipeline']['type']);
          
        $this->assertTrue($schema->hasProperty('deals', 'dealstage'));
        $this->assertEquals('enumeration',$schema->schema['items']['deals']['properties']['dealstage']['type']);
              
    }
    /**
     * Test the tickets definition in the JSON schema.
     * 
     * This function verifies that the 'tickets' object in the schema contains 
     * the expected properties and that these properties have the correct data types.
     */
function testDiscoverReturnsTicketDefinition() {

    $integration = new Integration($this->oauthConfig);
    $schema = $integration->discover();

    //Check if the  default properties exist in deals and also have expected datatypes
    $this->assertTrue($schema->hasProperty('tickets', 'content'));
    $this->assertEquals('string',$schema->schema['items']['tickets']['properties']['content']['type']);

    $this->assertTrue($schema->hasProperty('tickets', 'hs_pipeline'));
    $this->assertEquals('enumeration',$schema->schema['items']['tickets']['properties']['hs_pipeline']['type']);
    
    $this->assertTrue($schema->hasProperty('tickets', 'hs_pipeline_stage'));
    $this->assertEquals('enumeration',$schema->schema['items']['tickets']['properties']['hs_pipeline_stage']['type']);

    $this->assertTrue($schema->hasProperty('tickets', 'hs_ticket_category'));
    $this->assertEquals('enumeration',$schema->schema['items']['tickets']['properties']['hs_ticket_category']['type']);

    $this->assertTrue($schema->hasProperty('tickets', 'hs_ticket_priority'));
    $this->assertEquals('enumeration',$schema->schema['items']['tickets']['properties']['hs_ticket_priority']['type']);

    $this->assertTrue($schema->hasProperty('tickets', 'subject'));
    $this->assertEquals('string',$schema->schema['items']['tickets']['properties']['subject']['type']);

  
}
}
