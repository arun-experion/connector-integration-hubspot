<?php
namespace Connector\Integrations\Hubspot;

class Config{
    public const HUBSPOT_ACCESS_TOKEN='pat-na1-1d681633-88b2-4bc5-a23f-8205986b4736';

    // STANDARD_CRM_OBJECTS contains standard objects from HubSpot
    public const STANDARD_CRM_OBJECTS = ['contacts', 'companies', 'deals', 'tickets'];

    public const API_VERSION = '3';

    public const BASE_URL = 'https://api.hubapi.com/';
}