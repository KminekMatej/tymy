<?php

namespace Tymy\Module\Discussion\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of DiscussionMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
class DiscussionMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::datetime()->withColumn("created")->setProperty("createdAt")->setChangeable(false),
            Field::int()->withColumn("created_user_id")->setProperty("createdById")->setChangeable(false),
            Field::int()->withColumn("updated_user_id")->setProperty("updatedById")->setChangeable(false),
            Field::datetime()->withColumn("updated")->setProperty("updatedAt")->setChangeable(false),
            Field::string(255)->withPropertyAndColumn("caption", true),
            Field::string()->withColumn("descr")->setProperty("description"),
            Field::string(20)->withColumn("read_rights")->setProperty("readRightName"),
            Field::string(20)->withColumn("write_rights")->setProperty("writeRightName"),
            Field::string(20)->withColumn("del_rights")->setProperty("deleteRightName"),
            Field::string(20)->withColumn("sticky_rights")->setProperty("stickyRightName"),
            Field::int()->withColumn("public_read")->setProperty("publicRead"),
            Field::int()->withColumn("can_modify")->setProperty("editablePosts"),
            Field::int()->withColumn("order_flag")->setProperty("order"),
        ];
    }
}
