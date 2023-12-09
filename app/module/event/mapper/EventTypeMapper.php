<?php

namespace Tymy\Module\Event\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of EventTypeMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 8. 10. 2020
 */
class EventTypeMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::string(3)->withPropertyAndColumn("code", true),
            Field::string(255)->withPropertyAndColumn("caption"),
            Field::string(6)->withPropertyAndColumn("color"),
            Field::int()->withColumn("pre_status_set_id")->setProperty("preStatusSetId"),
            Field::int()->withColumn("post_status_set_id")->setProperty("postStatusSetId"),
            Field::string()->withPropertyAndColumn("mandatory")->setEnum(['FREE', 'WARN', 'MUST']),
            Field::datetime()->withColumn("updated")->setProperty("updatedAt")->setChangeable(false),
            Field::int()->withColumn("updated_user_id")->setProperty("updatedById"),
            Field::int()->withPropertyAndColumn("order"),
        ];
    }
}
