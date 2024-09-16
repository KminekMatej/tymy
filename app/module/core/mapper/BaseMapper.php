<?php

namespace Tymy\Module\Core\Mapper;

use Tymy\Module\Core\Model\Field;

/**
 * BaseMapper - parent function for all mappers
 */
abstract class BaseMapper
{
    /** @return Field[] */
    abstract public static function scheme(): array;
}
