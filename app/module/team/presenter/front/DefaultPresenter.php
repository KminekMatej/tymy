<?php

namespace Tymy\Module\Team\Presenter\Front;

use Tapi\UserResource;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\User\Model\User;

class DefaultPresenter extends SecuredPresenter
{

    private string $userType;

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

            return count($errFields);
        });
    }

    public function startup()
    {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("team.team", 1), "link" => $this->link(":Team:Default:")]]);
    }

    public function actionPlayers()
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.PLAYER", 2), "link" => $this->link(":Team:Default:players")]]);
        $this->userType = "PLAYER";
        $this->setView('default');
    }

    public function actionMembers()
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.MEMBER", 2), "link" => $this->link(":Team:Default:members")]]);
        $this->userType = "MEMBER";
        $this->setView('default');
    }

    public function actionSicks()
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.SICK", 2), "link" => $this->link(":Team:Default:sicks")]]);
        $this->userType = "SICK";
        $this->setView('default');
    }

    public function actionInits()
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.INIT", 2), "link" => $this->link(":Team:Default:inits")]]);
        $this->userType = "INIT";
        $this->setView('default');
    }

    public function renderDefault()
    {
        $users = isset($this->userType) ? $this->userManager->getByStatus($this->userType) : $this->userManager->getList();
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
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("team.jersey", 2), "link" => $this->link(":Team:Default:jerseys")]]);
    }

}