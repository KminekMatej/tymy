<?php

namespace Tymy\Module\Core\Component;

use Kdyby\Translation\Translator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tymy\Module\File\Handler\FileUploadHandler;
use Tymy\Module\Core\Model\Supplier;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Debt\Manager\DebtManager;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Manager\UserManager;
use const TEAM_DIR;

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
    private Translator $translator;
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
        $this->supplier = $presenter->supplier;
        $this->user = $user;
        $this->teamManager = $teamManager;
        $this->accessibleSettings = $this->presenter->getAccessibleSettings();
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
        $this->template->playersWarnings = $this->userManager->getWarnings($users);
        $this->template->me = $this->userManager->getById($this->user->getId());
    }

    private function initPolls(): void
    {
        $polls = $this->pollManager->getListUserAllowed();
        $this->template->polls = $polls;
        $this->template->voteWarnings = $this->pollManager->getWarnings($polls);
    }

    private function initEvents(): void
    {
        $events = $this->eventManager->getEventsInterval($this->user->getId(), new DateTime(), new DateTime("+ 1 year"));
        $this->template->events = $events;
        $this->template->eventWarnings = $this->eventManager->getWarnings($events);
        $this->template->eventColors = $this->supplier->getEventColors();
    }

    private function initFiles(): void
    {
        $this->template->files = array_map(function ($path) {
            return str_replace(FileUploadHandler::DOWNLOAD_DIR, "", $path);
        }, glob(FileUploadHandler::DOWNLOAD_DIR . "/*.*"));
        $this->template->usedSpace = $this->getDownloadFolderSize();
    }

    public function createComponentFileUploadForm(): Form
    {
        //create file upload ability
        $form = new Form;
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
            $file->move(FileUploadHandler::DOWNLOAD_DIR . '/' . $file_name);
        }
    }

    private function getDownloadFolderSize(): int
    {
        $cachedSizeFile = TEAM_DIR . "/temp/cache/download-size.json";

        $size = null;
        $timestamp = new DateTime();

        if (file_exists($cachedSizeFile)) {
            $size = intval(file_get_contents($cachedSizeFile));
            $timestamp = DateTime::createFromFormat("U", (string)filemtime($cachedSizeFile));
        }

        if ($size === null || $timestamp < new DateTime("- 10 minutes")) {
            $size = $this->folderSize(FileUploadHandler::DOWNLOAD_DIR);
            file_put_contents($cachedSizeFile, $size);
        }

        return $size;
    }

    private function folderSize(string $dir): int
    {
        $size = 0;

        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : folderSize($each);
        }
        
        return $size;
    }

    private function initSettings(): void
    {
        $this->template->accessibleSettings = $this->accessibleSettings;
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