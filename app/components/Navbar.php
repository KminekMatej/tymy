<?php

namespace Nette\Application\UI;

use Nette;

/**
 * Description of Navbar
 *
 * @author matej
 */
class NavbarControl extends Control {

    /** @var \App\Presenters\SecuredPresenter */
    private $presenter;

    /** @var \Tymy\Discussions */
    private $discussions;

    /** @var \Tymy\Polls */
    private $polls;

    /** @var \Tymy\Events */
    private $events;

    /** @var \Tymy\User */
    private $user;

    /** @var \Tymy\Users */
    private $users;

    /** @var \App\Model\Supplier */
    private $supplier;

    /** @var Nette\Security\User */
    private $presenterUser;

    public function __construct(\App\Presenters\SecuredPresenter $presenter) {
        parent::__construct();
        $this->presenter = $presenter;
        $this->discussions = $this->presenter->discussions;
        $this->polls = $this->presenter->polls;
        $this->events = $this->presenter->events;
        $this->user = $this->presenter->user;
        $this->users = $this->presenter->users;
        $this->supplier = $this->presenter->supplier;
        $this->presenterUser = $this->presenter->getUser();
    }

    private function discussions() {
        try {
            $discussionsResult = $this->discussions->reset()->setWithNew(true)->getResult();
            $this->template->discussionWarnings = $discussionsResult->menuWarningCount;
            $this->template->discussions = (object) $this->discussions->getData();
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
            $this->template->polls = (object) $this->polls->getData();
        } catch (Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
    }

    private function events() {
        try {
            $this->events
                    ->reset()
                    ->setWithMyAttendance(true)
                    ->setFrom(date("Ymd"))
                    ->setTo(date("Ymd", strtotime(" + 1 month")))
                    ->setOrder("startTime")
                    ->getData();
            $this->template->eventWarnings = $this->events->getResult()->menuWarningCount;
            $this->template->events = (object) $this->events->getData();
        } catch (Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
    }

    private function settings() {
        //not used now, maybe later
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
        //$this->settings();

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
