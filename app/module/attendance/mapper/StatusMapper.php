<?php

namespace Tymy\Module\Attendance\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of StatusMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4. 11. 2020
 */
class StatusMapper extends BaseMapper
{
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::string()->withPropertyAndColumn("code", true),
            Field::string()->withPropertyAndColumn("color"),
            Field::string()->withPropertyAndColumn("caption", true),
            Field::int()->withColumn("set_id", true)->setProperty("statusSetId"),
            Field::int()->withColumn("usr_mod")->setProperty("updatedById"),
            Field::int()->withColumn("dat_mod")->setProperty("updatedAt"),
        ];
    }
}
