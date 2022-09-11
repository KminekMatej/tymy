<?php

namespace Tymy\Module\Settings\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of ICalMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 11. 9. 2022, 15:49:12
 */
class ICalMapper extends BaseMapper
{

    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::datetime()->withPropertyAndColumn("created", false, false),
            Field::int()->withColumn("created_user_id")->setProperty("createdUserId"),
            Field::int()->withColumn("user_id", true)->setProperty("userId"),
            Field::string()->withPropertyAndColumn("hash"),
            Field::int()->withColumn("enabled")->setProperty("enabled"),
        ];
    }
}
