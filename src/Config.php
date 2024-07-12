<?php
namespace Connector\Integrations\Hubspot;

class Config
{
    public const HUBSPOT_ACCESS_TOKEN = '';

    public const API_VERSION = '3';

    public const BASE_URL = 'https://api.hubapi.com/';

    // Required properties for companies
    public const REQUIRED_COMPANIES_PROPERTIES = ['name', 'domain'];

    // Required properties for contacts
    public const REQUIRED_CONTACTS_PROPERTIES = ['email', 'firstname', 'lastname'];

    // Required properties for deals
    public const REQUIRED_DEALS_PROPERTIES = ['dealname', 'dealstage'];

    // Required properties for tickets
    public const REQUIRED_TICKETS_PROPERTIES = ['subject', 'hs_pipeline_stage'];

    // STANDARD_CRM_OBJECTS contains standard objects and its required properties from HubSpot
    public const STANDARD_CRM_OBJECTS = [
        ["fully_qualified_name" => "contacts", 'required_properties' => Config::REQUIRED_CONTACTS_PROPERTIES],
        ["fully_qualified_name" => "companies", 'required_properties' => Config::REQUIRED_COMPANIES_PROPERTIES],
        ["fully_qualified_name" => "deals", 'required_properties' => Config::REQUIRED_DEALS_PROPERTIES],
        ["fully_qualified_name" => "tickets", 'required_properties' => Config::REQUIRED_TICKETS_PROPERTIES]
    ];
}