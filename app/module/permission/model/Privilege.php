<?php

namespace Tymy\Module\Permission\Model;

/**
 * Description of Privilege
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 6. 9. 2020
 */
class Privilege
{
    private function __construct(private string $type, private string $name)
    {
    }

    /**
     * Returns a SYS privilege
     * @param string|null $name Name of permission
     */
    public static function SYS(?string $name): ?\Tymy\Module\Permission\Model\Privilege
    {
        return $name ? (new Privilege(Permission::TYPE_SYSTEM, $name)) : null;
    }

    /**
     * Returns a USR privilege
     * @param string|null $name Name of permission
     */
    public static function USR(?string $name): ?\Tymy\Module\Permission\Model\Privilege
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
