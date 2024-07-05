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
    public const HUBSPOT_ACCESS_TOKEN   =   'pat-na1-a49bf2f9-4aea-48e8-9d58-a1ff8c0cabed';
}
