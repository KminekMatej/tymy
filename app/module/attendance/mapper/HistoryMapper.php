<?php

namespace Tymy\Module\Attendance\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of HistoryMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 19. 9. 2020
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
            Field::string()->withColumn("created")->setProperty("updatedAt"),
            Field::int()->withColumn("updated_user_id")->setProperty("updatedById"),
            Field::int()->withColumn("status_id_from")->setProperty("statusIdFrom"),
            Field::int()->withColumn("status_id_to", true)->setProperty("statusIdTo"),
            Field::string()->withColumn("pre_desc_to")->setProperty("preDescTo"),
            Field::string()->withColumn("entry_type")->setProperty("entryType"),
            Field::string()->withColumn("pre_desc_from")->setProperty("preDescFrom"),
        ];
    }
}
