<?php

namespace Connector\Integrations\Hubspot;

use Connector\Integrations\AbstractIntegration;
use Connector\Integrations\Response;
use Connector\Mapping;
use Connector\Record\RecordKey;
use Connector\Record\RecordLocator;
use Connector\Schema\IntegrationSchema;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class Integration extends AbstractIntegration
{

    public function discover(): IntegrationSchema
    {
        // TODO: Implement discover() method.
    }

    public function extract(RecordLocator $recordLocator, Mapping $mapping, ?RecordKey $scope): Response
    {
        // TODO: Implement extract() method.
    }

    public function load(RecordLocator $recordLocator, Mapping $mapping, ?RecordKey $scope): Response
    {
        // TODO: Implement load() method.
    }

}
