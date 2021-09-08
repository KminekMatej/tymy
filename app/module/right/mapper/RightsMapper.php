<?php

namespace Tymy\Module\Right\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of RightMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4.8. 2020
 */
class RightMapper extends BaseMapper
{
    public static function scheme(): array
    {
        return [
            Field::string()->withColumn("name", true, false)->setProperty("type"),
            Field::string()->withColumn("caption", true, false)->setProperty("name"),
        ];
    }
}
