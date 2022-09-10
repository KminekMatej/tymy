<?php

namespace Tymy\Module\Team\Presenter\Front;

use Nette\Http\FileUpload;
use Nette\Utils\Image;
use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Manager\AvatarManager;
use Tymy\Module\User\Model\User;

class PlayerPresenter extends SecuredPresenter
{
    /** @inject */
    public AvatarManager $avatarManager;

    public function beforeRender()
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("team.team", 1), $this->link(":Team:Default:"));

        $allFields = $this->userManager->getAllFields();
        $this->template->addFilter('errorsCount', function ($player, $tabName) use ($allFields) {
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

    public function renderNew($player = null)
    {
        if (!$this->getUser()->isAllowed($this->user->getId(), Privilege::SYS('USR_CREATE'))) {
            $this->flashMessage($this->translator->translate("common.alerts.notPermitted"), "warning");
            $this->redirect('this');
        }

        $this->template->canUpdate = true;

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

    public function renderDefault($player)
    {
        /* @var $user User */
        $userId = $this->parseIdFromWebname($player);
        $user = $this->userManager->getById($userId);

        if (!$user) {
            $this->flashMessage($this->translator->translate("common.alerts.userNotFound", null, ['id' => $userId]), "danger");
            $this->redirect(':Team:Default:');
        }

        $this->addBreadcrumb($user->getDisplayName(), $this->link(":Team:Player:", $user->getWebName()));

        $this->template->player = $user;
        $this->template->isMe = $user->getId() == $this->getUser()->getId();
        $this->template->canUpdate = $this->getUser()->isAllowed($this->user->getId(), Privilege::SYS("USR_UPDATE")) || $this->template->isMe;

        $this->template->allRoles = $this->getAllRoles();
        $this->template->allSkins = TeamManager::SKINS;
    }

    public function handleCreate()
    {
        $bind = $this->getRequest()->getPost();
        if (array_key_exists("roles", $bind["changes"]) && $bind["changes"]["roles"] === "") {
            $bind["changes"]["roles"] = [];
        }
        /* @todo Finish proper validation on new player, make sure that password and email fields are filled */

        try {
            /* @var $createdPlayer User */
            $createdPlayer = $this->userManager->create($bind["changes"]);
        } catch (TymyResponse $tResp) {
            $this->handleTymyResponse($tResp);
            $this->redirect("this");
        }

        $this->flashMessage($this->translator->translate("common.alerts.userAdded", null, ["fullname" => $createdPlayer->getDisplayName()]), "success"); /* @phpstan-ignore-line */

        $this->redirect(":Team:Player:", $createdPlayer->getWebName()); /* @phpstan-ignore-line */
    }

    public function handleEdit()
    {
        $bind = $this->getRequest()->getPost();
        if (array_key_exists("roles", $bind["changes"]) && $bind["changes"]["roles"] === "") {
            $bind["changes"]["roles"] = [];
        }

        try {
            $this->userManager->update($bind["changes"], $bind["id"]);
        } catch (TymyResponse $tResp) {
            $this->handleTymyResponse($tResp);
            $this->redirect('this');
        }

        $this->flashMessage($this->translator->translate("common.alerts.configSaved"), "success");
        $this->redrawControl("flashes");
        $this->redrawControl("player-header");

        $this->redrawNavbar();

        if (array_key_exists("language", $bind["changes"])) {
            $this->flashMessage($this->translator->translate("team.alerts.signOffNeeded"), "info");
            $this->redirect('this');
        }
    }

    public function handleDelete()
    {
        $bind = $this->getRequest()->getPost();
        try {
            $this->userManager->delete($bind["id"]);
        } catch (TymyResponse $tResp) {
            $this->handleTymyResponse($tResp);
            $this->redirect('this');
        }

        $this->flashMessage($this->translator->translate("common.alerts.userSuccesfullyDeleted") . " ({$bind["id"]})", "success");
        $this->redirect(':Team:Default:');
    }

    public function handleUpload()
    {
        $bind = $this->getRequest()->getPost();
        $files = $this->getRequest()->getFiles();
        /* @var $file FileUpload */
        $file = $files["files"][0] ?? null;
        if ($file && $file->isImage() && $file->isOk()) {
            $type = null;
            $image = Image::fromFile($file->getTemporaryFile(), $type);
            try {
                $this->avatarManager->uploadAvatarImage($image, $type, $this->user->getId());
            } catch (TymyResponse $tResp) {
                $this->handleTymyResponse($tResp);
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
}
