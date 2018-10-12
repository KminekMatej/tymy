<?php

namespace Nette\Application\UI;

use Nette;
use Tapi\DiscussionListResource;
use Tapi\EventListResource;
use Tapi\UserListResource;
use Tapi\UserDetailResource;
use Tapi\PollListResource;
use Tapi\NoteListResource;
use Tapi\Exception\APIException;


/**
 * Description of Navbar
 *
 * @author matej
 */
class NavbarControl extends Control {

    /** @var \App\Presenters\SecuredPresenter */
    private $presenter;

    /** @var DiscussionListResource */
    private $discussionList;

    /** @var PollListResource */
    private $polls;

    /** @var EventListResource */
    private $eventList;

    /** @var UserDetailResource */
    private $userDetail;

    /** @var UserListResource */
    private $userList;

    /** @var NoteListResource */
    private $noteList;

    /** @var \App\Model\Supplier */
    private $supplier;

    /** @var Nette\Security\User */
    private $presenterUser;
    
    private $accessibleSettings;

    public function __construct(\App\Presenters\SecuredPresenter $presenter) {
        parent::__construct();
        $this->presenter = $presenter;
        $this->discussionList = $presenter->discussionList;
        $this->noteList = $presenter->noteList;
        $this->polls = $this->presenter->polls;
        $this->eventList = $this->presenter->eventList;
        $this->userDetail = $this->presenter->userDetail;
        $this->userList = $this->presenter->userList;
        $this->supplier = $this->presenter->supplier;
        $this->presenterUser = $this->presenter->getUser();
        $this->accessibleSettings = $this->presenter->getAccessibleSettings();
    }
    
    private function notes() {
        try {
            $this->template->notes = $this->noteList->init()->setMenu(TRUE)->getData();
            $this->template->notesWarnings = $this->noteList->getWarnings();
        } catch (APIException $ex) {
            $this->presenter->handleTapiException($ex);
        }
    }

    private function discussions() {
        try {
            $this->template->discussions = $this->discussionList->init()->getData();
            $this->template->discussionWarnings = $this->discussionList->getWarnings();
        } catch (APIException $ex) {
            $this->presenter->handleTapiException($ex);
        }
    }

    private function players() {
        try {
            $this->userList->init()->getData();
        } catch (APIException $ex) {
            $this->presenter->handleTapiException($ex);
        }

        $this->template->counts = $this->userList->getCounts();
        $this->template->playersWarnings = $this->userList->getWarnings();
        $this->template->me = $this->userList->getMe();
    }

    private function polls() {
        try {
            $this->template->polls = $this->polls->init()->setMenu(TRUE)->getData();
            $this->template->voteWarnings = $this->polls->getWarnings();
            
        } catch (APIException $ex) {
            $this->presenter->handleTapiException($ex);
        }
    }

    private function events() {
        try {
            
            $this->template->events = $this->eventList->init()
                    ->setFrom(date("Ymd"))
                    ->setTo(date("Ymd", strtotime(" + 1 month")))
                    ->setOrder("startTime")
                    ->getData();
            $this->template->eventWarnings = 0;
            if (count($this->template->events)) {
                foreach ($this->template->events as $ev) {
                    if ($ev->warning)
                        $this->template->eventWarnings++;
                }
            }
        } catch (APIException $ex) {
            $this->presenter->handleTapiException($ex);
        }
        $this->template->eventColors = $this->supplier->getEventColors();
    }

    private function settings() {
        $this->template->accessibleSettings = $this->accessibleSettings;
    }

    public function render() {
        
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/navbar.latte');
        $this->template->levels = $this->presenter->getLevelCaptions();
        $this->template->presenterName = $this->presenter->getName();
        $this->template->action = $this->presenter->getAction();
        $this->template->tym = $this->supplier->getTym();
        $this->template->userId = $this->presenterUser->getId();

        //tapi discussions
        $this->discussions();
        //tapi players
        $this->players();
        //tapi events
        $this->events();
        //tapi polls
        $this->polls();
        //tapi settings
        $this->settings();
        //tapi notes
        $this->notes();

        $template->render();
    }

    public function handleRefresh() {
        if ($this->parent->isAjax()) {
            $this->redrawControl('nav');
        } else {
            $this->parent->redirect('this');
        }
    }
    
}
