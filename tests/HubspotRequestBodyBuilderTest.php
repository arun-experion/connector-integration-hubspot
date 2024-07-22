<?php

namespace Tests;

use Connector\Integrations\Hubspot\HubspotOrderByClause;
use Connector\Integrations\Hubspot\HubspotRequestBodyBuilder;
use PHPUnit\Framework\TestCase;

final class HubspotRequestBodyBuilderTest extends TestCase
{

    function testSimpleWhereClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'example.com']];
        $selectFields=["domain", "name"];
        $orderBy=new HubspotOrderByClause();
        $hubspotRequestBodyBuilder=new HubspotRequestBodyBuilder;
        $hubspotRequestBody=$hubspotRequestBodyBuilder->toRequestBody($searchCondition,$selectFields,$orderBy);
        $desiredRequestBody = [
            "filterGroups" => [
                [
                    "filters" => [
                        [
                            "propertyName" => "domain",
                            "operator" => "EQ",
                            "value" => "example.com"
                        ]
                    ]
                ]
            ],
            "properties"=> ["domain", "name"],
            'limit' => 100,
        ];
         $this->assertEquals($desiredRequestBody,$hubspotRequestBody);

    }

    function testANDClause()
    {
        $a   =  ['left' => 'domain', 'op' => '=', 'right' => 'example.com'];
        $b    =  ['left' => 'name', 'op' => '=', 'right' => 'example'];
        $searchCondition  = ["where" => ['left' => $a,  'op' => 'AND', 'right' => $b]];
        $selectFields=["domain", "name"];
        $orderBy=new HubspotOrderByClause();
        $hubspotRequestBodyBuilder=new HubspotRequestBodyBuilder;
        $hubspotRequestBody=$hubspotRequestBodyBuilder->toRequestBody($searchCondition,$selectFields,$orderBy);
       
        $desiredRequestBody = [
            "filterGroups" => [
                [
                    "filters" => [
                        [
                            "propertyName" => "domain",
                            "operator" => "EQ",
                            "value" => "example.com"
                        ],
                        [
                            "propertyName" => "name",
                            "operator" => "EQ",
                            "value" => "example"
                        ]
                    ]
                ],
            ],
            "properties"=> ["domain", "name"],
            'limit' => 100,
        ];

        $this->assertEquals($desiredRequestBody,$hubspotRequestBody);
    }
    function testORClause()
    {
        $a   =  ['left' => 'domain', 'op' => '=', 'right' => 'example.com'];
        $b    =  ['left' => 'name', 'op' => '=', 'right' => 'example'];
        $searchCondition  = ["where" => ['left' => $a,  'op' => 'OR', 'right' => $b]];
        $selectFields=["domain", "name"];
        $orderBy=new HubspotOrderByClause();
        $hubspotRequestBodyBuilder=new HubspotRequestBodyBuilder;
        $hubspotRequestBody=$hubspotRequestBodyBuilder->toRequestBody($searchCondition,$selectFields,$orderBy);
       
        $desiredRequestBody =[
            "filterGroups" => [
                [
                    "filters" => [
                        [
                            "propertyName" => "domain",
                            "operator" => "EQ",
                            "value" => "example.com"
                        ],
                    ]
                ],
                [
                    "filters" => [
                        [
                            "propertyName" => "name",
                            "operator" => "EQ",
                            "value" => "example"
                        ]
                    ]
                ]
            ],
            "properties"=> ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody,$hubspotRequestBody);
    }

    function testDescendingOrderByClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'example.com']];
        $selectFields=["domain", "name"];
        $orderBy=new HubspotOrderByClause('hs_createdate', false);
        $hubspotRequestBodyBuilder=new HubspotRequestBodyBuilder;
        $hubspotRequestBody=$hubspotRequestBodyBuilder->toRequestBody($searchCondition,$selectFields,$orderBy);
   
        $desiredRequestBody = [
            "filterGroups" => [
                [
                    "filters" => [
                        [
                            "propertyName" => "domain",
                            "operator" => "EQ",
                            "value" => "example.com"
                        ],
                    ]
                ],
            ],
            "properties"=> ["domain", "name"],
            'limit' => 100,
            "sorts" => [
                [
                    "propertyName" => "hs_createdate",
                    "direction" => "DESCENDING"
                ]
            ]
        ];
        $this->assertEquals($desiredRequestBody,$hubspotRequestBody);
    }

    function testAscendingOrderByClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'example.com']];
        $selectFields=["domain", "name"];
        $orderBy=new HubspotOrderByClause('hs_createdate', true);
        $hubspotRequestBodyBuilder=new HubspotRequestBodyBuilder;
        $hubspotRequestBody=$hubspotRequestBodyBuilder->toRequestBody($searchCondition,$selectFields,$orderBy);
   
        $desiredRequestBody = [
            "filterGroups" => [
                [
                    "filters" => [
                        [
                            "propertyName" => "domain",
                            "operator" => "EQ",
                            "value" => "example.com"
                        ],
                    ]
                ],
            ],
            "properties"=> ["domain", "name"],
            'limit' => 100,
            "sorts" => [
                [
                    "propertyName" => "hs_createdate",
                    "direction" => "ASCENDING"
                ]
            ]
        ];
        $this->assertEquals($desiredRequestBody,$hubspotRequestBody);
    }
}
