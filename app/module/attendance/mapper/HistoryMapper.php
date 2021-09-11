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
    public static function scheme(): array
    {
        return [
            Field::string()->withColumn("event_id", true)->setProperty("eventId"),
            Field::string()->withColumn("user_id", true)->setProperty("userId"),
            Field::string()->withColumn("dat_mod")->setProperty("updatedAt"),
            Field::string()->withColumn("usr_mod")->setProperty("updatedById"),
            Field::string()->withColumn("pre_status_to", true)->setProperty("preStatusTo"),
            Field::string()->withColumn("pre_desc_to")->setProperty("preDescTo"),
            Field::string()->withColumn("entry_type")->setProperty("entryType"),
            Field::string()->withColumn("pre_status_from")->setProperty("preStatusFrom"),
            Field::string()->withColumn("pre_desc_from")->setProperty("preDescFrom"),
        ];
    }
}