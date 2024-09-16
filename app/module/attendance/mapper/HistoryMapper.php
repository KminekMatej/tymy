<?php

namespace Tymy\Module\Attendance\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of HistoryMapper
 */
class HistoryMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withColumn("event_id", true)->setProperty("eventId"),
            Field::int()->withColumn("user_id", true)->setProperty("userId"),
            Field::datetime()->withColumn("created")->setProperty("updatedAt"),
            Field::int()->withColumn("updated_user_id")->setProperty("updatedById"),
            Field::int()->withColumn("status_id_from")->setProperty("statusIdFrom"),
            Field::int()->withColumn("status_id_to", true)->setProperty("statusIdTo"),
            Field::string(255)->withColumn("pre_desc_to")->setProperty("preDescTo"),
            Field::string(3)->withColumn("entry_type")->setProperty("entryType"),
            Field::string(255)->withColumn("pre_desc_from")->setProperty("preDescFrom"),
        ];
    }
}
