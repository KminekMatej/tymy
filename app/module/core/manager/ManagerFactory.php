<?php

namespace Tymy\Module\Core\Factory;

use Nette\Database\Explorer;
use Nette\Security\User;
use Tymy\Module\Core\Manager\Responder;

/**
 * Description of ManagerFactory
 */
class ManagerFactory
{
    public function __construct(public Explorer $mainDatabase, public Explorer $teamDatabase, public string $teamSysName, public Responder $responder, public User $user)
    {
    }
}
