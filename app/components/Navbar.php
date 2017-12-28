<?php

namespace Nette\Application\UI;

use Nette;
use Tapi\DiscussionListResource;
use Tapi\EventListResource;


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

    /** @var \Tymy\Polls */
    private $polls;

    /** @var EventListResource */
    private $eventList;

    /** @var \Tymy\User */
    private $user;

    /** @var \Tymy\Users */
    private $users;

    /** @var \App\Model\Supplier */
    private $supplier;

    /** @var Nette\Security\User */
    private $presenterUser;
    
    private $accessibleSettings;

    public function __construct(\App\Presenters\SecuredPresenter $presenter) {
        parent::__construct();
        $this->presenter = $presenter;
        $this->discussionList = \Tapi\DiscussionResource::mergeListWithNews($presenter->discussionList, $presenter->discussionNews);
        $this->polls = $this->presenter->polls;
        $this->eventList = $this->presenter->eventList;
        $this->user = $this->presenter->user;
        $this->users = $this->presenter->users;
        $this->supplier = $this->presenter->supplier;
        $this->presenterUser = $this->presenter->getUser();
        $this->accessibleSettings = $this->presenter->getAccessibleSettings();
    }

    private function discussions() {
        try {
            $this->template->discussionWarnings = $this->discussionList->getWarnings();
            $this->template->discussions = $this->discussionList->getData();
        } catch (Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
    }

    private function players() {
        try {
            $players = $this->users->reset()->getResult(TRUE);
        } catch (Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }


        $this->template->counts = $players->counts;
        $this->template->playersWarnings = $players->menuWarningCount;
        $this->template->me = $players->me;
    }

    private function polls() {
        try {
            $this->template->voteWarnings = $this->polls->reset()->setMenu(TRUE)->getResult()->menuWarningCount;
            $this->template->polls = $this->polls->getData();
        } catch (Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
    }

    private function events() {
        try {
            $this->template->events = $this->eventList
                    ->setFrom(date("Ymd"))
                    ->setTo(date("Ymd", strtotime(" + 1 month")))
                    ->setOrder("startTime")
                    ->getData();
            $this->template->eventWarnings = $this->eventList->getWarnings();
            
        } catch (Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
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
