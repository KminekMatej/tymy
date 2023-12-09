<?php

namespace Tymy\Module\Team\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of TeamMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
class TeamMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::string(30)->withPropertyAndColumn("name", true),
            Field::string(30)->withColumn("sys_name")->setProperty("sysName"),
            Field::string(50)->withColumn("db_name")->setProperty("dbName"),
            Field::string(30)->withPropertyAndColumn("languages"),
            Field::string(3)->withColumn("default_lc")->setProperty("defaultLanguageCode"),
            Field::string(30)->withPropertyAndColumn("sport"),
            Field::string(32)->withColumn("account_number")->setProperty("accountNumber"),
            Field::string(50)->withPropertyAndColumn("web"),
            Field::int()->withColumn("country_id")->setProperty("countryId"),
            Field::int()->withColumn("max_users")->setProperty("maxUsers"),
            Field::int()->withColumn("max_events_month")->setProperty("maxEventsMonth"),
            Field::datetime()->withColumn("insert_date")->setProperty("insertDate"),
            Field::int()->withColumn("time_zone")->setProperty("timeZone"),
            Field::string()->withColumn("dst_flag")->setProperty("dstFlag")->setEnum(['YES', 'NO', 'AUTO']),
            Field::string()->withColumn("att_check")->setProperty("attCheckType")->setEnum(['NO', 'MESSAGE', 'FW']),
            Field::int()->withColumn("att_check_days")->setProperty("attendanceCheckDays"),
            Field::string()->withPropertyAndColumn("tariff")->setEnum(['FREE', 'LITE', 'FULL', 'FREE-LITE', 'FREE-FULL']),
            Field::string(32)->withPropertyAndColumn("skin"),
            Field::string(255)->withColumn("required_fields")->setProperty("requiredFields"),
            Field::date()->withColumn("tariff_until")->setProperty("tariffUntil"),
            Field::string()->withColumn("tariff_payment")->setProperty("tariffPayment")->setEnum(['MONTHLY', 'QUARTERLY', 'YEARLY', 'OTHER']),
            Field::string(255)->withColumn("required_fields")->setProperty("requiredFields"),
        ];
    }
}
