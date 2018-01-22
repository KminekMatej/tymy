<?php

namespace Nette\Application\UI;

use Tapi\UserListResource;

/**
 * Description of Navbar
 *
 * @author matej
 */
class NewPostControl extends Control {

    /** @var UserListResource */
    private $userList;

    public function __construct() {
        parent::__construct();
    }

    public function setUserList(UserListResource $userList) {
        $this->userList = $userList;
        return $this;
    }

    public function render($discussion, $search = NULL, $suser = NULL) {

        $this->template->addFilter('czechize', function ($status) {
            return ["PLAYER" => "HRÁČI","MEMBER" => "ČLENOVÉ","SICK" => "MARODI"][$status];
        });

        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/newpost.latte');
        $template->discussion = $discussion;
        $template->search = $search;
        $template->suser = $suser;
        $this->userList->getData();
        $userList = $this->userList->getByTypesAndId();
        unset($userList["INIT"]);
        unset($userList["DELETED"]);
        $template->userList = $userList;
        $template->render();
    }
    

}
