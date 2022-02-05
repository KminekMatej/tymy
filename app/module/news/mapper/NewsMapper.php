<?php

namespace Tymy\Module\News\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of NewsMapper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 21. 02. 2021
 */
class NewsMapper extends BaseMapper
{
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::datetime()->withColumn("inserted")->setProperty("created", false, false),
            Field::string()->withPropertyAndColumn("caption"),
            Field::string()->withColumn("descr")->setProperty("description"),
            Field::string()->withColumn("lc")->setProperty("languageCode"),
            Field::string()->withPropertyAndColumn("team"),
        ];
    }
}
