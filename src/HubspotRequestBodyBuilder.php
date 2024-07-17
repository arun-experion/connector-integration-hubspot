<?php
namespace Connector\Integrations\Hubspot;

class HubspotRequestBodyBuilder
{
    static public function toRequestBody( $query, array $selectFields): array
    {
        $body['properties']= $selectFields;
        $body['filterGroups']= $query;
        $body['limit'] = 100;
        
        return $body;
    }
}