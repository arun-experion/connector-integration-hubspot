<?php

namespace Connector\Integrations\Hubspot;

use Connector\Integrations\Hubspot\Enumerations\OperationTypes;
use Connector\Record\RecordLocator;
use Connector\Schema\IntegrationSchema;

class HubspotRecordLocator extends RecordLocator
{
    /**
     * @var OperationTypes $type Type of Operation (create, update)
     */
    public OperationTypes $type = OperationTypes::Create;
    
    /**
     * @param mixed|null                               $params
     * @param \Connector\Schema\IntegrationSchema|null $schema
     *
     * @throws \Connector\Exceptions\InvalidSchemaException
     */
    public function __construct(mixed $params = null, IntegrationSchema $schema = null)
    {
        parent::__construct($params);
    }

    /**
     * Check if the operation type is 'create'.
     *
     * @return bool Returns true if the operation type is 'create', false otherwise.
     */
    public function isCreate(): bool
    {
        return $this->type === OperationTypes::Create;
    }

    /**
     * Check if the operation type is 'update'.
     *
     * @return bool Returns true if the operation type is 'create', false otherwise.
     */
    public function isUpdate(): bool
    {
        return $this->type === OperationTypes::Update;
    }
}