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
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::string()->withPropertyAndColumn("code", true),
            Field::string()->withPropertyAndColumn("color"),
            Field::string()->withPropertyAndColumn("icon"),
            Field::string()->withPropertyAndColumn("caption", true),
            Field::int()->withColumn("status_set_id", true)->setProperty("statusSetId"),
            Field::int()->withColumn("updated_user_id")->setProperty("updatedById"),
            Field::datetime()->withColumn("updated")->setProperty("updatedAt"),
            Field::int()->withPropertyAndColumn("order"),

        ];
    }
}
