<?php

namespace App\Presenters;
use Tapi\UserCreateResource;
use Tapi\UserEditResource;
use Tapi\UserDeleteResource;
use Tapi\AvatarUploadResource;
use Tapi\Exception\APIException;

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
    
    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => "Tým", "link" => $this->link("Team:") ] ]);
    }
    
    public function actionPlayers() {
        $this->setLevelCaptions(["2" => ["caption" => "Hráči", "link" => $this->link("Team:players") ] ]);
        $this->userType = "PLAYER";
        $this->setView('default');
    }
    
    public function actionMembers() {
        $this->setLevelCaptions(["2" => ["caption" => "Členové", "link" => $this->link("Team:members") ] ]);
        $this->userType = "MEMBER";
        $this->setView('default');
    }
    
    public function actionSicks() {
        $this->setLevelCaptions(["2" => ["caption" => "Marodi", "link" => $this->link("Team:sicks") ] ]);
        $this->userType = "SICK";
        $this->setView('default');
    }
    
    public function actionInits() {
        $this->setLevelCaptions(["2" => ["caption" => "Registrovaní", "link" => $this->link("Team:inits") ] ]);
        $this->userType = "INIT";
        $this->setView('default');
    }
    
    public function renderDefault() {
        parent::showNotes();
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
                $this->flashMessage("Nikdo nenalezen!");
            }

            $this->template->users = $users;
            $this->template->allMails = join(",", $allMails);
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
    }

    public function renderPlayer($player) {
        try {
            $user = $this->userDetail->init()
                    ->setId($this->parseIdFromWebname($player))
                    ->getData();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        parent::showNotes($user->id);

        $this->setLevelCaptions(["2" => ["caption" => $user->displayName, "link" => $this->link("Team:player", $user->webName)]]);

        $this->template->player = $user;
        $this->template->canUpdate = $this->getUser()->isAllowed("user", "canUpdate") || $user->id == $this->getUser()->getId();
        $allRoles = [];
        $allRoles[] = (object) ["code" => "SUPER", "caption" => "Administrátor", "class" => $this->supplier->getRoleClass("SUPER")];
        $allRoles[] = (object) ["code" => "USR", "caption" => "Správce uživatelů", "class" => $this->supplier->getRoleClass("USR")];
        $allRoles[] = (object) ["code" => "ATT", "caption" => "Správce docházky", "class" => $this->supplier->getRoleClass("ATT")];

        $this->template->allRoles = $allRoles;
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
        $this->setLevelCaptions(["2" => ["caption" => "Dresy", "link" => $this->link("Team:jerseys")]]);
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
        $this->flashMessage("Uživatel byl úspešně smazán", "success");
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
        } else {
            $response = $this->getHttpResponse();
            $response->setCode(400);
        }
    }

}
