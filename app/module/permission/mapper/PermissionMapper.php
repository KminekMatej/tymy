<?php

namespace Tymy\Module\Permission\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of PermissionMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4.8. 2020
 */
class PermissionMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::string()->withColumn("right_type", true, false)->setProperty("type")->setEnum(['SYS', 'PAGE', 'USR']),
            Field::string(20)->withPropertyAndColumn("name", true),
            Field::string(255)->withPropertyAndColumn("caption"),
            Field::string(100)->withColumn("a_roles")->setProperty("allowedRoles"),
            Field::string(100)->withColumn("r_roles")->setProperty("revokedRoles"),
            Field::string(100)->withColumn("a_statuses")->setProperty("allowedStatuses"),
            Field::string(100)->withColumn("r_statuses")->setProperty("revokedStatuses"),
            Field::string(1000)->withColumn("a_users")->setProperty("allowedUsers"),
            Field::string(1000)->withColumn("r_users")->setProperty("revokedUsers"),
            Field::datetime()->withColumn("updated", false, false)->setProperty("updatedAt"),
            Field::int()->withColumn("updated_user_id", false, false)->setProperty("updatedById"),
        ];
    }
}
