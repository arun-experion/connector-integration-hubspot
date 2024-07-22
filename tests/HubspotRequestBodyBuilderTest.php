<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

final class HubspotRequestBodyBuilderTest extends TestCase
{

    function testSimpleWhereClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'example.com']];
        //calls the desired function to format $searchCondition
        // $hubspotRequestBodyBuilder=new HubspotRequestBodyBuilder
        // $hubspotRequestBody=$hubspotRequestBody->toRequestBody($searchCondition);
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
        ];
        // $this->assertEquals($desiredRequestBody,$hubspotRequestBody)

    }

    function testANDClause()
    {
        $a   = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'example.com']];
        $b    = ["where" => ['left' => 'name', 'op' => '=', 'right' => 'example']];
        $where  = ["where" => ['left' => $a, 'op' => "AND", 'right' => $b]];

        //calls the desired function to format $where
        // $hubspotRequestBodyBuilder=new HubspotRequestBodyBuilder
        // $hubspotRequestBody=$hubspotRequestBody->toRequestBody($searchCondition);
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
        ];

        //$this->assertEquals($desiredRequestBody,$hubspotRequestBody)
    }
    function testORClause()
    {
        $a   = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'example.com']];
        $b    = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'examplehubspot.com']];
        $where  = ["where" => ['left' => $a, 'op' => 'OR', 'right' => $b]];

        //calls the desired function to format $where
        // $hubspotRequestBodyBuilder=new HubspotRequestBodyBuilder
        // $hubspotRequestBody=$hubspotRequestBody->toRequestBody($searchCondition);
        $desiredRequestBody = $requestPayload = [
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

        ];

        //$this->assertEquals($desiredRequestBody,$hubspotRequestBody)
    }

    function testDescendingOrderByClause()
    {
        $where = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'example.com']];
        $orderBy = ['Created', 'Descending'];
        //calls the desired function to format $where
        // $hubspotRequestBodyBuilder=new HubspotRequestBodyBuilder
        // $hubspotRequestBody=$hubspotRequestBody->toRequestBody($searchCondition);
        $desiredRequestFormat = [
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
            "sorts" => [
                [
                    "propertyName" => "createdate",
                    "direction" => "DESCENDING"
                ]
            ]
        ];

        //$this->assertEquals($desiredRequestBody,$hubspotRequestBody)
    }

    function testAscendingOrderByClause()
    {
        $where = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'example.com']];
        $orderBy = ['Created', 'Ascending'];
        //calls the desired function to format $where
        // $hubspotRequestBodyBuilder=new HubspotRequestBodyBuilder
        // $hubspotRequestBody=$hubspotRequestBody->toRequestBody($searchCondition);
        $desiredRequestFormat = [
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
            "sorts" => [
                [
                    "propertyName" => "createdate",
                    "direction" => "ASCENDING"
                ]
            ]
        ];

        //$this->assertEquals($desiredRequestBody,$hubspotRequestBody)
    }
}
