<?php

namespace Tymy\Module\PushNotification\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of SubscriberMapper
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
            Field::datetime()->withColumn("created")->setProperty("created")->setChangeable(false),
            Field::string()->withPropertyAndColumn("type")->setEnum(['WEB', 'APNS', 'FCM']),
            Field::int()->withColumn("user_id", true)->setProperty("userId"),
            Field::string()->withPropertyAndColumn("subscription", true),
        ];
    }
}
