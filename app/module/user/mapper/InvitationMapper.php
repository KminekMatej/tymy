<?php

namespace Tymy\Module\User\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of InvitationMapper
 */
class InvitationMapper extends BaseMapper
{
    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::datetime()->withPropertyAndColumn("created", false, false),
            Field::int()->withColumn("created_user_id", false, false)->setProperty("createdUserId"),
            Field::string(20)->withColumn("first_name")->setProperty("firstName"),
            Field::string(20)->withColumn("last_name")->setProperty("lastName"),
            Field::string(50)->withPropertyAndColumn("email"),
            Field::string(32)->withPropertyAndColumn("code"),
            Field::string(2)->withPropertyAndColumn("lang"),
            Field::int()->withColumn("user_id")->setProperty("userId"),
            Field::datetime()->withColumn("valid_until")->setProperty("validUntil"),
        ];
    }
}
