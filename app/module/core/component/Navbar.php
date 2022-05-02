<?php

namespace Tymy\Module\Core\Component;

use Kdyby\Translation\Translator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Debt\Manager\DebtManager;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\File\Handler\FileManager;
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
    private array $accessibleSettings;
    private PollManager $pollManager;
    private DiscussionManager $discussionManager;
    private EventManager $eventManager;
    private DebtManager $debtManager;
    private UserManager $userManager;
    private MultiaccountManager $multiaccountManager;
    private TeamManager $teamManager;
    private Translator $translator;
    private User $user;
    private EventTypeManager $eventTypeManager;

    public function __construct(SecuredPresenter $presenter, PollManager $pollManager, DiscussionManager $discussionManager, EventManager $eventManager, DebtManager $debtManager, UserManager $userManager, MultiaccountManager $multiaccountManager, User $user, TeamManager $teamManager, EventTypeManager $eventTypeManager)
    {
        $this->presenter = $presenter;
        $this->discussionManager = $discussionManager;
        $this->pollManager = $pollManager;
        $this->eventManager = $eventManager;
        $this->eventTypeManager = $eventTypeManager;
        $this->debtManager = $debtManager;
        $this->userManager = $userManager;
        $this->multiaccountManager = $multiaccountManager;
        $this->user = $user;
        $this->teamManager = $teamManager;
        $this->translator = $this->presenter->translator;
    }

    private function initMultiaccounts(): void
    {
        $this->template->multiaccounts = $this->multiaccountManager->getListUserAllowed();
    }

    private function initDiscussions(): void
    {
        $discussions = $this->discussionManager->getListUserAllowed($this->user->getId());
        $this->template->discussions = $discussions;
        $this->template->discussionWarnings = $this->discussionManager->getWarnings($discussions);
    }

    private function initDebts(): void
    {
        $debts = $this->debtManager->getListUserAllowed();
        $this->template->debts = $debts;
        $this->template->debtWarnings = $this->debtManager->getWarnings($debts);
    }

    private function initPlayers(): void
    {
        /* @var $me \Tymy\Module\User\Model\User */
        $me = $this->userManager->getById($this->user->getId());
        $users = $this->userManager->getList();
        $this->template->counts = $this->userManager->getCounts($users);
        $this->template->playersWarnings = $me->getWarnings();
        $this->template->inits = $this->user->isAllowed($this->user->getId(), \Tymy\Module\Permission\Model\Privilege::SYS('SEE_INITS')) ? $this->template->counts["INIT"] : 0;
        $this->template->me = $me;
    }

    private function initPolls(): void
    {
        $polls = $this->pollManager->getListMenu();
        $this->template->polls = $polls;
        $this->template->voteWarnings = $this->pollManager->getWarnings($polls);
    }

    private function initEvents(): void
    {
        $events = $this->eventManager->getEventsInterval($this->user->getId(), new DateTime(), new DateTime("+ 1 year"));
        $this->template->events = $events;
        $this->template->eventWarnings = $this->eventManager->getWarnings($events);
        $this->template->eventTypes = $this->eventTypeManager->getIndexedList();
    }

    private function initFiles(): void
    {
        $this->template->files = array_map(function ($path) {
            return str_replace(FileManager::DOWNLOAD_DIR, "", $path);
        }, glob(FileManager::DOWNLOAD_DIR . "/*.*"));
    }

    public function createComponentFileUploadForm(): Form
    {
        //create file upload ability
        $form = new Form();
        $form->addUpload("file", $this->translator->translate("file.file"));
        $form->addSubmit("save", "Nahrát");
        $form->onSuccess[] = [$this, "fileLoad"];

        return $form;
    }

    public function fileLoad(Form $form, $values)
    {
        /* @var $file FileUpload */
        $file = $values['file'];

        if ($file->isOk()) {
            $file->move(FileManager::DOWNLOAD_DIR . '/' . $file->getUntrustedName());
        }
    }

    private function initSettings(): void
    {
        $this->template->accessibleSettings = $this->presenter->getAccessibleSettings();
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/templates/navbar.latte');
        $this->template->levels = $this->presenter->getLevelCaptions();
        $this->template->presenterName = $this->presenter->getName();
        $this->template->action = $this->presenter->getAction();
        $this->template->userId = $this->user->getId();
        $this->template->team = $this->teamManager->getTeam();
        $this->template->publicPath = $this->presenter->getHttpRequest()->getUrl()->getBasePath() . "public";

        $this->initDiscussions();
        $this->initPlayers();
        $this->initEvents();
        $this->initPolls();
        $this->initSettings();
        $this->initMultiaccounts();
        $this->initDebts();
        $this->initFiles();

        $this->template->render();
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
