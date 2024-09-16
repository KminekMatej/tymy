<?php

namespace Tymy\Module\Multiaccount\Mapper;

use Tymy\Module\Core\Mapper\BaseMapper;
use Tymy\Module\Core\Model\Field;

/**
 * Description of TransferKeyMapper
 */
class TransferKeyMapper extends BaseMapper
{
    /**
     * @return mixed[]
     */
    public static function scheme(): array
    {
        return [
            Field::string()->withColumn("user_id")->setProperty("uid"),
            Field::string()->withColumn("transfer_key")->setProperty("transferKey"),
        ];
    }
}
