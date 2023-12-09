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
            Field::string()->withColumn("country_id")->setProperty("countryId"),
            Field::string()->withColumn("max_users")->setProperty("maxUsers"),
            Field::string()->withColumn("max_events_month")->setProperty("maxEventsMonth"),
            Field::string()->withColumn("insert_date")->setProperty("insertDate"),
            Field::string()->withColumn("time_zone")->setProperty("timeZone"),
            Field::string()->withColumn("dst_flag")->setProperty("dstFlag"),
            Field::string()->withColumn("att_check")->setProperty("attCheckType"),
            Field::string()->withColumn("att_check_days")->setProperty("attendanceCheckDays"),
            Field::string()->withPropertyAndColumn("tariff"),
            Field::string(32)->withPropertyAndColumn("skin"),
            Field::string(255)->withColumn("required_fields")->setProperty("requiredFields"),
            Field::string()->withColumn("tariff_until")->setProperty("tariffUntil"),
            Field::string()->withColumn("tariff_payment")->setProperty("tariffPayment"),
            Field::string(255)->withColumn("required_fields")->setProperty("requiredFields"),
        ];
    }
}
