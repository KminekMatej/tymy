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
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::string()->withColumn("right_type", true, false)->setProperty("type"),
            Field::string()->withPropertyAndColumn("name", true),
            Field::string()->withPropertyAndColumn("caption"),
            Field::string()->withColumn("a_roles")->setProperty("allowedRoles"),
            Field::string()->withColumn("r_roles")->setProperty("revokedRoles"),
            Field::string()->withColumn("a_statuses")->setProperty("allowedStatuses"),
            Field::string()->withColumn("r_statuses")->setProperty("revokedStatuses"),
            Field::string()->withColumn("a_users")->setProperty("allowedUsers"),
            Field::string()->withColumn("r_users")->setProperty("revokedUsers"),
            Field::datetime()->withColumn("dat_mod", false, false)->setProperty("updatedAt"),
            Field::int()->withColumn("usr_mod", false, false)->setProperty("updatedById"),
        ];
    }
}
