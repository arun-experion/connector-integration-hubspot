<?php

namespace Connector\Integrations\Hubspot\Enumerations;

enum OperationTypes: string
{
    case Select = 'select';
    case Create = 'create';
    case Update = 'update';
}
