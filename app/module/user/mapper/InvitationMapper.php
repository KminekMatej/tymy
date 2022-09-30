<?php

namespace Tymy\Module\User\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of InvitationMapper
 *
 * @author kminekmatej, 25. 9. 2022, 21:21:18
 */
class InvitationMapper extends BaseMapper
{

    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::datetime()->withPropertyAndColumn("created", false, false),
            Field::int()->withColumn("created_user_id", false, false)->setProperty("createdUserId"),
            Field::string()->withColumn("first_name")->setProperty("firstName"),
            Field::string()->withColumn("last_name")->setProperty("lastName"),
            Field::string()->withPropertyAndColumn("email"),
            Field::string()->withPropertyAndColumn("code"),
            Field::string()->withColumn("user_id")->setProperty("userId"),
            Field::string()->withColumn("valid_until")->setProperty("validUntil"),
        ];
    }
}
