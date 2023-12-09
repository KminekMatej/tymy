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
            Field::string(3)->withPropertyAndColumn("code", true),
            Field::string(6)->withPropertyAndColumn("color"),
            Field::string(32)->withPropertyAndColumn("icon"),
            Field::string(50)->withPropertyAndColumn("caption", true),
            Field::int()->withColumn("status_set_id", true)->setProperty("statusSetId"),
            Field::int()->withColumn("updated_user_id")->setProperty("updatedById"),
            Field::datetime()->withColumn("updated")->setProperty("updatedAt"),
            Field::int()->withPropertyAndColumn("order"),

        ];
    }
}
