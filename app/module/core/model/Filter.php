<?php

namespace Tymy\Module\Core\Model;

use Nette\Database\Table\Selection;

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

    /**
     * Add filters, one by one, to existing selector, using ->where functions
     */
    public static function addFilter(Selection &$selector, array $filters): void
    {
        if (empty($filters)) {
            return;
        }

        foreach ($filters as $filter) {
            /* @var $filter Filter */
            if ($filter->getOperator() === "#=") {
                $selector->where("UPPER(`{$filter->getColumn()}`) = UPPER(?)", $filter->getValue());
            } else {
                $selector->where("`{$filter->getColumn()}` {$filter->getOperator()} ?", $filter->getValue()); //`column` >= 'value', replaced by question marks
            }
        }
    }
}
