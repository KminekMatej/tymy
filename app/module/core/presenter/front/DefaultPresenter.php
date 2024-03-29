<?php

namespace Tymy\Module\Core\Presenter\Front;

use Nette\Bridges\ApplicationLatte\Template;
use Tymy\Module\Debt\Manager\DebtManager;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;
use Tymy\Module\Multiaccount\Model\TransferKey;
use Tymy\Module\News\Manager\NewsManager;
use Tymy\Module\User\Manager\UserManager;

class DefaultPresenter extends SecuredPresenter
{
    /** @inject */
    public EventManager $eventManager;

    /** @inject */
    public UserManager $userManager;

    /** @inject */
    public DebtManager $debtManager;

    /** @inject */
    public NewsManager $newsManager;

    /** @inject */
    public DiscussionManager $discussionManager;

    /** @inject */
    public EventTypeManager $eventTypeManager;

    /** @inject */
    public MultiaccountManager $multiaccountManager;

    public function beforeRender(): void
    {
        parent::beforeRender();
        assert($this->template instanceof Template);
        $this->template->addFilter('lastLogin', function ($lastLogin): string {
            $diff = date("U") - strtotime($lastLogin);
            if ($diff == 1) {
                return $this->translator->translate("common.lastLogin.secondAgo");
            }
            if ($diff < 60) {
                return $this->translator->translate("common.lastLogin.secondsAgo", null, ['n' => $diff]);
            }
            if ($diff < 120) {
                return $this->translator->translate("common.lastLogin.minuteAgo");
            }
            $diffMinutes = round($diff / 60);
            if ($diff < 1800) {
                return $this->translator->translate("common.lastLogin.minutesAgo", null, ['n' => $diffMinutes]);
            }
            if ($diff < 3600) {
                return $this->translator->translate("common.lastLogin.halfHourAgo");
            }
            if ($diff < 7200) {
                return $this->translator->translate("common.lastLogin.hourAgo");
            }
            $diffHours = round($diff / 3600);
            if ($diff < 86400) {
                return $this->translator->translate("common.lastLogin.hoursAgo", null, ['n' => $diffHours]);
            }
            $diffDays = round($diff / 86400);
            if ($diff < 172800) {
                return $this->translator->translate("common.lastLogin.dayAgo");
            }
            return $this->translator->translate("common.lastLogin.daysAgo", null, ['n' => $diffDays]);
            ;
        });

        $this->template->addFilter('namedayToday', fn($name, $webname): string => $this->translator->translate("team.hasNamedayToday", null, ["name" => '<strong><a href=' . $this->link(":Team:Player:", $webname) . '>' . $name . '</a></strong>']));
        $this->template->addFilter('namedayTommorow', fn($name, $webname): string => $this->translator->translate("team.hasNamedayTommorow", null, ["name" => '<strong><a href=' . $this->link(":Team:Player:", $webname) . '>' . $name . '</a></strong>']));
        $this->template->addFilter('birthdayToday', fn($name, $webname, $year): string => $this->translator->translate("team.hasBirthdayToday", null, ["name" => '<strong><a href=' . $this->link(":Team:Player:", $webname) . '>' . $name . '</a></strong>', "year" => '<strong>' . $year . '.</strong>']));
        $this->template->addFilter('birthdayTommorow', fn($name, $webname, $year): string => $this->translator->translate("team.hasBirthdayTommorow", null, ["name" => '<strong><a href=' . $this->link(":Team:Player:", $webname) . '>' . $name . '</a></strong>', "year" => '<strong>' . $year . '.</strong>']));
    }

    public function renderDefault(): void
    {
        assert($this->template instanceof Template);
        $this->template->liveUsers = $this->userManager->getLiveUsers();
        $this->template->discussions = $this->discussionManager->getListUserAllowed($this->user->getId());
        $this->template->users = $this->userManager->getListOrder(null, "id", "last_login DESC");
        $this->template->currentEvents = $this->eventManager->getCurrentEvents($this->user->getId());

        //$this->debtList->postProcessWithUsers($this->userList->getById(), $debts);    //@todo
        $this->template->debts = $this->debtManager->getListUserAllowed();
        $this->template->notices = $this->newsManager->getListUserAllowed();

        $this->template->today = date('m-d');
        $this->template->tommorow = date('m-d', strtotime('+ 1 day'));
        $this->template->currY = date("Y");
        $this->template->currM = date("m");
        $this->template->eventTypes = $this->eventTypeManager->getListUserAllowed($this->user->getId());

        $this->template->neverLogin = $this->translator->translate("common.never");
    }

    public function actionJump(string $teamSysName): void
    {
        $tk = $this->multiaccountManager->generateNewTk($teamSysName);
        assert($tk instanceof TransferKey);
        $this->redirectUrl("https://$teamSysName.tymy.cz/sign/in?tk=" . $tk->getTransferKey());
    }
}
