<?php

namespace Tymy\Module\Settings\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of ICalItemMapper
 */
class ICalItemMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::datetime()->withPropertyAndColumn("created", false, false),
            Field::int()->withColumn("created_user_id", false, false)->setProperty("createdUserId"),
            Field::int()->withColumn("ical_id")->setProperty("icalId"),
            Field::int()->withColumn("status_id")->setProperty("statusId"),
        ];
    }
}
