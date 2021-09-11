<?php

namespace Tymy\Module\Core\Component;

use Nette\Application\UI\Control;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tymy\App\Model\Supplier;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Debt\Manager\DebtManager;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of Navbar
 *
 * @author matej
 */
class NavbarControl extends Control
{
    private SecuredPresenter $presenter;
    private Supplier $supplier;
    private array $accessibleSettings;
    private PollManager $pollManager;
    private DiscussionManager $discussionManager;
    private EventManager $eventManager;
    private DebtManager $debtManager;
    private UserManager $userManager;
    private MultiaccountManager $multiaccountManager;
    private TeamManager $teamManager;
    private User $user;

    public function __construct(SecuredPresenter $presenter, PollManager $pollManager, DiscussionManager $discussionManager, EventManager $eventManager, DebtManager $debtManager, UserManager $userManager, MultiaccountManager $multiaccountManager, User $user, TeamManager $teamManager)
    {
        $this->presenter = $presenter;
        $this->discussionManager = $discussionManager;
        $this->pollManager = $pollManager;
        $this->eventManager = $eventManager;
        $this->debtManager = $debtManager;
        $this->userManager = $userManager;
        $this->multiaccountManager = $multiaccountManager;
        $this->supplier = $this->presenter->supplier;
        $this->user = $user;
        $this->teamManager = $teamManager;
        $this->accessibleSettings = $this->presenter->getAccessibleSettings();
    }

    private function multiaccounts()
    {
        $this->template->multiaccounts = $this->multiaccountManager->getListUserAllowed();
    }

    private function discussions()
    {
        $discussions = $this->discussionManager->getListUserAllowed($this->user->getId());
        $this->template->discussions = $discussions;
        $this->template->discussionWarnings = $this->discussionManager->getWarnings($discussions);
    }

    private function debts()
    {
        $debts = $this->debtManager->getListUserAllowed();
        $this->template->debts = $debts;
        $this->template->debtWarnings = $this->debtManager->getWarnings($debts);
        
    }

    private function players()
    {
        $users = $this->userManager->getList();
        $this->template->counts = $this->userManager->getCounts($users);
        $this->template->playersWarnings = $this->userManager->getWarnings($users);
        $this->template->me = $this->userManager->getById($this->user->getId());
    }

    private function polls()
    {
        $polls = $this->pollManager->getListUserAllowed();
        $this->template->polls = $polls;
        $this->template->voteWarnings = $this->pollManager->getWarnings($polls);
    }

    private function events()
    {
        $events = $this->eventManager->getEventsInterval($this->user->getId(), new DateTime(), new DateTime("+ 1 year"));
        $this->template->events = $events;
        $this->template->eventWarnings = $this->eventManager->getWarnings($events);
        $this->template->eventColors = $this->supplier->getEventColors();
    }

    private function settings()
    {
        $this->template->accessibleSettings = $this->accessibleSettings;
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/navbar.latte');
        $this->template->levels = $this->presenter->getLevelCaptions();
        $this->template->presenterName = $this->presenter->getName();
        $this->template->action = $this->presenter->getAction();
        $this->template->userId = $this->user->getId();
        $this->template->team = $this->teamManager->getTeam();

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
        /* //tapi notes
          $this->notes(); */
        //tapi multiaccounts
        $this->multiaccounts();
        //tapi debts
        $this->debts();

        $template->render();
    }

    public function handleRefresh()
    {
        if ($this->parent->isAjax()) {
            $this->redrawControl('nav');
        } else {
            $this->parent->redirect('this');
        }
    }
}