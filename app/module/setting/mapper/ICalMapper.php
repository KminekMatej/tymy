<?php

namespace Tymy\Module\Settings\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of ICalMapper
 */
class ICalMapper extends BaseMapper
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
            Field::int()->withColumn("user_id", true)->setProperty("userId"),
            Field::string(32)->withPropertyAndColumn("hash"),
            Field::int()->withColumn("enabled")->setProperty("enabled"),
        ];
    }
}
