<?php

namespace Connector\Integrations\Hubspot;

use Connector\Integrations\Hubspot\Enumerations\OperationTypes;
use Connector\Record\RecordLocator;
use Connector\Schema\IntegrationSchema;

class HubspotRecordLocator extends RecordLocator
{
    /**
     * @var OperationTypes $type Type of Operation (create, update, select)
     */
    public OperationTypes $type = OperationTypes::Create;

    /**
     * @var array<string, string, string> $query Contains the query, with keys left, op and right
     */
    public array $query;

    /**
     * @var HubspotOrderByClause $orderBy Contains order by clause
     */
    public HubspotOrderByClause $orderBy;

    /**
     * @param mixed|null                               $params
     * @param \Connector\Schema\IntegrationSchema|null $schema
     *
     * @throws \Connector\Exceptions\InvalidSchemaException
     */
    public function __construct(mixed $params = null, IntegrationSchema $schema = null)
    {
        $this->orderBy = new HubspotOrderByClause();
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
}
