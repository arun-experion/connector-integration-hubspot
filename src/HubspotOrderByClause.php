<?php

namespace Connector\Integrations\Hubspot;

use InvalidArgumentException;

class HubspotOrderByClause 
{
    /**
     * Contains name of the field to be sorted
     * @var mixed $column
     */
    public mixed $column;

    /**
     * @var mixed $ascending
     */
    public bool $ascending;

    public function __construct(mixed $column = null,bool $ascending = true) {
        $this->column = $column;
        $this->ascending = $ascending;
    }

    /**
     * @param HubspotOrderByClause $orderBy
     * @return array
     */
    static public function toSorts(HubspotOrderByClause $orderBy): array
    {
       $sorts = [['propertyName' => $orderBy->column, 'direction' => self::convertOrderBy($orderBy->ascending)]]; 
       return $sorts;   
    }

    /**
     * @param bool $operator
     * @throws InvalidArgumentException
     * @return string
     */
    static public function convertOrderBy(bool $operator): string
    {
        switch ($operator) {
            case true:
                return 'ASCENDING';
            case false:
                return 'DESCENDING';
            default:
                throw new InvalidArgumentException("Invalid clause for ordering: ", $operator);
        }
    }

}
