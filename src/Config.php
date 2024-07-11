<?php
namespace Connector\Integrations\Hubspot;

class Config{
    public const HUBSPOT_ACCESS_TOKEN='';

    // STANDARD_CRM_OBJECTS contains standard objects from HubSpot
    public const STANDARD_CRM_OBJECTS = ['contacts', 'companies', 'deals', 'tickets'];

    public const API_VERSION = '3';

    public const BASE_URL = 'https://api.hubapi.com/';

    // Required properties for companies
    public const REQUIRED_COMPANIES_PROPERTIES = ["companies" => ['name', 'domain']];

    // Required properties for contacts
    public const REQUIRED_CONTACTS_PROPERTIES = ["contacts" => ['email', 'firstname', 'lastname']];

    // Required properties for deals
    public const REQUIRED_DEALS_PROPERTIES = ["deals" => ['dealname', 'dealstage']];
    
    // Required properties for tickets
    public const REQUIRED_TICKETS_PROPERTIES = ["tickets" => ['subject', 'hs_pipeline_stage']];
}