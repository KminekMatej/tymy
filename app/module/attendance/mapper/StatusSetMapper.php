<?php

namespace Tymy\Module\Attendance\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of StatusSetMapper
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
            Field::string(50)->withPropertyAndColumn("name", true),
            Field::int()->withPropertyAndColumn("order"),
        ];
    }
}
