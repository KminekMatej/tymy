<?php

namespace Tymy\Module\Core\Component;

use Nette\Application\UI\Control;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of Navbar
 *
 * @author matej
 */
class NewPostControl extends Control
{
    public function __construct(private UserManager $userManager)
    {
    }

    public function render($discussion, $search = null, $suser = null): void
    {

        $this->template->addFilter('czechize', fn($status) => ["PLAYER" => "HRÁČI", "MEMBER" => "ČLENOVÉ", "SICK" => "MARODI"][$status]);

        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/newpost.latte');
        $template->discussion = $discussion;
        $template->search = $search;
        $template->suser = $suser;
        $userList = $this->userManager->getByStatusAndId();
        unset($userList["INIT"]);
        unset($userList["DELETED"]);
        $template->userList = $userList;
        $template->render();
    }
}
