<?php

namespace Tymy\Module\Team\Presenter\Front;

use Nette\Application\UI\Form;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Http\FileUpload;
use Nette\Utils\Image;
use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Core\Factory\FormFactory;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\User\Manager\AvatarManager;
use Tymy\Module\User\Model\User;

use function count;

class PlayerPresenter extends SecuredPresenter
{
    #[\Nette\DI\Attributes\Inject]
    public AvatarManager $avatarManager;

    #[\Nette\DI\Attributes\Inject]
    public FormFactory $formFactory;

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("team.team", 1), $this->link(":Team:Default:"));

        $allFields = $this->userManager->getAllFields();
        assert($this->template instanceof Template);
        $this->template->addFilter('errorsCount', function ($player, $tabName) use ($allFields): int {
            $errFields = [];
            switch ($tabName) {
                case "osobni-udaje":
                    $errFields = array_intersect(array_keys($allFields["PERSONAL"]), $this->team->getRequiredFields(), $player->getErrFields());
                    break;
                case "prihlaseni":
                    $errFields = array_intersect(array_keys($allFields["LOGIN"]), $this->team->getRequiredFields(), $player->getErrFields());
                    break;
                case "ui":
                    $errFields = array_intersect(array_keys($allFields["UI"]), $this->team->getRequiredFields(), $player->getErrFields());
                    break;
                case "tymove-info":
                    $errFields = array_intersect(array_keys($allFields["TEAMINFO"]), $this->team->getRequiredFields(), $player->getErrFields());
                    break;
                case "adresa":
                    $errFields = array_intersect(array_keys($allFields["ADDRESS"]), $this->team->getRequiredFields(), $player->getErrFields());
                    break;
            }

            return count($errFields);
        });
    }

    public function renderNew($player = null): void
    {
        if (!$this->getUser()->isAllowed((string) $this->user->getId(), "SYS:USR_CREATE")) {
            $this->flashMessage($this->translator->translate("common.alerts.notPermitted"), "warning");
            $this->redirect('this');
        }

        $this->template->canUpdate = true;
        $this->template->isNew = true;

        $team = $this->teamManager->getTeam();

        $errFls = array_intersect($this->team->getRequiredFields(), array_merge(User::FIELDS_PERSONAL, User::FIELDS_LOGIN, User::FIELDS_TEAMINFO, User::FIELDS_ADDRESS));

        if ($player) {  //new player based on another user
            $user = $this->userManager->getById($this->parseIdFromWebname($player));

            $newPlayer = $user->setStatus("PLAYER")
                ->setEmail("")
                ->setPictureUrl("");
        } else {    //brand new player
            $newPlayer = (new User())
                ->setLanguage($team->getDefaultLanguageCode())
                ->setCanLogin(true)
                ->setCanEditCallName(true)
                ->setStatus("PLAYER")
                ->setGender("UNKNOWN")
                ->setIsNew(true)
                ->setErrFields($errFls);
        }

        $this->addBreadcrumb($this->translator->translate("common.new"));

        $this->template->player = $newPlayer;
        $this->template->allRoles = $this->getAllRoles();
    }

    public function renderDefault($player): void
    {
        $userId = $this->parseIdFromWebname($player);
        $user = $this->userManager->getById($userId);

        if (!$user instanceof BaseModel) {
            $this->flashMessage($this->translator->translate("common.alerts.userNotFound", null, ['id' => $userId]), "danger");
            $this->redirect(':Team:Default:');
        }

        $this->addBreadcrumb($user->getDisplayName(), $this->link(":Team:Player:", $user->getWebName()));

        $this->template->player = $user;
        $this->template->isMe = $user->getId() == $this->getUser()->getId();
        $this->template->canUpdate = $this->getUser()->isAllowed((string) $this->user->getId(), "SYS:USR_UPDATE") || $this->template->isMe;

        $this->template->allRoles = $this->getAllRoles();
        $this->template->allSkins = $this->teamManager->allSkins;
        $this->template->isNew = false;
    }

    public function handleDelete($player): void
    {
        $userId = $this->parseIdFromWebname($player);

        try {
            $this->userManager->delete($userId);
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
            $this->redirect('this');
        }

        $this->flashMessage($this->translator->translate("common.alerts.userSuccesfullyDeleted") . " (id $userId)", "success");
        $this->redirect(':Team:Default:');
    }

    public function handleUpload(): void
    {
        $this->getRequest()->getPost();
        $files = $this->getRequest()->getFiles();
        /* @var $file FileUpload */
        $file = $files["files"][0] ?? null;
        if ($file && $file->isImage() && $file->isOk()) {
            assert($file instanceof FileUpload);
            $type = null;
            $image = Image::fromFile($file->getTemporaryFile(), $type);
            try {
                $this->avatarManager->uploadAvatarImage($image, $type, $this->user->getId());
            } catch (TymyResponse $tResp) {
                $this->respondByTymyResponse($tResp);
                $this->redirect('this');
            }

            $this->flashMessage($this->translator->translate("common.alerts.avatarSaved"), "success");
            $this->redrawControl("flashes");
            $this->redrawControl("player-header");
            $this->redrawNavbar();
        } else {
            $response = $this->getHttpResponse();
            $response->setCode(400);
        }
    }


    public function createComponentUserConfigForm(): Form
    {
        if ($this->getRequest()->getParameter("player")) {
            $userId = $this->parseIdFromWebname($this->getRequest()->getParameter("player"));
        }

        return $this->formFactory->createUserConfigForm(
            fn(Form $form, $values) => $this->userConfigFormSuccess($form, $values),
            $this->getAction() == "new" || !isset($userId) ? null : $this->userManager->getById($userId),
        );
    }

    public function userConfigFormSuccess(Form $form, $values): void
    {
        $userId = (int) $values->id;

        try {
            if ($userId !== 0) {
                $this->userManager->update((array) $values, $userId);
                $this->flashMessage($this->translator->translate("common.alerts.configSaved"), "success");
                $this->redirect('this');
            } else {
                $createdUser = $this->userManager->create((array) $values);
                $this->flashMessage($this->translator->translate("common.alerts.userAdded", null, ["fullname" => $createdUser->getDisplayName()]), "success");
                $this->redirect(':Team:Default:');
            }
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }

        $this->redirect('this');
    }
}
