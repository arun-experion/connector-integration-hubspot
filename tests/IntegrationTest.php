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
        // Assert that the response array contains a 'results' key
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
        $this->assertTrue(file_get_contents(__DIR__ . "/testDiscover.json") === $jsonSchema, "Schema is different than what was expected.");
    }
   
    function testDiscoverReturnsJsonSchema() {
        $integration = new Integration($this->oauthConfig);
        $integration->setAuthorization(json_encode([
                                                    "accessToken"  => getenv('OAUTH_ACCESS_TOKEN'),
                                                    "refreshToken" => getenv('OAUTH_REFRESH_TOKEN'),
                                                    "expires"      => (int) getenv('OAUTH_EXPIRES')]));

        $schema = $integration->discover();
        $this->assertInstanceOf(IntegrationSchema::class, $schema);
        $this->assertJson($schema->json);
        $this->assertArrayHasKey('$schema',$schema->schema);
        $this->assertArrayHasKey('$id',$schema->schema);
        $this->assertArrayHasKey('title',$schema->schema);
        $this->assertArrayHasKey('type',$schema->schema);
        $this->assertEquals('array',$schema->schema['type']);
        $this->assertArrayHasKey('items',$schema->schema);
    }
    function testDiscoverReturnsCompanyDefinition() {
        $integration = new Integration($this->oauthConfig);
        $integration->setAuthorization(json_encode([
                                                    "accessToken"  => getenv('OAUTH_ACCESS_TOKEN'),
                                                    "refreshToken" => getenv('OAUTH_REFRESH_TOKEN'),
                                                    "expires"      => (int) getenv('OAUTH_EXPIRES')]));

        $schema = $integration->discover();

        // Not an exhaustive list of properties.

        $this->assertTrue($schema->hasProperty('company','name'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('company','name')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('company','name')->format);

        $this->assertTrue($schema->hasProperty('company','domain'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('company','domain')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('company','domain')->format);

        $this->assertTrue($schema->hasProperty('company','city'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('company','city')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('company','city')->format);
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
