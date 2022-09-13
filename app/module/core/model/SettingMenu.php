<?php

namespace Tymy\Module\Core\Model;

/**
 * Object setting accessible configuration area
 *
 * @author kminekmatej
 */
class SettingMenu
{
    public string $code;
    public ?string $name = null;
    public ?string $href = null;
    public ?string $icon = null;
    public bool $enabled = true;

    public function __construct(string $code, ?string $name = null, ?string $href = null, ?string $icon = null, bool $enabled = true)
    {
        $this->code = $code;
        $this->name = $name;
        $this->href = $href;
        $this->icon = $icon;
        $this->enabled = $enabled;
    }
}
