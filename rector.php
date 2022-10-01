<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app'
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::ACTION_INJECTION_TO_CONSTRUCTOR_INJECTION,
//        SetList::EARLY_RETURN,    //makes code a bit harder to read
//        SetList::PRIVATIZATION,   //makes every class final
//        SetList::CODING_STYLE,    //dont like the coding style, stick with phpcs
        LevelSetList::UP_TO_PHP_80,
    ]);
};
