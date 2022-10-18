<?php

namespace Tymy\Module\Team\Presenter\Front;

use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Model\User;

class DefaultPresenter extends SecuredPresenter
{
    private string $userType;

    public function beforeRender(): void
    {
        parent::beforeRender();

        $allFields = $this->userManager->getAllFields();
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

        $this->addBreadcrumb($this->translator->translate("team.team", 1), $this->link(":Team:Default:"));
    }

    public function actionPlayers(): void
    {
        $this->addBreadcrumb($this->translator->translate("team.PLAYER", 2), $this->link(":Team:Default:players"));
        $this->userType = "PLAYER";
        $this->setView('default');
    }

    public function actionMembers(): void
    {
        $this->addBreadcrumb($this->translator->translate("team.MEMBER", 2), $this->link(":Team:Default:members"));
        $this->userType = "MEMBER";
        $this->setView('default');
    }

    public function actionSicks(): void
    {
        $this->addBreadcrumb($this->translator->translate("team.SICK", 2), $this->link(":Team:Default:sicks"));
        $this->userType = "SICK";
        $this->setView('default');
    }

    public function actionInits(): void
    {
        $this->addBreadcrumb($this->translator->translate("team.INIT", 2), $this->link(":Team:Default:inits"));
        $this->userType = "INIT";
        $this->setView('default');
    }

    public function renderDefault(): void
    {
        $users = isset($this->userType) ? $this->userManager->getByStatus($this->userType) : $this->userManager->getList();
        $allMails = [];
        if ($users !== []) {
            foreach ($users as $u) {
                if ($u->getEmail()) {
                    $allMails[] = $u->getEmail();
                }
            }
        } else {
            $this->flashMessage($this->translator->translate("common.alerts.nobodyFound") . "!");
        }

        $this->template->userType = $this->userType ?? null;
        $this->template->users = $users;
        $this->template->allMails = implode(",", $allMails);
    }

    public function renderJerseys(): void
    {
        $allPlayers = $this->userManager->getList();
        $min = 0;
        $max = 0;
        $jerseyList = [];
        foreach ($allPlayers as $player) {
            /* @var $player User */
            if ($player->getJerseyNumber() != "") {
                $jNumber = (int) $player->getJerseyNumber();
                if ($jNumber < $min && $jNumber > -100) {
                    $min = $jNumber;
                }
                if ($jNumber > $max && $jNumber < 10000) {//limit 10000 top
                    $max = $jNumber;
                }
                $jerseyList[$player->getJerseyNumber()][] = $player;
            }
        }
        for ($i = $min; $i <= $max + 10; $i++) {
            if (!array_key_exists($i, $jerseyList)) {
                $jerseyList[$i] = null;
            }
        }
        ksort($jerseyList);

        $this->template->jerseyList = $jerseyList;
        $this->addBreadcrumb($this->translator->translate("team.jersey", 2), $this->link(":Team:Default:jerseys"));
    }

    public function handleApprove(int $userId): void
    {
        if (!$this->getUser()->isAllowed($this->user->getId(), Privilege::SYS("USR_UPDATE"))) {
            $this->flashMessage($this->translator->translate("common.alerts.notPermitted"));
            $this->redirect('this');
        }

        try {
            $this->userManager->update(["status" => User::STATUS_PLAYER, "canLogin" => true], $userId);
        } catch (TymyResponse $tResp) {
            $this->handleTymyResponse($tResp);
            $this->redirect('this');
        }

        $this->redrawControl("userList");
        $this->redrawNavbar();
    }

    public function handleDelete(int $userId): void
    {
        try {
            $this->userManager->delete($userId);
        } catch (TymyResponse $tResp) {
            $this->handleTymyResponse($tResp);
            $this->redirect('this');
        }

        $this->redrawControl("userList");
        $this->redrawNavbar();
    }
}
