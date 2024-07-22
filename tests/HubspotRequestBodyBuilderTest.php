<?php

namespace Tests;

use Connector\Integrations\Hubspot\HubspotOrderByClause;
use Connector\Integrations\Hubspot\HubspotRequestBodyBuilder;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Connector\Integrations\Hubspot\HubspotRequestBodyBuilder
 * @covers \Connector\Integrations\Hubspot\HubspotOrderByClause
 */
class HubspotRequestBodyBuilderTest extends TestCase
{
    /**
     * Test the construction of a simple WHERE clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with a simple WHERE clause filtering by domain.
     */
    function testSimpleWhereClause()
    {
        // Define the search condition with a simple WHERE clause
        $searchCondition = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'example.com']];
        // Define the fields to be selected
        $selectFields = ["domain", "name"];
        // Create a new orderBy clause object for ordering the results
        $orderBy = new HubspotOrderByClause();
        // Generate the request body using the HubspotRequestBodyBuilder
        $hubspotRequestBodyBuilder = new HubspotRequestBodyBuilder;
        $hubspotRequestBody = $hubspotRequestBodyBuilder->toRequestBody($searchCondition, $selectFields, $orderBy);
        // Define the expected request body
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
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
    /**
     * Test the construction of an AND clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an AND clause combining two conditions.
     */
    function testANDClause()
    {
        // Define two conditions to be combined with AND
        $a   =  ['left' => 'domain', 'op' => '=', 'right' => 'example.com'];
        $b    =  ['left' => 'name', 'op' => '=', 'right' => 'example'];
        // Define the search condition with an AND clause
        $searchCondition  = ["where" => ['left' => $a,  'op' => 'AND', 'right' => $b]];
        $selectFields = ["domain", "name"];
        $orderBy = new HubspotOrderByClause();
        // Generate the request body using the HubspotRequestBodyBuilder
        $hubspotRequestBodyBuilder = new HubspotRequestBodyBuilder;
        $hubspotRequestBody = $hubspotRequestBodyBuilder->toRequestBody($searchCondition, $selectFields, $orderBy);
        // Define the expected request body
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
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];

        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
    /**
     * Test the construction of an OR clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an OR clause combining two conditions.
     */
    function testORClause()
    {
        // Define two conditions to be combined with OR  
        $a   =  ['left' => 'domain', 'op' => '=', 'right' => 'example.com'];
        $b    =  ['left' => 'name', 'op' => '=', 'right' => 'example'];
        // Define the search condition with an OR clause
        $searchCondition  = ["where" => ['left' => $a,  'op' => 'OR', 'right' => $b]];

        $selectFields = ["domain", "name"];
        $orderBy = new HubspotOrderByClause();
        // Generate the request body using the HubspotRequestBodyBuilder
        $hubspotRequestBodyBuilder = new HubspotRequestBodyBuilder;
        $hubspotRequestBody = $hubspotRequestBodyBuilder->toRequestBody($searchCondition, $selectFields, $orderBy);
        // Define the expected request body
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
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
    /**
     * Test the construction of a request body with a descending order by clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with a descending order by clause for a specified field.
     */
    function testDescendingOrderByClause()
    {
        // Define the search condition with a simple WHERE clause
        $searchCondition = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'example.com']];
        // Define the fields to be selected
        $selectFields = ["domain", "name"];
        // Create a new orderBy clause object with descending order for 'hs_createdate'
        $orderBy = new HubspotOrderByClause('hs_createdate', false);
        // Generate the request body using the HubspotRequestBodyBuilder
        $hubspotRequestBodyBuilder = new HubspotRequestBodyBuilder;
        $hubspotRequestBody = $hubspotRequestBodyBuilder->toRequestBody($searchCondition, $selectFields, $orderBy);
        // Define the expected request body
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
            "properties" => ["domain", "name"],
            'limit' => 100,
            "sorts" => [
                [
                    "propertyName" => "hs_createdate",
                    "direction" => "DESCENDING"
                ]
            ]
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
    /**
     * Test the construction of a request body with an ascending order by clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an ascending order by clause for a specified field.
     */
    function testAscendingOrderByClause()
    {
        // Define the search condition with a simple WHERE clause
        $searchCondition = ["where" => ['left' => 'domain', 'op' => '=', 'right' => 'example.com']];
        // Define the fields to be selected
        $selectFields = ["domain", "name"];
        // Create a new orderBy clause object with ascending order for 'hs_createdate'
        $orderBy = new HubspotOrderByClause('hs_createdate', true);
        // Generate the request body using the HubspotRequestBodyBuilder
        $hubspotRequestBodyBuilder = new HubspotRequestBodyBuilder;
        $hubspotRequestBody = $hubspotRequestBodyBuilder->toRequestBody($searchCondition, $selectFields, $orderBy);
        // Define the expected request body
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
            "properties" => ["domain", "name"],
            'limit' => 100,
            "sorts" => [
                [
                    "propertyName" => "hs_createdate",
                    "direction" => "ASCENDING"
                ]
            ]
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
}
