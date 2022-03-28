<?php

namespace Tymy\Module\User\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of UserMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4.8. 2020
 */
class UserMapper extends BaseMapper
{
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::string()->withColumn("user_name", true)->setProperty("login"),
            Field::int()->withColumn("can_login")->setProperty("canLogin"),
            Field::int()->withColumn("editable_call_name")->setProperty("canEditCallName"),
            Field::datetime()->withColumn("created_at", false, true)->setProperty("createdAt"),
            Field::datetime()->withColumn("last_login", false, true)->setProperty("lastLogin"),
            Field::string()->withPropertyAndColumn("status"),
            Field::string()->withPropertyAndColumn("roles"),
            Field::string()->withColumn("first_name")->setProperty("firstName"),
            Field::string()->withColumn("last_name")->setProperty("lastName"),
            Field::string()->withColumn("call_name", true)->setProperty("callName"),
            Field::string()->withPropertyAndColumn("language"),
            Field::string()->withColumn("jersey_number")->setProperty("jerseyNumber"),
            Field::string()->withColumn("sex")->setProperty("gender"),
            Field::string()->withPropertyAndColumn("street"),
            Field::string()->withPropertyAndColumn("city"),
            Field::string()->withColumn("zipcode")->setProperty("zipCode"),
            Field::string()->withPropertyAndColumn("phone"),
            Field::string()->withPropertyAndColumn("phone2"),
            Field::datetime()->withColumn("birth_date")->setProperty("birthDate"),
            Field::int()->withPropertyAndColumn("nameday_month")->setProperty("nameDayMonth"),
            Field::int()->withPropertyAndColumn("nameday_day")->setProperty("nameDayDay"),
            Field::string()->withColumn("account_number")->setProperty("accountNumber"),
            Field::string()->withColumn("birth_code")->setProperty("birthCode"),
            Field::string()->withColumn("password")->setProperty("password"),
            Field::datetime()->withColumn("gdpr_accepted_at")->setProperty("gdprAccepted")->setChangeable(false),
            Field::datetime()->withColumn("gdpr_revoked_at")->setProperty("gdprRevoked")->setChangeable(false),
            Field::datetime()->withColumn("last_read_news")->setProperty("lastReadNews")->setChangeable(false),
            Field::string()->withPropertyAndColumn("skin"),
            Field::int()->withColumn("hide_disc_desc")->setProperty("hideDiscDesc"),
        ];
    }
}
