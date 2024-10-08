<?php

namespace Tymy\Module\Event\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of EventMapper
 */
class EventMapper extends BaseMapper
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
            Field::string(255)->withPropertyAndColumn("caption", true),
            Field::int()->withColumn("event_type_id")->setProperty("eventTypeId"),
            Field::string()->withColumn("descr")->setProperty("description"),
            Field::datetime()->withColumn("close_time")->setProperty("closeTime"),
            Field::datetime()->withColumn("start_time", true)->setProperty("startTime"),
            Field::datetime()->withColumn("end_time")->setProperty("endTime"),
            Field::string(255)->withPropertyAndColumn("link"),
            Field::string(255)->withPropertyAndColumn("place"),
            Field::string(20)->withColumn("view_rights")->setProperty("viewRightName"),
            Field::string(20)->withColumn("plan_rights")->setProperty("planRightName"),
            Field::string(20)->withColumn("result_rights")->setProperty("resultRightName"),
        ];
    }
}
