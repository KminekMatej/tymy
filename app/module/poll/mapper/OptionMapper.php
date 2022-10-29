<?php

namespace Tymy\Module\Poll\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of OptionMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 20. 12. 2020
 */
class OptionMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::int()->withColumn("quest_id", true)->setProperty("pollId"),
            Field::string()->withPropertyAndColumn("caption"),
            Field::string()->withColumn("item_type", true)->setProperty("type"),
        ];
    }
}
