<?php

namespace Tymy\Module\Multiaccount\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of TransferKeyMapper
 *
 * @author kminekmatej, 18. 11. 2021, 23:33:50
 */
class TransferKeyMapper extends BaseMapper
{
    public static function scheme(): array
    {
        return [
            Field::string()->withColumn("user_id")->setProperty("uid"),
            Field::string()->withColumn("transfer_key")->setProperty("transferKey"),
        ];
    }
}
