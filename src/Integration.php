<?php

namespace Connector\Integrations\Hubspot;
require __DIR__."/../vendor/autoload.php";

use Connector\Integrations\AbstractIntegration;
use Connector\Integrations\Authorizations\OAuthInterface;
use Connector\Integrations\Authorizations\OAuthTrait;
use Connector\Integrations\Response;
use Connector\Mapping;
use Connector\Record\RecordKey;
use Connector\Record\RecordLocator;
use Connector\Schema\IntegrationSchema;
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
        // TODO: Implement discover() method.
        $hubspotSchema = new HubspotSchema($this->client);
        return $hubspotSchema;
    }

    public function extract(RecordLocator $recordLocator, Mapping $mapping, ?RecordKey $scope): Response
    {
        // TODO: Implement extract() method.
    }

    public function load(RecordLocator $recordLocator, Mapping $mapping, ?RecordKey $scope): Response
    {
        // TODO: Implement load() method.
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
}

