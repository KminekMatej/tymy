<?php

namespace Tymy\Module\Team\Presenter\Front;

use Tapi\UserResource;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Model\User;

class TeamPresenter extends SecuredPresenter
{
    private $userType;
    public $userCreator;
    public $userEditor;
    public $userDeleter;
    public $avatarUploader;

    public function beforeRender()
    {
        parent::beforeRender();

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
            $cnt = count($errFields);
            return $cnt;
        });
    }

    public function startup()
    {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("team.team", 1), "link" => $this->link("Team:")]]);
    }

    public function actionPlayers()
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.PLAYER", 2), "link" => $this->link("Team:players")]]);
        $this->userType = "PLAYER";
        $this->setView('default');
    }

    public function actionMembers()
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.MEMBER", 2), "link" => $this->link("Team:members")]]);
        $this->userType = "MEMBER";
        $this->setView('default');
    }

    public function actionSicks()
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.SICK", 2), "link" => $this->link("Team:sicks")]]);
        $this->userType = "SICK";
        $this->setView('default');
    }

    public function actionInits()
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.INIT", 2), "link" => $this->link("Team:inits")]]);
        $this->userType = "INIT";
        $this->setView('default');
    }

    public function renderDefault()
    {
        $users = $this->userManager->getByStatus($this->userType);
        $allMails = [];
        if ($users) {
            foreach ($users as $u) {
                if ($u->getEmail()) {
                    $allMails[] = $u->getEmail();
                }
            }
        } else {
            $this->flashMessage($this->translator->translate("common.alerts.nobodyFound") . "!");
        }

        $this->template->users = $users;
        $this->template->allMails = join(",", $allMails);
    }

    public function renderNew($player = null)
    {
        if (!$this->getUser()->isAllowed("user", "canCreate")) {
            $this->flashMessage($this->translator->translate("common.alerts.notPermitted"), "warning");
            $this->redirect('this');
        }

        $this->template->canUpdate = true;

        $teamData = $this->is->getData();

        $errFls = array_intersect($this->supplier->getRequiredFields(), array_merge(UserResource::FIELDS_PERSONAL, UserResource::FIELDS_LOGIN, UserResource::FIELDS_TEAMINFO, UserResource::FIELDS_ADDRESS));


        if ($player) {  //new player based on another user
            $user = $this->userManager->getById($this->parseIdFromWebname($player));
            $newPlayer = $user->setId(null)
                    ->setStatus("PLAYER")
                    ->setEmail("")
                    ->setPictureUrl("");
        } else {    //brand new player
            $newPlayer = (new User())
                    ->setId(null)
                    ->setLogin("")
                    ->setCanLogin(true)
                    ->setCanEditCallName(true)
                    ->setStatus("PLAYER")
                    ->setFirstName("")
                    ->setLastName("")
                    ->setCallName("")
                    ->setLanguage($teamData->defaultLanguageCode)
                    ->setEmail("")
                    ->setJerseyNumber("")
                    ->setGender("UNKNOWN")
                    ->setStreet("")
                    ->setCity("")
                    ->setZipCode("")
                    ->setPhone("")
                    ->setPhone2("")
                    ->setBirthDate("")
                    ->setNameDayMonth(null)
                    ->setNameDayDay(null)
                    ->setFullName("")
                    ->setPictureUrl("")
                    ->setDisplayName("")
                    ->setIsNew(true)
                    ->setErrFields($errFls);
        }

        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("common.new")]]);

        $this->template->player = $newPlayer;
        $this->template->allRoles = $this->getAllRoles();
    }

    public function renderPlayer($player)
    {
        /* @var $user User */
        $user = $this->userManager->getById($this->parseIdFromWebname($player));

        $this->setLevelCaptions(["2" => ["caption" => $user->getDisplayName(), "link" => $this->link("Team:player", $user->getWebName())]]);

        $this->template->player = $user;
        $this->template->canUpdate = $this->getUser()->isAllowed($this->user->getId(), Privilege::SYS("USR_UPDATE")) || $user->getId() == $this->getUser()->getId();

        $this->template->allRoles = $this->getAllRoles();
    }

    public function renderJerseys()
    {
        $allPlayers = $this->userManager->getList();
        $min = 0;
        $max = 0;
        $jerseyList = [];
        foreach ($allPlayers as $player) {
            /* @var $player User */
            if ($player->getJerseyNumber() != "") {
                if ($player->getJerseyNumber() < $min) {
                    $min = $player->getJerseyNumber();
                }
                if ($player->getJerseyNumber() > $max) {
                    $max = $player->getJerseyNumber();
                }
                $jerseyList[$player->getJerseyNumber()][] = $player;
            }
        }
        for ($i = $min; $i <= $max + 10; $i++) {
            if (!array_key_exists($i, $jerseyList))
                $jerseyList[$i] = null;
        }
        ksort($jerseyList);

        $this->template->jerseyList = $jerseyList;
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.jersey", 2), "link" => $this->link("Team:jerseys")]]);
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