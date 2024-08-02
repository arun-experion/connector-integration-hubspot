<?php

namespace Connector\Integrations\Hubspot;

use Connector\Exceptions\InvalidExecutionPlan;
use Connector\Exceptions\RecordNotFound;
use Connector\Exceptions\AbortedOperationException;
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
use GuzzleHttp\Client;
use HubSpot\Client\Crm\Objects\ApiException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
class Integration extends AbstractIntegration implements OAuthInterface
{
    use OAuthTrait;

    /**
     * @var Client $httpClient
     */
    private Client $httpClient;

    /**
     * @var string $baseUrl
     */
    private string $baseUrl = Config::BASE_URL;

    /**
     * @var string $apiVersion
     */
    private string $apiVersion = Config::API_VERSION;

    public function __construct()
    {
        $options['base_uri'] = $this->baseUrl . 'crm/v' . $this->apiVersion . '/objects/';
        $options['headers']['Authorization'] = 'Bearer ' . Config::HUBSPOT_ACCESS_TOKEN;
        $options['headers']['Content-Type'] = 'application/json';
        $this->httpClient = new Client($options);
    }

    /**
     * @throws InvalidExecutionPlan
     * @throws \Connector\Exceptions\InvalidSchemaException
     */
    public function discover(): IntegrationSchema
    {
        $hubspotSchema = new HubspotSchema();
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
        // Recast to Hubspot child class
        $recordLocator = new HubspotRecordLocator($recordLocator, $this->getSchema());

        $action = new Actions\Select($recordLocator, $mapping, $scope);

        $result = $action->execute($this->httpClient);

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

        // $recordLocator should contains $type which indicates the type of operation (Create or Update)
        if($recordLocator->isCreate()){
            $action = new Actions\Create($recordLocator, $mapping, $scope);
        } elseif($recordLocator->isUpdate()){
            // Finding the record id
            $recordLocator = $this->lookupRecordsToUpdate($recordLocator, $scope);
            $action = new Actions\Update($recordLocator, $mapping, $scope);
        } else {
            throw new InvalidExecutionPlan("Unknown operation type");
        }
        
        try {
            $result = $action->execute($this->httpClient);
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

    /**
     * lookupRecordsToUpdate method is used to return the recordId found using the query provided
     * @param \Connector\Integrations\Hubspot\HubspotRecordLocator $recordLocator
     * @param RecordKey|null $scope
     * 
     * @throws \Connector\Exceptions\RecordNotFound
     * @throws \Connector\Exceptions\AbortedOperationException
     * 
     * @return \Connector\Integrations\Hubspot\HubspotRecordLocator
     */
    private function lookupRecordsToUpdate(HubspotRecordLocator $recordLocator, ?RecordKey $scope): HubspotRecordLocator
    {
        if(!empty($recordLocator->query))
        {
            $action = new Actions\Select($recordLocator, new Mapping(['Id' => null]), $scope);
            $result = $action->execute($this->httpClient);
            $this->log(json_encode($action->getLog()));

            if($result->getExtractedRecordSet()->count() > 0)
            {
                $recordLocator->recordId = $result->getExtractedRecordSet()->records[0]->data['id'];
            } else {
                throw new RecordNotFound("No records found.");
            }
        } else{
            throw new AbortedOperationException("Empty query");
        }

        return $recordLocator;
    }
}
