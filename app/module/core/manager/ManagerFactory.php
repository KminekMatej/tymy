<?php

namespace Tymy\Module\Core\Factory;

use Nette\Database\Explorer;
use Nette\Security\User;
use Tymy\Module\Core\Manager\Responder;

/**
 * Description of ManagerFactory
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
class ManagerFactory
{
    public Explorer $mainDatabase;
    public Explorer $teamDatabase;
    public Responder $responder;
    public User $user;
    public string $teamSysName;

    public function __construct(Explorer $mainDatabase, Explorer $teamDatabase, string $teamSysName, Responder $responder, User $user)
    {
        $this->mainDatabase = $mainDatabase;
        $this->teamDatabase = $teamDatabase;
        $this->teamSysName = $teamSysName;
        $this->responder = $responder;
        $this->user = $user;
    }
}
