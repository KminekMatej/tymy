<?php

namespace Nette\Application\UI;

use App\Model\Supplier;
use App\Presenters\SecuredPresenter;
use Nette;
use Tapi\DebtListResource;
use Tapi\DiscussionListResource;
use Tapi\EventListResource;
use Tapi\Exception\APIException;
use Tapi\IsResource;
use Tapi\MultiaccountListResource;
use Tapi\NoteListResource;
use Tapi\PollListResource;
use Tapi\UserDetailResource;
use Tapi\UserListResource;


/**
 * Description of Navbar
 *
 * @author matej
 */
class NavbarControl extends Control {

    /** @var SecuredPresenter */
    private $presenter;

    /** @var DiscussionListResource */
    private $discussionList;

    /** @var PollListResource */
    private $polls;

    /** @var EventListResource */
    private $eventList;

    /** @var DebtListResource */
    private $debtList;

    /** @var UserDetailResource */
    private $userDetail;

    /** @var UserListResource */
    private $userList;

    /** @var NoteListResource */
    private $noteList;

    /** @var MultiaccountListResource */
    private $maList;
    
    /** @var IsResource */
    private $is;
    
    /** @var Supplier */
    private $supplier;

    /** @var Nette\Security\User */
    private $presenterUser;
    
    private $accessibleSettings;

    public function __construct(SecuredPresenter $presenter) {
        parent::__construct();
        $this->presenter = $presenter;
        $this->discussionList = $presenter->discussionList;
        $this->noteList = $presenter->noteList;
        $this->polls = $this->presenter->polls;
        $this->eventList = $this->presenter->eventList;
        $this->debtList = $this->presenter->debtList;
        $this->userDetail = $this->presenter->userDetail;
        $this->userList = $this->presenter->userList;
        $this->maList = $this->presenter->maList;
        $this->is = $this->presenter->is;
        $this->supplier = $this->presenter->supplier;
        $this->presenterUser = $this->presenter->getUser();
        $this->accessibleSettings = $this->presenter->getAccessibleSettings();
    }
    
    private function multiaccounts() {
        try {
            $this->template->multiaccounts = $this->maList->init()->getData();
        } catch (APIException $ex) {
            $this->presenter->handleTapiException($ex);
        }
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

    private function debts() {
        try {
            $debts = $this->debtList->init()->getData();
            $this->debtList->postProcessWithUsers($this->userList->getById(), $debts);
            $this->template->debts = $debts;
            $this->template->debtWarnings = $this->debtList->getWarnings();
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
                    ->setTo(date("Ymd", strtotime(" + 12 months")))
                    ->setOrder("startTime")
                    ->getData();
            $this->template->eventWarnings = 0;
            if (!empty($this->template->events)) {
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
        $this->template->steam = $this->is->init()->getData();

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
        //tapi multiaccounts
        $this->multiaccounts();
        //tapi debts
        $this->debts();
        
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
