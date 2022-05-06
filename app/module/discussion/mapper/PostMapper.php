<?php

namespace Tymy\Module\Discussion\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of PostMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
class PostMapper extends BaseMapper
{
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::int()->withColumn("discussion_id")->setProperty("discussionId"),
            Field::string()->withColumn("item", true)->setProperty("post")->setNonempty(),
            Field::int()->withColumn("user_id")->setProperty("createdById")->setChangeable(false),
            Field::datetime()->withColumn("insert_date")->setProperty("createdAt")->setChangeable(false),
            Field::datetime()->withColumn("dat_mod")->setProperty("updatedAt"),
            Field::int()->withColumn("usr_mod")->setProperty("updatedById"),
            Field::int()->withPropertyAndColumn("sticky"),
        ];
    }
}
