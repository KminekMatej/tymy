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
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::int()->withColumn("dat_cre")->setProperty("createdAt")->setChangeable(false),
            Field::int()->withColumn("usr_cre")->setProperty("createdById")->setChangeable(false),
            Field::int()->withColumn("usr_mod")->setProperty("updatedById")->setChangeable(false),
            Field::datetime()->withColumn("dat_mod")->setProperty("updatedAt")->setChangeable(false),
            Field::string()->withPropertyAndColumn("caption", true),
            Field::string()->withColumn("descr")->setProperty("description"),
            Field::string()->withColumn("read_rights")->setProperty("readRightName"),
            Field::string()->withColumn("write_rights")->setProperty("writeRightName"),
            Field::string()->withColumn("del_rights")->setProperty("deleteRightName"),
            Field::string()->withColumn("sticky_rights")->setProperty("stickyRightName"),
            Field::int()->withColumn("public_read")->setProperty("publicRead"),
            Field::int()->withColumn("can_modify")->setProperty("editablePosts"),
            Field::int()->withColumn("order_flag")->setProperty("order"),
        ];
    }
}
