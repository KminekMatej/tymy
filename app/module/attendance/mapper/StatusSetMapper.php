<?php

namespace Tymy\Module\Attendance\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of StatusSetMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4. 11. 2020
 */
class StatusSetMapper extends BaseMapper
{
    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::string()->withPropertyAndColumn("name", true),
            Field::int()->withPropertyAndColumn("order"),
        ];
    }
}
