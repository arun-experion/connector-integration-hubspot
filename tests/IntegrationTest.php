<?php
namespace Tests;

use Connector\Integrations\Hubspot\Config;
use Connector\Integrations\Hubspot\Integration;
use Connector\Schema\IntegrationSchema;
use Connector\Type\JsonSchemaFormats;
use Connector\Type\JsonSchemaTypes;
use Exception;
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
        'access_token' =>Config::HUBSPOT_ACCESS_TOKEN
    ];
}

function testUnauthorizedAccess(){
  $client = Factory::createWithAccessToken($this->oauthConfig['access_token']); 
  try {
    $apiResponse = $client->crm()->companies()->basicApi()->getPage(10, false); 
    $responseData = json_decode($apiResponse, true);
    $responseData = json_decode($apiResponse, true);
    $this->assertIsArray($responseData['results']);
          
} 
catch (Exception $e) {
    if ($e->getCode() === 401) {
        $this->assertEquals(401, $e->getCode(), 'Expected HTTP status code 401 (Unauthorized)');
    } elseif ($e->getCode() === 403) {
        $this->assertEquals(403, $e->getCode(), 'Expected HTTP status code 403 (Forbidden)');
    } else {
        $this->fail('Unexpected exception occurred: ' . $e->getMessage());
    }
}  

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

    function testDiscoverReturnsContactDefinition() {
        $integration = new Integration($this->oauthConfig);
        $integration->setAuthorization(json_encode([
                                                       "accessToken"  => getenv('OAUTH_ACCESS_TOKEN'),
                                                       "refreshToken" => getenv('OAUTH_REFRESH_TOKEN'),
                                                       "expires"      => (int) getenv('OAUTH_EXPIRES')]));

        $schema = $integration->discover();

        // Not an exhaustive list of properties.

        $this->assertTrue($schema->hasProperty('contact','email'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contact','email')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contact','email')->format);

        $this->assertTrue($schema->hasProperty('contact','firstname'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contact','firstname')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contact','firstname')->format);

        $this->assertTrue($schema->hasProperty('contact','lastname'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contact','lastname')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contact','lastname')->format);

        $this->assertTrue($schema->hasProperty('contact','phone'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contact','phone')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contact','phone')->format);

        $this->assertTrue($schema->hasProperty('contact','jobtitle'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('contact','jobtitle')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('contact','jobtitle')->format);
    }

    function testDiscoverReturnsDealDefinition() {
        $integration = new Integration($this->oauthConfig);
        $integration->setAuthorization(json_encode([
                                                       "accessToken"  => getenv('OAUTH_ACCESS_TOKEN'),
                                                       "refreshToken" => getenv('OAUTH_REFRESH_TOKEN'),
                                                       "expires"      => (int) getenv('OAUTH_EXPIRES')]));

        $schema = $integration->discover();

        // Not an exhaustive list of properties.

        $this->assertTrue($schema->hasProperty('deal','hs_acv'));
        $this->assertEquals(JsonSchemaTypes::Number, $schema->getDataType('deal','hs_acv')->type);

        $this->assertTrue($schema->hasProperty('deal','hs_arr'));
        $this->assertEquals(JsonSchemaTypes::Number, $schema->getDataType('deal','hs_arr')->type);

        $this->assertTrue($schema->hasProperty('deal','hs_closed_won_date'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('deal','hs_closed_won_date')->type);
        $this->assertEquals(JsonSchemaFormats::DateTime, $schema->getDataType('deal','hs_closed_won_date')->format);

        $this->assertTrue($schema->hasProperty('deal','dealname'));
        $this->assertEquals(JsonSchemaTypes::String, $schema->getDataType('deal','dealname')->type);
        $this->assertEquals(JsonSchemaFormats::None, $schema->getDataType('deal','dealname')->format);
    }

}
