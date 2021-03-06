<?php

namespace App\Presenters;

use Tapi\AvatarUploadResource;
use Tapi\Exception\APIException;
use Tapi\UserCreateResource;
use Tapi\UserDeleteResource;
use Tapi\UserEditResource;
use Tapi\UserResource;

class TeamPresenter extends SecuredPresenter {
    
    private $userType;
    
    /** @var UserCreateResource @inject */
    public $userCreator;
    
    /** @var UserEditResource @inject */
    public $userEditor;
    
    /** @var UserDeleteResource @inject */
    public $userDeleter;
    
    /** @var AvatarUploadResource @inject */
    public $avatarUploader;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function beforeRender() {
        parent::beforeRender();
        
        $allFields = UserResource::getAllFields($this->translator);
        $this->template->addFilter('errorsCount', function ($player, $tabName) use ($allFields) {
            switch ($tabName) {
                case "osobni-udaje":
                    $errFields = array_intersect(array_keys($allFields["PERSONAL"]), $this->supplier->getRequiredFields(), $player->errFls);
                    break;
                case "prihlaseni":
                    $errFields = array_intersect(array_keys($allFields["LOGIN"]), $this->supplier->getRequiredFields(), $player->errFls);
                    break;
                case "tymove-info":
                    $errFields = array_intersect(array_keys($allFields["TEAMINFO"]), $this->supplier->getRequiredFields(), $player->errFls);
                    break;
                case "adresa":
                    $errFields = array_intersect(array_keys($allFields["ADDRESS"]), $this->supplier->getRequiredFields(), $player->errFls);
                    break;
            }
            $cnt = count($errFields);
            return $cnt;
        });
    }

    
    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("team.team",1), "link" => $this->link("Team:") ] ]);
    }
    
    public function actionPlayers() {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.PLAYER",2), "link" => $this->link("Team:players") ] ]);
        $this->userType = "PLAYER";
        $this->setView('default');
    }
    
    public function actionMembers() {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.MEMBER",2), "link" => $this->link("Team:members") ] ]);
        $this->userType = "MEMBER";
        $this->setView('default');
    }
    
    public function actionSicks() {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.SICK",2), "link" => $this->link("Team:sicks") ] ]);
        $this->userType = "SICK";
        $this->setView('default');
    }
    
    public function actionInits() {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.INIT",2), "link" => $this->link("Team:inits") ] ]);
        $this->userType = "INIT";
        $this->setView('default');
    }
    
    public function renderDefault() {
        //parent::showNotes();
        try {
            $users = $this->userList->init()->setUserType($this->userType)->getData();
            $allMails = [];
            if ($users) {
                foreach ($users as $u) {
                    if (property_exists($u, "email")) {
                        $allMails[] = $u->email;
                    }
                }
            } else {
                $this->flashMessage($this->translator->translate("common.alerts.nobodyFound") . "!");
            }

            $this->template->users = $users;
            $this->template->allMails = join(",", $allMails);
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
    }

    public function renderNew($player = null) {
        if(! $this->getUser()->isAllowed("user", "canCreate")){
            $this->flashMessage($this->translator->translate("common.alerts.notPermitted"), "warning");
            $this->redirect('this');
        }
        
        $this->template->canUpdate = true;
        
        $teamData = $this->is->getData();
        
        $errFls = array_intersect($this->supplier->getRequiredFields(), array_merge(UserResource::FIELDS_PERSONAL, UserResource::FIELDS_LOGIN, UserResource::FIELDS_TEAMINFO, UserResource::FIELDS_ADDRESS));
        
        $newPlayer = (object)[
            "id" => null,
            "login" => "",
            "canLogin" => true,
            "canEditCallName" => true,
            "status" => "PLAYER",
            "firstName" => "",
            "lastName" => "",
            "callName" => "",
            "language" => $teamData->defaultLanguageCode,
            "email" => "",
            "jerseyNumber" => "",
            "gender" => "UNKNOWN",
            "street" => "",
            "city" => "",
            "zipCode" => "",
            "phone" => "",
            "phone2" => "",
            "birthDate" => "",
            "nameDayMonth" => null,
            "nameDayDay" => null,
            "fullName" => "",
            "pictureUrl" => "",
            "displayName" => "",
            "isNew" => true,
            "errCnt" => count($errFls),
            "errFls" => $errFls,
        ];
        
        if($player){
            try {
                $user = $this->userDetail->init()
                        ->setId($this->parseIdFromWebname($player))
                        ->getData();
            } catch (APIException $ex) {
                $this->handleTapiException($ex);
            }
            
            //todo rewrite playerMock
            $newPlayer = $user;
            $newPlayer->id = null;
            $newPlayer->status = "PLAYER";
            $newPlayer->email = "";
            $newPlayer->pictureUrl = "";
        }

        //parent::showNotes();
        
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("common.new")]]);

        $this->template->player = $newPlayer;
        $this->template->allRoles = $this->getAllRoles();
    }
    
    public function renderPlayer($player) {
        try {
            $user = $this->userDetail->init()
                    ->setId($this->parseIdFromWebname($player))
                    ->getData();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        //parent::showNotes($user->id);

        $this->setLevelCaptions(["2" => ["caption" => $user->displayName, "link" => $this->link("Team:player", $user->webName)]]);

        $this->template->player = $user;
        $this->template->canUpdate = $this->getUser()->isAllowed("user", "canUpdate") || $user->id == $this->getUser()->getId();
        
        $this->template->allRoles = $this->getAllRoles();
    }

    public function renderJerseys(){
        $allPlayers = $this->userList->init()->getData();
        $min = 0;
        $max = 0;
        $jerseyList = [];
        foreach ($allPlayers as $player) {
            if ($player->jerseyNumber != "") {
                if($player->jerseyNumber < $min) $min = $player->jerseyNumber;
                if($player->jerseyNumber > $max) $max = $player->jerseyNumber;
                $jerseyList[$player->jerseyNumber][] = $player;
            }
        }
        for ($i = $min; $i <=$max+10; $i++){
            if(!array_key_exists($i, $jerseyList)) $jerseyList[$i] = null;
        }
        ksort($jerseyList);
        
        $this->template->jerseyList = $jerseyList;
        $this->template->me = $this->userList->getMe();
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.jersey", 2), "link" => $this->link("Team:jerseys")]]);
    }
    
    public function handleCreate(){
        $bind = $this->getRequest()->getPost();
        if(array_key_exists("roles", $bind["changes"]) && $bind["changes"]["roles"] === ""){
            $bind["changes"]["roles"] = [];
        }
        /* @todo Finish proper validation on new player, make sure that password and email fields are filled */
        try {
            $createdPlayer = $this->userCreator->init()
                ->setUser($bind["changes"])
                ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, "this");
        }
        
        $this->flashMessage($this->translator->translate("common.alerts.userAdded", null, ["fullname" => $createdPlayer->displayName]), "success");
        
        $this->redirect("Team:player", $createdPlayer->webName);
    }
    
    public function handleEdit(){
        $bind = $this->getRequest()->getPost();
        if(array_key_exists("roles", $bind["changes"]) && $bind["changes"]["roles"] === ""){
            $bind["changes"]["roles"] = [];
        }
        try {
            $this->userEditor->init()
                ->setId($bind["id"])
                ->setUser($bind["changes"])
                ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, "this");
        }
        
        $this->flashMessage($this->translator->translate("common.alerts.configSaved"), "success");
        $this->redrawControl("flashes");
        $this->redrawControl("player-header");
        
        $this->redrawNavbar();
        
        if(array_key_exists("language", $bind["changes"])){
            $this->flashMessage($this->translator->translate("team.alerts.signOffNeeded"), "info");
            $this->redirect('this');
        }
    }
    
    public function handleDelete() {
        if (!$this->getUser()->isAllowed("user", "canDelete"))
            return;
        $bind = $this->getRequest()->getPost();
        try {
            $this->userDeleter->init()
                    ->setId($bind["id"])
                    ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        $this->flashMessage($this->translator->translate("common.alerts.userSuccesfullyDeleted"), "success");
        $this->redirect('Team:');
    }

    public function handleUpload() {
        $bind = $this->getRequest()->getPost();
        $files = $this->getRequest()->getFiles();
        $file = $files["files"][0];
        if ($file->isImage() && $file->isOk()) {
            $avatarB64 = 'data:' . mime_content_type($file->getTemporaryFile()) . ';base64,' . base64_encode(file_get_contents($file->getTemporaryFile()));
            try {
                $this->avatarUploader->init()
                        ->setId($bind["id"])
                        ->setAvatar($avatarB64)
                        ->perform();
            } catch (APIException $ex) {
                $this->handleTapiException($ex, "this");
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
