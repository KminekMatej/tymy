<?php

namespace Tymy\Module\Core\Component;

use Contributte\Translation\Translator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Helper\StringHelper;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Debt\Manager\DebtManager;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\File\Handler\FileManager;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\User as TymyUser;

/**
 * Description of Navbar
 *
 * @author matej
 */
class NavbarControl extends Control
{
    private Translator $translator;

    public function __construct(private SecuredPresenter $presenter, private PollManager $pollManager, private DiscussionManager $discussionManager, private EventManager $eventManager, private DebtManager $debtManager, private UserManager $userManager, private MultiaccountManager $multiaccountManager, private User $user, private TymyUser $tymyUser, private TeamManager $teamManager, private EventTypeManager $eventTypeManager, private Request $httpRequest)
    {
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
        $users = $this->userManager->getList();
        $this->template->counts = $this->userManager->getCounts($users);
        $this->template->playersWarnings = $this->tymyUser->getWarnings();
        $this->template->inits = $this->user->isAllowed($this->user->getId(), Privilege::SYS('SEE_INITS')) ? $this->template->counts["INIT"] : 0;
        $this->template->me = $this->tymyUser;
    }

    private function initPolls(): void
    {
        $polls = $this->pollManager->getListUserAllowed();
        $this->template->polls = $polls;
        $this->template->voteWarnings = $this->pollManager->getWarnings($polls);
    }

    private function initEvents(): void
    {
        $events = $this->eventManager->getEventsInterval($this->user->getId(), new DateTime(), new DateTime("+ 1 year"), "startTime__asc");
        $this->template->events = $events;
        $this->template->eventWarnings = $this->eventManager->getWarnings($events, $this->tymyUser);
        $this->template->eventTypes = $this->eventTypeManager->getIndexedList();
    }

    private function initFiles(): void
    {
        $files = [];
        foreach (glob(FileManager::DOWNLOAD_DIR . "/*.*") as $file) {
            $basename = basename($file);
            $files[StringHelper::urlencode($basename)] = $basename;
        }
        $this->template->files = $files;
    }

    public function createComponentFileUploadForm(): Form
    {
        //create file upload ability
        $form = new Form();
        $form->addUpload("file", $this->translator->translate("file.file"));
        $form->addSubmit("save", "Nahrát");
        $form->onSuccess[] = fn(Form $form, $values) => $this->fileLoad($form, $values);

        return $form;
    }

    public function fileLoad(Form $form, $values): void
    {
        $file = $values['file'];
        assert($file instanceof FileUpload);

        if ($file->isOk()) {
            $file->move(FileManager::DOWNLOAD_DIR . '/' . $file->getUntrustedName());
        }
    }

    private function initSettings(): void
    {
        $this->template->accessibleSettings = $this->presenter->getAccessibleSettings();
    }

    public function render(): void
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

    public function handleRefresh(): void
    {
        if ($this->httpRequest->isAjax()) {
            $this->redrawControl('nav');
        } else {
            $this->presenter->redirect('this');
        }
    }
}
