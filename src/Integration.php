<?php

namespace Connector\Integrations\Hubspot;
require __DIR__."/../vendor/autoload.php";

use Connector\Exceptions\InvalidExecutionPlan;
use Connector\Integrations\AbstractIntegration;
use Connector\Integrations\Authorizations\OAuthInterface;
use Connector\Integrations\Authorizations\OAuthTrait;
use Connector\Integrations\Response;
use Connector\Mapping;
use Connector\Record;
use Connector\Record\RecordKey;
use Connector\Record\RecordLocator;
use Connector\Record\Recordset;
use Connector\Schema\IntegrationSchema;
use HubSpot\Client\Crm\Objects\ApiException;
use HubSpot\Factory;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class Integration extends AbstractIntegration implements OAuthInterface
{
    use OAuthTrait;

    /**
     *  @var \HubSpot\Discovery\Discovery $client
     */
    private $client;

    public function __construct()
    {
        $this->client = Factory::createWithAccessToken(Config::HUBSPOT_ACCESS_TOKEN);
    }

    /**
     * @throws InvalidExecutionPlan
     * @throws \Connector\Exceptions\InvalidSchemaException
     */
    public function discover(): IntegrationSchema
    {
        $hubspotSchema = new HubspotSchema($this->client);
        return $hubspotSchema;
    }

    /**
     * @param \Connector\Record\RecordLocator  $recordLocator
     * @param \Connector\Mapping               $mapping
     * @param \Connector\Record\RecordKey|null $scope
     *
     * @return \Connector\Integrations\Response
     */
    public function extract(RecordLocator $recordLocator, Mapping $mapping, ?RecordKey $scope): Response
    {
        // TODO: Implement extract() method.

        // Recast to Hubspot child class
        $recordLocator = new HubspotRecordLocator($recordLocator, $this->getSchema());

        $action = new Actions\Select($recordLocator, $mapping, $scope);

        $result = $action->execute();

        $this->log('Selected ' . $recordLocator->recordType . ' ' . $result->getLoadedRecordKey()->recordId);

        return (new Response())->setRecordKey($result->getLoadedRecordKey())->setRecordset($result->getExtractedRecordSet());
    }

    /**
     * @param \Connector\Record\RecordLocator  $recordLocator
     * @param \Connector\Mapping               $mapping
     * @param \Connector\Record\RecordKey|null $scope
     *
     * @return \Connector\Integrations\Response
     * 
     * @throws InvalidExecutionPlan
     */ 
    public function load(RecordLocator $recordLocator, Mapping $mapping, ?RecordKey $scope): Response
    {
        $response = new Response();

        // Recast to Hubspot child class
        // $recordLocator->recordType should contain the fullyQualifiedName of the CRM Object record that is to be created
        $recordLocator = new HubspotRecordLocator($recordLocator, $this->getSchema());
        
        // Mapping may contain fully-qualified names (remove record type and keep only property name)
        $mapping = $this->normalizeMapping($mapping);

        // Initially, trying to create only. $recordLocator should contain $type which indicates the type of operation
        if($recordLocator->isCreate()){
            $action = new Actions\Create($recordLocator, $mapping, $scope);
        } else {
            throw new InvalidExecutionPlan("Unknown operation type");
        }
        
        try {
            $result = $action->execute($this->client);
            $this->log('Created ' . $recordLocator->recordType . ' ' . $result->getLoadedRecordKey()->recordId);
        } catch (ApiException $e) {
            throw new InvalidExecutionPlan($e->getMessage());
        }

        $recordset   = new Recordset();
        $recordset[] = new Record($result->getLoadedRecordKey(),
            [
                'FormAssemblyConnectorResult:Id'  => $result->getLoadedRecordKey()->recordId,
                'FormAssemblyConnectorResult:Url' => Config::BASE_URL. 'crm/v' . Config::API_VERSION . '/objects/' . $recordLocator->recordType . '/' . $result->getLoadedRecordKey()->recordId,
            ]
        );

        return $response->setRecordKey($result->getLoadedRecordKey())->setRecordset($recordset);
    }

    /**
     * @throws \Connector\Exceptions\InvalidExecutionPlan
     */
    public function setAuthorization(string $authorization): void
    {
        $this->setOAuthCredentials($authorization);
        // TODO: Implement setAuthorization() method.
    }

    public function getAuthorizationProvider(): AbstractProvider
    {
        // TODO: Implement getAuthorizationProvider() method.
    }

    public function getAuthorizedUserName(ResourceOwnerInterface $user): string
    {
        // TODO: Implement getAuthorizedUserName() method.
    }

    private function normalizeMapping(Mapping $mapping): Mapping
    {
        foreach($mapping as $item) {
            if($this->schema->isFullyQualifiedName($item->key)) {
                $item->key = $this->schema->getPropertyNameFromFQN($item->key);
            }
        }
        return $mapping;
    }
}

$integration = new Integration();
$schema = json_decode(file_get_contents(__DIR__ . "/../tests/schemas/DiscoverResult.json"), true);
$integration->setSchema(new IntegrationSchema($schema));
$integration->begin();

// Configure the operation query and mapping
// $query = ['where' => ['left' => ['left' => "make", "op" => "=", "right" => "Mercedez"], 'op' => 'OR', 'right' => ['left' => ["left" => "make", "op" => "=", "right" => "Nissan"], 'op' => 'AND', 'right' => ['left' => 'model', 'op' => '=', 'right' => "Frontier"]]]];
// $query = ['where' => ['left' => ['left' => "make", "op" => "=", "right" => "BMW"], 'op' => 'OR', 'right' => ['left' => ["left" => "make", "op" => "=", "right" => "Nissan"], 'op' => 'OR', 'right' => ['left' => ['left' => 'year', 'op' => '=', 'right' => '2014'], 'op' => 'AND', 'right' => ['left' => 'model', 'op' => '=', 'right' => 'C-Class']]]]];
$query = ['where' => ['left' => ['left' => "make", "op" => "=", "right" => "BMW"], 'op' => 'OR', 'right' => ['left' => ["left" => "make", "op" => "=", "right" => "Nissan"], 'op' => 'AND', 'right' => ['left' => ['left' => 'year', 'op' => '=', 'right' =>'2019'], 'op' => 'OR', 'right' => ['left' => 'model', 'op' => '=', 'right' =>'C-Class']]]]];
// $query = ['where' => ['left' => 'make', "op" => '=', "right" => 'Mercedez']];

// Ordering false = descending
$orderBy = new HubspotOrderByClause('hs_createdate', false);

$recordLocator = new RecordLocator(["recordType" => 'p46520094_Obj_schema', "query" => $query, 'orderBy' => $orderBy]);
$mapping = new Mapping(["make" => null, "model" => null]);

// Extract the data from Salesforce
$response = $integration->extract($recordLocator, $mapping, null);