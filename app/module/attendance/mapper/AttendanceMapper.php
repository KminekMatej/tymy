<?php

namespace Tymy\Module\Attendance\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of AttendanceMapper
 */
class AttendanceMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withColumn("user_id")->setProperty("userId"),
            Field::int()->withColumn("event_id", true)->setProperty("eventId")->setNonempty(),
            Field::int()->withColumn("pre_status_id")->setProperty("preStatusId"),
            Field::string(255)->withColumn("pre_desc")->setProperty("preDescription"),
            Field::int()->withColumn("pre_usr_mod")->setProperty("preUserMod"),
            Field::datetime()->withColumn("pre_dat_mod")->setProperty("preDatMod"),
            Field::int()->withColumn("post_status_id")->setProperty("postStatusId"),
            Field::string(255)->withColumn("post_desc")->setProperty("postDescription"),
            Field::int()->withColumn("post_usr_mod")->setProperty("postUserMod"),
            Field::datetime()->withColumn("post_dat_mod")->setProperty("postDatMod"),
        ];
    }
}
