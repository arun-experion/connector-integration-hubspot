<?php
namespace Connector\Integrations\Hubspot;

use InvalidArgumentException;

class HubspotRequestBodyBuilder
{
    /**
     * Function to create the required JSON structure for the request body.
     * @param array $query
     * @param array $selectFields
     * @param \Connector\Integrations\Hubspot\HubspotOrderByClause $orderBy
     * @return array
     */
    static public function toRequestBody(array $query, array $selectFields, HubspotOrderByClause $orderBy): array
    {
        // Creating the filters and filterGroups inside request body
        $result = [];
        $body = self::createFilterGroupsAndFilters($query['where'], $result);

        // To return a specific set of properties, include a properties array in the request body
        $body['properties'] = $selectFields;
        
        // By default results will be returned in order of object creation (oldest first)
        // Only if an order by is manually mentioned, a sort key will be prepared in the request body
        if (!empty($orderBy->column)) {
            $body['sorts'] = HubspotOrderByClause::toSorts($orderBy);
        }

        // Maximum limit of retruning records is 100.
        $body['limit'] = 100;
        return $body;
    }

    /**
     * This method is used to create the filterGroups and filters in the request body
     * @param array $query
     * @param array $result
     * @return array
     */
    static public function createFilterGroupsAndFilters(array $query, array &$result): array
    {
        if (!isset($result['filterGroups'])) {
            $result['filterGroups'] = [];
        }

        // Checking if op is OR. When OR is encountered, a new object in filterGroup should be created.
        if (isset($query['op']) && self::translateOperator(strtoupper($query['op'])) == 'OR') {
            if (is_array($query['left'])) {
                $leftGroup = ['filterGroups' => []];
                self::createFilterGroupsAndFilters($query['left'], $leftGroup);

                // Merge the filter groups from the left part into the result
                $result['filterGroups'] = array_merge($result['filterGroups'], $leftGroup['filterGroups']);
            } else {
                // Add the single filter from the left part to the result
                $result['filterGroups'][] = [
                    'filters' => [self::formatFilter($query['left'])]
                ];
            }

            if (is_array($query['right'])) {
                $rightGroup = ['filterGroups' => []];
                self::createFilterGroupsAndFilters($query['right'], $rightGroup);

                // Merge the filter groups from the right part into the result
                $result['filterGroups'] = array_merge($result['filterGroups'], $rightGroup['filterGroups']);
            } else {
                // Add the single filter from the right part to the result
                $result['filterGroups'][] = [
                    'filters' => [self::formatFilter($query['right'])]
                ];
            }
        } else if (isset($query['op']) && self::translateOperator(strtoupper($query['op'])) == 'AND') {
            // When a AND is encountered, the query should be appended to filter array
            // Initialize an array to hold filters for the 'AND' condition
            $group = [];

            if (is_array($query['left'])) {
                $leftGroup = ['filterGroups' => []];
                self::createFilterGroupsAndFilters($query['left'], $leftGroup);

                // Merge the filters from the left part into the group array
                $group = array_merge($group, $leftGroup['filterGroups'][0]['filters']);
            } else {
                // Add the single filter from the left part to the group array
                $group[] = self::formatFilter($query['left']);
            }

            if (is_array($query['right'])) {
                $rightGroup = ['filterGroups' => []];
                self::createFilterGroupsAndFilters($query['right'], $rightGroup);

                // Merge the filters from the right part into the group array
                $group = array_merge($group, $rightGroup['filterGroups'][0]['filters']);
            } else {
                // Add the single filter from the right part to the group array
                $group[] = self::formatFilter($query['right']);
            }

            // Add the combined filters as a new filter group to the result
            $result['filterGroups'][] = ['filters' => $group];
        } else {
            // Process a single filter condition (no logical operator)
            $result['filterGroups'][] = ['filters' => [self::formatFilter($query)]];
        }

        return $result;
    }

    /**
     * Function to format an indivudal filter for the request body
     * @param array $query
     * @return array
     */
    static public function formatFilter(array $query): array
    {
        return [
            'propertyName' => $query['left'],
            'operator' => self::translateOperator($query['op']),
            'value' => $query['right']
        ];
    }

    /**
     * @param string $operator
     * 
     * @throws InvalidArgumentException
     * 
     * @return string
     */
    static public function translateOperator(string $operator): string
    {
        switch ($operator) {
            case 'AND':
                return 'AND';
            case 'OR':
                return 'OR';
            case '=':
                return 'EQ';
            case '!=':
                return 'NEQ';
            case '<=':
                return 'LTE';
            case '<':
                return 'LT';
            case '>=':
                return 'GTE';
            case '>':
                return 'GT';
            case 'IN':
                return 'IN';
            case 'NOTIN':
                return 'NOT_IN';
            case 'BETWEEN':
                return 'BETWEEN';
            case 'LIKE':
                return 'CONTAINS_TOKEN';
            case 'NOTLIKE':
                return 'NOT_CONTAINS_TOKEN';
            default:
                throw new InvalidArgumentException("Invalid operator: ", $operator);
        }
    }
}
