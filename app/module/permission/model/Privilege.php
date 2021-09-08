<?php

namespace Tymy\Module\Permission\Model;

/**
 * Description of Privilege
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 6. 9. 2020
 */
class Privilege
{
    private string $type;
    private string $name;

    private function __construct(string $type, string $name)
    {
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * Returns a SYS privilege
     * @param string $name Name of permission
     * @return Privilege|null
     */
    public static function SYS(?string $name)
    {
        return $name ? (new Privilege(Permission::TYPE_SYSTEM, $name)) : null;
    }

    /**
     * Returns a USR privilege
     * @param string $name Name of permission
     * @return Privilege|null
     */
    public static function USR(?string $name)
    {
        return $name ? (new Privilege(Permission::TYPE_USER, $name)) : null;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
