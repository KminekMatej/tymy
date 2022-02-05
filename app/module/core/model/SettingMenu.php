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
    public string $name;
    public string $href;
    public string $icon;
    public bool $enabled;

    public function __construct(string $code, string $name, string $href, string $icon, bool $enabled)
    {
        $this->code = $code;
        $this->name = $name;
        $this->href = $href;
        $this->icon = $icon;
        $this->enabled = $enabled;
    }
}
