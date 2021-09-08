<?php

namespace Tymy\Module\Core\Model;

/**
 * Description of Filter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 15. 2. 2021
 */
class Filter
{

    private string $column;
    private string $operator;
    private $value;

    public function __construct(string $column, string $operator, $value)
    {
        $this->column = $column;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue()
    {
        return $this->value;
    }

    public static function toParams(array $filters): array
    {
        $values = [];

        foreach ($filters as $filter) {
            /* @var $filter Filter */
            $values[] = $filter->getValue();
        }

        return $values;
    }

    /**
     * Generate string to append to query, with each filter connected by ANDs and prepended with AND
     * 
     * @param Filter[] $filters
     * @return string
     */
    public static function toAndQuery(array $filters): string
    {
        if(empty($filters)){
            return "";
        }
        
        $q = [];

        foreach ($filters as $filter) {
            /* @var $filter Filter */
            if ($filter->getOperator() === "#=") {
                $q[] = "UPPER(`{$filter->getColumn()}`) = UPPER(?)"; //UPPER(`column`) = UPPER('value'), replaced by question marks
            } else {
                $q[] = "`{$filter->getColumn()}` {$filter->getOperator()} ?"; //`column` >= 'value', replaced by question marks
            }
        }

        return " AND " . join(" AND ", $q) . " ";//with trailing space
    }

}
