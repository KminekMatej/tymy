<?php

namespace Tymy\Module\PushNotification\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of SubscriberMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 14. 11. 2021
 */
class SubscriberMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::int()->withColumn("created")->setProperty("created")->setChangeable(false),
            Field::int()->withPropertyAndColumn("type"),
            Field::int()->withColumn("user_id", true)->setProperty("userId"),
            Field::string()->withPropertyAndColumn("subscription", true),
        ];
    }
}
