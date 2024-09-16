<?php

namespace Tymy\Module\Poll\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of VoteMapper
 */
class VoteMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withColumn("quest_id", true)->setProperty("pollId"),
            Field::int()->withColumn("user_id", true)->setProperty("userId"),
            Field::int()->withColumn("item_id", true)->setProperty("optionId"),
            Field::string(500)->withColumn("text_value")->setProperty("stringValue"),
            Field::float()->withColumn("numeric_value")->setProperty("numericValue"),
            Field::int()->withColumn("boolean_value")->setProperty("booleanValue"),
            Field::int()->withColumn("updated_user_id")->setProperty("updatedById")->setChangeable(false),
            Field::datetime()->withColumn("updated")->setProperty("updatedAt")->setChangeable(false),
        ];
    }
}
