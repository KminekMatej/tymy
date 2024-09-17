<?php

namespace Tymy\Module\User\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of UserMapper
 */
class UserMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::string(20)->withColumn("user_name", true)->setProperty("login"),
            Field::string(1023)->withPropertyAndColumn("email"),
            Field::int()->withColumn("can_login")->setProperty("canLogin"),
            Field::int()->withColumn("editable_call_name")->setProperty("canEditCallName"),
            Field::datetime()->withColumn("created_at", false, true)->setProperty("createdAt"),
            Field::datetime()->withColumn("last_login", false, true)->setProperty("lastLogin"),
            Field::string()->withPropertyAndColumn("status")->setEnum(['INIT', 'PLAYER', 'MEMBER', 'SICK']),
            Field::string(40)->withPropertyAndColumn("roles"),
            Field::string(20)->withColumn("first_name")->setProperty("firstName"),
            Field::string(20)->withColumn("last_name")->setProperty("lastName"),
            Field::string(30)->withColumn("call_name", true)->setProperty("callName"),
            Field::string(3)->withPropertyAndColumn("language"),
            Field::string(255)->withColumn("jersey_number")->setProperty("jerseyNumber"),
            Field::string()->withColumn("sex")->setProperty("gender")->setEnum(['male', 'female', 'unknown']),
            Field::string(40)->withPropertyAndColumn("street"),
            Field::string(40)->withPropertyAndColumn("city"),
            Field::string(12)->withColumn("zipcode")->setProperty("zipCode"),
            Field::string(25)->withPropertyAndColumn("phone"),
            Field::string(35)->withPropertyAndColumn("phone2"),
            Field::date()->withColumn("birth_date")->setProperty("birthDate"),
            Field::int()->withPropertyAndColumn("nameday_month")->setProperty("nameDayMonth"),
            Field::int()->withPropertyAndColumn("nameday_day")->setProperty("nameDayDay"),
            Field::string(32)->withColumn("account_number")->setProperty("accountNumber"),
            Field::string(16)->withColumn("birth_code")->setProperty("birthCode"),
            Field::string(40)->withColumn("password")->setProperty("password"),
            Field::datetime()->withColumn("gdpr_accepted_at")->setProperty("gdprAccepted")->setChangeable(false),
            Field::datetime()->withColumn("gdpr_revoked_at")->setProperty("gdprRevoked")->setChangeable(false),
            Field::datetime()->withColumn("last_read_news")->setProperty("lastReadNews")->setChangeable(false),
            Field::string(32)->withPropertyAndColumn("skin"),
            Field::int()->withColumn("hide_disc_desc")->setProperty("hideDiscDesc"),
        ];
    }
}
