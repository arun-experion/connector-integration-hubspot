<?php

namespace Tests;

use Connector\Exceptions\AbortedExecutionException;
use Connector\Exceptions\AbortedOperationException;
use Connector\Integrations\Hubspot\HubspotOrderByClause;
use Connector\Integrations\Hubspot\HubspotRequestBodyBuilder;
use InvalidArgumentException;
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
    function testAndClause()
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
    function testOrClause()
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
    /**
     * Test the construction of an NOT EQUAL clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an OR clause combining two conditions.
     */
    function testNotEqualClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => '!=', 'right' => 'example.com']];

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
                            "operator" => "NEQ",
                            "value" => "example.com"
                        ],
                    ]
                ]
            ],
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }

    /**
     * Test the construction of an LessThanOrEqual clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an OR clause combining two conditions.
     */
    function testLessThanOrEqualClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => '<=', 'right' => 'example.com']];
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
                            "operator" => "LTE",
                            "value" => "example.com"
                        ],
                    ]
                ]
            ],
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
    /**
     * Test the construction of an LessThan clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an OR clause combining two conditions.
     */
    function testLessThanClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => '<', 'right' => 'example.com']];
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
                            "operator" => "LT",
                            "value" => "example.com"
                        ],
                    ]
                ]
            ],
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
    /**
     * Test the construction of an GreaterThanOrEqual clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an OR clause combining two conditions.
     */
    function testGreaterThanOrEqualClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => '>=', 'right' => 'example.com']];
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
                            "operator" => "GTE",
                            "value" => "example.com"
                        ],
                    ]
                ]
            ],
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
    /**
     * Test the construction of an GreaterThan clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an OR clause combining two conditions.
     */
    function testGreaterThanClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => '>', 'right' => 'example.com']];
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
                            "operator" => "GT",
                            "value" => "example.com"
                        ],
                    ]
                ]
            ],
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
    /**
     * Test the construction of an IN clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an OR clause combining two conditions.
     */
    function testInClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => 'IN', 'right' => 'example.com']];
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
                            "operator" => "IN",
                            "value" => "example.com"
                        ],
                    ]
                ]
            ],
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
    /**
     * Test the construction of an NOT IN clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an OR clause combining two conditions.
     */
    function testNotInClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => 'NOTIN', 'right' => 'example.com']];
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
                            "operator" => "NOT_IN",
                            "value" => "example.com"
                        ],
                    ]
                ]
            ],
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
    /**
     * Test the construction of an LIKE clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an OR clause combining two conditions.
     */
    function testLikeClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => 'LIKE', 'right' => 'example.com']];
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
                            "operator" => "CONTAINS_TOKEN",
                            "value" => "example.com"
                        ],
                    ]
                ]
            ],
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
    /**
     * Test the construction of an NOT LIKE clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an OR clause combining two conditions.
     */
    function testNotLikeClause()
    {
        $searchCondition = ["where" => ['left' => 'domain', 'op' => 'NOTLIKE', 'right' => 'example.com']];
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
                            "operator" => "NOT_CONTAINS_TOKEN",
                            "value" => "example.com"
                        ],
                    ]
                ]
            ],
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
    /**
     * Test the construction of an BETWEEN clause.
     *
     * This test case verifies that the HubspotRequestBodyBuilder correctly constructs
     * a request body with an OR clause combining two conditions.
     */
    function testBetweenClause()
    {
        $searchCondition = ["where" =>   ['left' => 'createdate', 'op' => 'BETWEEN', 'right' => ['2023-01-01', '2023-12-31']]];
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
                            "propertyName" => "createdate",
                            "operator" => "BETWEEN",
                            "value" => ['2023-01-01', '2023-12-31']
                        ],
                    ]
                ]
            ],
            "properties" => ["domain", "name"],
            'limit' => 100,
        ];
        $this->assertEquals($desiredRequestBody, $hubspotRequestBody);
    }
     /**
     * Test the Invalid operator for HubspotRequestBodyBuilder.
     */
    function testInvalidClause()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid operator: ");
        $searchCondition = ["where" => ['left' => 'domain', 'op' =>0, 'right' => 'example.com']];
        $selectFields = ["domain", "name"];
        $orderBy = new HubspotOrderByClause();
        // Generate the request body using the HubspotRequestBodyBuilder
        $hubspotRequestBodyBuilder = new HubspotRequestBodyBuilder;
        $hubspotRequestBodyBuilder->toRequestBody($searchCondition, $selectFields, $orderBy);
    }

     /**
     * Test the Invalid Left Clause for OR clause in HubspotRequestBodyBuilder.
     */
    function testInvalidLeftOperationOrClause()
    {
        $this->expectException(AbortedOperationException::class);
        $this->expectExceptionMessage("Left key should contain an array");
        $a = '';
        $b = ['left' => 'name', 'op' => '=', 'right' => 'example'];
        $searchCondition  = ["where" => ['left' => $a,  'op' => 'OR', 'right' => $b]];
        $selectFields = ["domain", "name"];
        $orderBy = new HubspotOrderByClause();
        // Generate the request body using the HubspotRequestBodyBuilder
        $hubspotRequestBodyBuilder = new HubspotRequestBodyBuilder;
        $hubspotRequestBodyBuilder->toRequestBody($searchCondition, $selectFields, $orderBy);
    }

    /**
     * Test the Invalid Right Clause for OR clause in HubspotRequestBodyBuilder.
     */
    function testInvalidRightOperationOrClause()
    {
        $this->expectException(AbortedOperationException::class);
        $this->expectExceptionMessage("Right key should contain an array");
        $a    =  ['left' => 'name', 'op' => '=', 'right' => 'example'];
        $b= '';
        $searchCondition  = ["where" => ['left' => $a,  'op' => 'OR', 'right' => $b]];
        $selectFields = ["domain", "name"];
        $orderBy = new HubspotOrderByClause();
        // Generate the request body using the HubspotRequestBodyBuilder
        $hubspotRequestBodyBuilder = new HubspotRequestBodyBuilder;
        $hubspotRequestBodyBuilder->toRequestBody($searchCondition, $selectFields, $orderBy);
    }

    /**
     * Test the Invalid Left Clause for AND clause in HubspotRequestBodyBuilder.
     */
    function testInvalidLeftOperationAndClause()
    {
        $this->expectException(AbortedOperationException::class);
        $this->expectExceptionMessage("Left key should contain an array");
         $a    =  '';
        $b= ['left' => 'name', 'op' => '=', 'right' => 'example'];
        $searchCondition  = ["where" => ['left' => $a,  'op' => 'AND', 'right' => $b]];
        $selectFields = ["domain", "name"];
        $orderBy = new HubspotOrderByClause();
        // Generate the request body using the HubspotRequestBodyBuilder
        $hubspotRequestBodyBuilder = new HubspotRequestBodyBuilder;
        $hubspotRequestBodyBuilder->toRequestBody($searchCondition, $selectFields, $orderBy);
    }
    
    /**
     * Test the Invalid Right Clause for OR clause in HubspotRequestBodyBuilder.
     */
    function testInvalidRightOperationAndClause()
    {
        $this->expectException(AbortedOperationException::class);
        $this->expectExceptionMessage("Right key should contain an array");
        $a    =  ['left' => 'name', 'op' => '=', 'right' => 'example'];
        $b= '';
        $searchCondition  = ["where" => ['left' => $a,  'op' => 'AND', 'right' => $b]];
        $selectFields = ["domain", "name"];
        $orderBy = new HubspotOrderByClause();
        // Generate the request body using the HubspotRequestBodyBuilder
        $hubspotRequestBodyBuilder = new HubspotRequestBodyBuilder;
        $hubspotRequestBodyBuilder->toRequestBody($searchCondition, $selectFields, $orderBy);
    }
}
