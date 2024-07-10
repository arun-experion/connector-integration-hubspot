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
use Exception;
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

    public function discover(): IntegrationSchema
    {
        $hubspotSchema = new HubspotSchema($this->client);
        return $hubspotSchema;
    }

    public function extract(RecordLocator $recordLocator, Mapping $mapping, ?RecordKey $scope): Response
    {
        // TODO: Implement extract() method.
    }

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
        } catch (Exception $e) {
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
$schema = json_decode(file_get_contents(__DIR__."/../DiscoverResult.json"),true);
$integration->setSchema(new IntegrationSchema($schema));
$integration->begin();

// $recordLocator = new RecordLocator(["recordType" => 'p46520094_Obj_schema']);
$recordLocator = new RecordLocator(["recordType" => 'deals']);

// Mock data
if($recordLocator->recordType == 'companies'){
    $mapping = new Mapping([
        
        
        "state" => "Massachusetts"
    ]);
} else if($recordLocator->recordType == 'contacts'){
    $mapping = new Mapping([
        
        "phone" => "(555) 555-5555",
        "company" => "HubSpot",
        "lifecyclestage" => "marketingqualifiedlead"
    ]);
} else if($recordLocator->recordType == 'deals'){
    $mapping = new Mapping([
        "amount" => "1500.00",
        "closedate" => "2019-12-07T16:50:06.678Z",
        "dealname" => "New deal",
        "pipeline" => "default",
        "dealstage" => "contractsent"
    ]);
} else if($recordLocator->recordType == 'tickets'){
    $mapping = new Mapping([
        "hs_pipeline" => "0",
        "hs_pipeline_stage" => "1",
        "hs_ticket_priority" => "HIGH",
        "subject" => "troubleshoot report"
    ]);
} else{
    $mapping = new Mapping([
        "condition" => "used",
        "date_received" => "1582416000000",
        "year" => "2019",
        "make" => "Nissan",
        "model" => "GTR"
    ]);
}
$integration->load($recordLocator, $mapping, null);