<?php

namespace Connector\Integrations\Hubspot;

class Config
{
    // Default API version. Can be overwritten by user in connector setup.
    public const API_VERSION = '60.0';

    // Client ID and Secret, registered in FormAssembly's main Salesforce org as the "FormAssembly Salesforce Connector"
    public const CLIENT_ID = '';
    public const CLIENT_SECRET = '';

    
    public const REDIRECT_URI = 'https://app.formassembly.localhost:8443/api_v2/authorization/redirect';

    // Misc OAuth configuration.
    public const USER_INFO_URI          =   '';
    // public const HUBSPOT_ACCESS_TOKEN   =   'pat-na1-1eac8072-6d73-4564-ae2c-cddd101a8a65';   //Abhilash's App

    // Private app access token
    public const HUBSPOT_ACCESS_TOKEN   =   'pat-na1-1d681633-88b2-4bc5-a23f-8205986b4736';

    // STANDARD_CRM_OBJECTS contains standard objects from HubSpot
    public const STANDARD_CRM_OBJECTS = ['contacts', 'companies', 'deals', 'tickets'];
}
