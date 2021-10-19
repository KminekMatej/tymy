<?php

namespace Tymy\Module\Team\Presenter\Front;

use Tapi\UserResource;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Model\User;

class PlayerPresenter extends SecuredPresenter
{

    public function beforeRender()
    {
        parent::beforeRender();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("team.team", 1), "link" => $this->link(":Team:Default:")]]);

        $allFields = UserResource::getAllFields($this->translator);
        $this->template->addFilter('errorsCount', function ($player, $tabName) use ($allFields) {
            switch ($tabName) {
                case "osobni-udaje":
                    $errFields = array_intersect(array_keys($allFields["PERSONAL"]), $this->supplier->getRequiredFields(), $player->getErrFields());
                    break;
                case "prihlaseni":
                    $errFields = array_intersect(array_keys($allFields["LOGIN"]), $this->supplier->getRequiredFields(), $player->getErrFields());
                    break;
                case "tymove-info":
                    $errFields = array_intersect(array_keys($allFields["TEAMINFO"]), $this->supplier->getRequiredFields(), $player->getErrFields());
                    break;
                case "adresa":
                    $errFields = array_intersect(array_keys($allFields["ADDRESS"]), $this->supplier->getRequiredFields(), $player->getErrFields());
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

        $errFls = array_intersect($this->supplier->getRequiredFields(), array_merge(UserResource::FIELDS_PERSONAL, UserResource::FIELDS_LOGIN, UserResource::FIELDS_TEAMINFO, UserResource::FIELDS_ADDRESS));

        if ($player) {  //new player based on another user
            $user = $this->userManager->getById($this->parseIdFromWebname($player));
            $newPlayer = $user->setId(null)
                    ->setStatus("PLAYER")
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

        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("common.new")]]);

        $this->template->player = $newPlayer;
        $this->template->allRoles = $this->getAllRoles();
    }

    public function renderDefault($player)
    {
        /* @var $user User */
        $user = $this->userManager->getById($this->parseIdFromWebname($player));

        $this->setLevelCaptions(["2" => ["caption" => $user->getDisplayName(), "link" => $this->link(":Team:Player:", $user->getWebName())]]);

        $this->template->player = $user;
        $this->template->canUpdate = $this->getUser()->isAllowed($this->user->getId(), Privilege::SYS("USR_UPDATE")) || $user->getId() == $this->getUser()->getId();

        $this->template->allRoles = $this->getAllRoles();
    }

    public function handleCreate()
    {
        $bind = $this->getRequest()->getPost();
        if (array_key_exists("roles", $bind["changes"]) && $bind["changes"]["roles"] === "") {
            $bind["changes"]["roles"] = [];
        }
        /* @todo Finish proper validation on new player, make sure that password and email fields are filled */

        /* @var $createdPlayer User */
        $createdPlayer = $this->userManager->create($bind["changes"]);

        $this->flashMessage($this->translator->translate("common.alerts.userAdded", null, ["fullname" => $createdPlayer->getDisplayName()]), "success");

        $this->redirect("Team:player", $createdPlayer->getWebName());
    }

    public function handleEdit()
    {
        $bind = $this->getRequest()->getPost();
        if (array_key_exists("roles", $bind["changes"]) && $bind["changes"]["roles"] === "") {
            $bind["changes"]["roles"] = [];
        }

        $this->userManager->update($bind["changes"], $bind["id"]);

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
        if (!$this->getUser()->isAllowed("user", "canDelete"))
            return;
        $bind = $this->getRequest()->getPost();
        $this->userManager->delete($bind["id"]);
        $this->flashMessage($this->translator->translate("common.alerts.userSuccesfullyDeleted"), "success");
        $this->redirect('Team:');
    }

    public function handleUpload()
    {
        $bind = $this->getRequest()->getPost();
        $files = $this->getRequest()->getFiles();
        $file = $files["files"][0];
        if ($file->isImage() && $file->isOk()) {
            $avatarB64 = 'data:' . mime_content_type($file->getTemporaryFile()) . ';base64,' . base64_encode(file_get_contents($file->getTemporaryFile()));
            $this->userManager->uploadAvatar($bind["id"], $avatarB64);
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