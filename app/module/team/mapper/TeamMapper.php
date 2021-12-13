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
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::string()->withPropertyAndColumn("name", true),
            Field::string()->withColumn("sys_name")->setProperty("sysName"),
            Field::string()->withColumn("db_name")->setProperty("dbName"),
            Field::string()->withPropertyAndColumn("languages"),
            Field::string()->withColumn("default_lc")->setProperty("defaultLanguageCode"),
            Field::string()->withPropertyAndColumn("sport"),
            Field::string()->withColumn("account_number")->setProperty("accountNumber"),
            Field::string()->withPropertyAndColumn("web"),
            Field::string()->withColumn("country_id")->setProperty("countryId"),
            Field::string()->withPropertyAndColumn("modules"),
            Field::string()->withColumn("max_users")->setProperty("maxUsers"),
            Field::string()->withColumn("max_events_month")->setProperty("maxEventsMonth"),
            Field::string()->withPropertyAndColumn("advertisement"),
            Field::string()->withColumn("insert_date")->setProperty("insertDate"),
            Field::string()->withColumn("time_zone")->setProperty("timeZone"),
            Field::string()->withColumn("dst_flag")->setProperty("dstFlag"),
            Field::string()->withColumn("use_namedays")->setProperty("useNamedays"),
            Field::string()->withColumn("att_check")->setProperty("attCheckType"),
            Field::string()->withColumn("att_check_days")->setProperty("attendanceCheckDays"),
            Field::string()->withPropertyAndColumn("host"),
            Field::string()->withPropertyAndColumn("tariff"),
            Field::string()->withPropertyAndColumn("skin"),
            Field::string()->withColumn("tariff_until")->setProperty("tariffUntil"),
            Field::string()->withColumn("tariff_payment")->setProperty("tariffPayment"),
        ];
    }
}
