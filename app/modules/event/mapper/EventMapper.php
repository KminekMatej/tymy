<?php

namespace Tymy\Module\Event\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of EventMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 19. 9. 2020
 */
class EventMapper extends BaseMapper
{
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::string()->withPropertyAndColumn("caption", true),
            Field::string()->withPropertyAndColumn("type", true),
            Field::string()->withColumn("descr")->setProperty("description"),
            Field::string()->withColumn("close_time")->setProperty("closeTime"),
            Field::string()->withColumn("start_time", true)->setProperty("startTime"),
            Field::string()->withColumn("end_time")->setProperty("endTime"),
            Field::string()->withPropertyAndColumn("link"),
            Field::string()->withPropertyAndColumn("place"),
            Field::string()->withColumn("view_rights")->setProperty("viewRightName"),
            Field::string()->withColumn("plan_rights")->setProperty("planRightName"),
            Field::string()->withColumn("result_rights")->setProperty("resultRightName"),
        ];
    }
}
