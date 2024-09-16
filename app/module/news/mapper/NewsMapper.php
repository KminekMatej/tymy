<?php

namespace Tymy\Module\News\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of NewsMapper
 */
class NewsMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::int()->withPropertyAndColumn("id", false, false),
            Field::datetime()->withColumn("inserted")->setProperty("created"),
            Field::string(100)->withPropertyAndColumn("caption"),
            Field::string()->withColumn("descr")->setProperty("description"),
            Field::string(3)->withColumn("lc")->setProperty("languageCode"),
            Field::string(50)->withPropertyAndColumn("team"),
        ];
    }
}
