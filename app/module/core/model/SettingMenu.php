<?php

namespace Tymy\Module\Core\Model;

/**
 * Object setting accessible configuration area
 */
class SettingMenu
{
    public function __construct(public string $code, public ?string $name = null, public ?string $href = null, public ?string $icon = null, public bool $enabled = true)
    {
    }
}
