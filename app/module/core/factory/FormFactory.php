<?php

namespace Tymy\Module\Core\Factory;

use Kdyby\Translation\Translator;
use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Manager\StatusSetManager;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Attendance\Model\StatusSet;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Poll\Model\Poll;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\User;

class FormFactory
{
    use Nette\SmartObject;

    private EventTypeManager $eventTypeManager;
    private StatusSetManager $statusSetManager;
    private EventManager $eventManager;
    private TeamManager $teamManager;
    private UserManager $userManager;
    private Translator $translator;
    private PermissionManager $permissionManager;
    private array $userPermissions;

    public function __construct(EventTypeManager $eventTypeManager, EventManager $eventManager, Translator $translator, StatusSetManager $statusSetManager, TeamManager $teamManager, UserManager $userManager, PermissionManager $permissionManager)
    {
        $this->eventTypeManager = $eventTypeManager;
        $this->eventManager = $eventManager;
        $this->teamManager = $teamManager;
        $this->userManager = $userManager;
        $this->translator = $translator;
        $this->statusSetManager = $statusSetManager;
        $this->permissionManager = $permissionManager;
    }

    /**
     * Get array of user permissions (cached on first call)
     * @return array in the format of name = caption
     */
    private function getUserPermissions(): array
    {
        if (!isset($this->userPermissions)) {
            $this->userPermissions = [];
            foreach ($this->permissionManager->getByType(Permission::TYPE_USER) as $userPermission) {
                /* @var $userPermission Permission */
                $this->userPermissions[$userPermission->getName()] = $userPermission->getCaption();
            }
        }

        return $this->userPermissions;
    }

    /**
     * @return Form
     */
    public function createEventLineForm(array $eventTypesList, array $onSuccess, ?Event $event = null): Form
    {
        $eventTypes = [];

        foreach ($eventTypesList as $eventType) {
            /* @var $eventType EventType */
            $eventTypes[$eventType->getId()] = $eventType->getCaption();
        }

        $form = new Form();

        //     $id = $form->addHidden("id", $id);

        $type = $form->addSelect("eventTypeId", null, $eventTypes)->setHtmlAttribute("data-name", "eventTypeId")->setRequired();
        $caption = $form->addText("caption")->setHtmlAttribute("data-name", "caption")->setRequired();
        $description = $form->addTextArea("description", null, null, 1)->setHtmlAttribute("data-name", "description");
        $start = $form->addText("startTime")->setHtmlAttribute("data-name", "startTime")->setHtmlType("datetime-local")->setValue((new DateTime("+ 24 hours"))->format(BaseModel::DATETIME_ISO_NO_SECS_FORMAT))->setRequired();
        $end = $form->addText("endTime")->setHtmlAttribute("data-name", "endTime")->setHtmlType("datetime-local")->setValue((new DateTime("+ 25 hours"))->format(BaseModel::DATETIME_ISO_NO_SECS_FORMAT))->setRequired();
        $close = $form->addText("closeTime")->setHtmlAttribute("data-name", "closeTime")->setHtmlType("datetime-local")->setValue((new DateTime("+ 23 hours"))->format(BaseModel::DATETIME_ISO_NO_SECS_FORMAT))->setRequired();
        $place = $form->addText("place")->setHtmlAttribute("data-name", "place");
        $link = $form->addText("link")->setHtmlAttribute("data-name", "link");
        $canView = $form->addSelect("canView", null, $this->getUserPermissions())->setHtmlAttribute("data-name", "canView")->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");
        $canPlan = $form->addSelect("canPlan", null, $this->getUserPermissions())->setHtmlAttribute("data-name", "canPlan")->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");
        $canResult = $form->addSelect("canResult", null, $this->getUserPermissions())->setHtmlAttribute("data-name", "canResult")->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");

        if ($event) {
            $form->addHidden("id", $event->getId());
            $type->setValue($event->getEventTypeId());
            $caption->setValue($event->getCaption());
            $description->setValue($event->getDescription());
            $start->setValue($event->getStartTime()->format(BaseModel::DATETIME_ISO_FORMAT));
            $end->setValue($event->getEndTime()->format(BaseModel::DATETIME_ISO_FORMAT));
            $close->setValue($event->getCloseTime()->format(BaseModel::DATETIME_ISO_FORMAT));
            $place->setValue($event->getPlace());
            $link->setValue($event->getLink());
            if (!empty($event->getViewRightName())) {
                $canView->setValue($event->getViewRightName());
            }
            if (!empty($event->getPlanRightName())) {
                $canPlan->setValue($event->getPlanRightName());
            }

            if (!empty($event->getResultRightName())) {
                $canResult->setValue($event->getResultRightName());
            }
        }

        $form->addSubmit("save")->setHtmlAttribute("title", $this->translator->translate("common.saveAll"));
        $form->onSuccess[] = $onSuccess;

        return $form;
    }

    public function createStatusSetForm(array $onSuccess): Multiplier
    {
        return new Multiplier(function (string $statusSetId) use ($onSuccess) {
                /* @var $statusSet StatusSet */
                $statusSet = $this->statusSetManager->getById(intval($statusSetId));
                $form = new Form();
                $form->addHidden("id", $statusSetId);
                $form->addText("name", $this->translator->translate("settings.team"))->setValue($statusSet->getName())->setRequired();
                $form->addSubmit("save")->setHtmlAttribute("title", $this->translator->translate("common.save"));

            foreach ($statusSet->getStatuses() as $status) {
                /* @var $status Status */
                $form->addText("status_{$status->getId()}_caption", $this->translator->translate("common.name"))
                    ->setValue($status->getCaption())
                    ->setHtmlAttribute("placeholder", $this->translator->translate("common.name"))
                    ->setRequired()
                    ->setMaxLength(50);
                $form->addText("status_{$status->getId()}_code", $this->translator->translate("status.code"))
                    ->setValue($status->getCode())
                    ->setHtmlAttribute("placeholder", $this->translator->translate("status.code"))
                    ->setHtmlAttribute("size", "5")
                    ->setRequired()
                    ->setMaxLength(3);
                $form->addText("status_{$status->getId()}_color", $this->translator->translate("status.color"))
                    ->setValue("#" . $status->getColor())
                    ->setHtmlAttribute("placeholder", $this->translator->translate("status.color"))
                    ->setRequired()
                    ->setMaxLength(6)
                    ->setHtmlAttribute("type", "color");
                $form->addText("status_{$status->getId()}_icon", $this->translator->translate("status.icon"))
                    ->setValue($status->getIcon())
                    ->setHtmlAttribute("id", "iconpicker-{$status->getId()}")
                    ->setHtmlAttribute("data-toggle", "dropdown")
                    ->setHtmlAttribute("type", "hidden");
            }
                $form->onSuccess[] = $onSuccess;
                return $form;
        });
    }

    public function createEventTypeForm(array $onSuccess): Multiplier
    {
        $ssList = [];

        foreach ($this->statusSetManager->getIdList() as $statusSet) {
            /* @var $statusSet StatusSet */
            $ssList[$statusSet->getId()] = $statusSet->getName();
        }

        return new Multiplier(function (string $eventTypeId) use ($onSuccess, $ssList) {
                /* @var $eventType EventType */
                $eventType = $this->eventTypeManager->getById(intval($eventTypeId));
                $form = new Form();
                $form->addHidden("id", $eventTypeId);
                $form->addText("code", $this->translator->translate("status.code"))
                    ->setValue($eventType->getCode())
                    ->setHtmlAttribute("size", "5")
                    ->setRequired()
                    ->setMaxLength(3);
                $form->addText("caption", $this->translator->translate("common.name"))
                    ->setValue($eventType->getCaption())
                    ->setRequired();
                $form->addText("color", $this->translator->translate("status.color"))
                    ->setValue("#" . $eventType->getColor())
                    ->setMaxLength(6)
                    ->setHtmlAttribute("type", "color")
                    ->setRequired();

                $form->addSelect("preStatusSet", $this->translator->translate("status.preStatus"), $ssList)
                    ->setValue($eventType->getPreStatusSetId());
                $form->addSelect("postStatusSet", $this->translator->translate("status.postStatus"), $ssList)
                    ->setValue($eventType->getPostStatusSetId());

                $form->addSubmit("save")->setHtmlAttribute("title", $this->translator->translate("common.save"));
                $form->onSuccess[] = $onSuccess;
                return $form;
        });
    }

    public function createTeamConfigForm(array $onSuccess): Form
    {
        $eventTypes = $this->eventTypeManager->getList();
        $team = $this->teamManager->getTeam();

        $form = new Form();
        $form->addText("name", $this->translator->translate("team.name"))->setValue($team->getName());
        $form->addText("sport", $this->translator->translate("team.sport"))->setValue($team->getSport());
        $form->addSelect("defaultLanguage", $this->translator->translate("team.defaultLanguage"), ["CZ" => "Česky", "EN" => "English", "FR" => "Le français", "PL" => "Polski"])->setValue($team->getDefaultLanguageCode() ?: "CZ");
        $form->addSelect("skin", $this->translator->translate("team.defaultSkin"), TeamManager::SKINS)->setValue($team->getSkin());
        $form->addMultiSelect("requiredFields", $this->translator->translate("team.requiredFields"), $this->userManager->getAllFields()["ALL"])->setValue($team->getRequiredFields());

        foreach ($eventTypes as $etype) {
            /* @var $etype EventType */
            $form->addText("eventColor_" . $etype->getCode(), $etype->getCaption())
                ->setAttribute("type", "color")
                ->setAttribute("data-color", $etype->getColor())
                ->setValue('#' . $etype->getColor());
        }

        $form->addSubmit("save");

        $form->onSuccess[] = $onSuccess;

        return $form;
    }

    public function createPollConfigForm(array $onSuccess, ?Poll $poll = null): Form
    {
        $form = new Form();

        $pollStatuses = [
            Poll::STATUS_DESIGN => $this->translator->translate("poll.design"),
            Poll::STATUS_OPENED => $this->translator->translate("poll.opened"),
            Poll::STATUS_CLOSED => $this->translator->translate("poll.closed"),
        ];

        $pollResults = [
            Poll::RESULTS_ALWAYS => $this->translator->translate("poll.always"),
            Poll::RESULTS_AFTER_VOTE => $this->translator->translate("poll.afterVote"),
            Poll::RESULTS_WHEN_CLOSED => $this->translator->translate("poll.whenClosed"),
            Poll::RESULTS_NEVER => $this->translator->translate("poll.never"),
        ];

        $caption = $form->addText("caption", $this->translator->translate("settings.title"))->setRequired();
        $description = $form->addTextArea("description", $this->translator->translate("settings.description"));
        $status = $form->addSelect("status", $this->translator->translate("settings.status"), $pollStatuses)->setDefaultValue(Poll::STATUS_DESIGN)->setPrompt($this->translator->translate("common.chooseState") . " ...")->setRequired();
        $minItems = $form->addInteger("minItems", $this->translator->translate("poll.minItems"))->setHtmlAttribute("min", 0)->setRequired();
        $maxItems = $form->addInteger("maxItems", $this->translator->translate("poll.maxItems"))->setHtmlAttribute("min", 0)->setRequired();

        /*$minItems->addRule(Form::MAX, null, $form['maxItems']);
        $maxItems->addRule(Form::MIN, null, $form['minItems']);
         * Commented out - was causing recursion overflow in live-form-validation
         */

        $anonymousVotes = $form->addCheckbox("anonymousVotes", $this->translator->translate("poll.anonymousVotes"));
        $changeableVotes = $form->addCheckbox("changeableVotes", $this->translator->translate("poll.setChangeableVotes"));
        $displayResults = $form->addSelect("displayResults", $this->translator->translate("poll.displayResults"), $pollResults)->setPrompt($this->translator->translate("common.choose") . " ...");
        
        $canVote = $form->addSelect("canVote", $this->translator->translate("poll.canVote"), $this->getUserPermissions())->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");
        $canDisplayResults = $form->addSelect("canDisplayResults", $this->translator->translate("poll.canDisplayResults"), $this->getUserPermissions())->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");
        $canAlienVote = $form->addSelect("canAlienVote", $this->translator->translate("poll.canAlienVote"), $this->getUserPermissions())->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");

        if ($poll) {
            $caption->setValue($poll->getCaption())->setHtmlAttribute("data-value", $poll->getCaption());
            $description->setValue($poll->getDescription())->setHtmlAttribute("data-value", $poll->getDescription());
            $status->setValue($poll->getStatus())->setHtmlAttribute("data-value", $poll->getStatus());
            $minItems->setValue($poll->getMinItems())->setHtmlAttribute("data-value", $poll->getMinItems());
            $maxItems->setValue($poll->getMaxItems())->setHtmlAttribute("data-value", $poll->getMaxItems());
            $anonymousVotes->setValue($poll->getAnonymousResults())->setHtmlAttribute("data-value", $poll->getAnonymousResults());
            $changeableVotes->setValue($poll->getChangeableVotes())->setHtmlAttribute("data-value", $poll->getChangeableVotes());
            $displayResults->setValue($poll->getShowResults())->setHtmlAttribute("data-value", $poll->getShowResults());
            if ($poll->getVoteRightName()) {
                $canVote->setValue($poll->getVoteRightName())->setHtmlAttribute("data-value", $poll->getVoteRightName());
            }
            if ($poll->getResultRightName()) {
                $canDisplayResults->setValue($poll->getResultRightName())->setHtmlAttribute("data-value", $poll->getResultRightName());
            }
            if ($poll->getAlienVoteRightName()) {
                $canAlienVote->setValue($poll->getAlienVoteRightName())->setHtmlAttribute("data-value", $poll->getAlienVoteRightName());
            }
        }

        $form->onSuccess[] = $onSuccess;

        $form->addSubmit("save")->setHtmlAttribute("title", $this->translator->translate("common.save"));

        return $form;
    }

    public function createUserConfigForm(array $onSuccess, ?User $user): Form
    {
        $form = new Form();

        $genderList = [
            "MALE" => $this->translator->translate("team.male", 1),
            "FEMALE" => $this->translator->translate("team.female", 1),
        ];

        $gender = $form->addSelect("gender", $this->translator->translate("team.gender"), $genderList)->setCaption($this->translator->translate("common.chooseSex", 1) . " ↓");
        $firstName = $form->addText("firstName", $this->translator->translate("team.firstName"));



        /*
         * <tr>
            <th>{_team.firstName}:</th><td><input name="firstName" data-value="{$player->getFirstName()}" type="text" value="{$player->getFirstName()}" n:class="col-6, form-control, in_array('firstName',$player->getErrFields()) ? is-invalid" /></td>
        </tr>
        <tr>
            <th>{_team.lastName}:</th><td><input name="lastName" data-value="{$player->getLastName()}" type="text" value="{$player->getLastName()}" n:class="col-6, form-control, in_array('lastName',$player->getErrFields()) ? is-invalid" /></td>
        </tr>
        <tr>
            <th>{_team.phone}:</th><td><input name="phone" data-value="{$player->getPhone()}" type="text" value="{$player->getPhone()}" n:class="col-6, form-control, in_array('phone',$player->getErrFields()) ? is-invalid" /></td>
        </tr>
        <tr>
            <th>{_team.email}:</th><td><div class="input-group"><input name="email" data-value="{$player->getEmail()}" type="text" value="{$player->getEmail()}" n:class="col-6, form-control, in_array('email',$player->getErrFields()) ? is-invalid" style="min-width: 200px"/><div class="input-group-append"><span class="input-group-text"><a n:tag-if="$player->getEmail() != ''" href="mailto:{$player->getEmail()}"><i class="fa fa-envelope" aria-hidden="true"></i></a></span></div></div></td>
        </tr>
        <tr>
            <th>{_team.birthDate}:</th><td><input name="birthDate" data-value="{$player->getBirthDate()}" type="date" value="{$player->getBirthDate()}" n:class="col-6, form-control, in_array('birthDate',$player->getErrFields()) ? is-invalid" /></td>
        </tr>
        <tr>
            <th>{_team.nameDayMonth}:</th><td>
                <select n:class="col-3, form-control, in_array('nameDayMonth',$player->getErrFields()) ? is-invalid" style="min-width: 160px" name="nameDayMonth" data-value="{$player->getNameDayMonth() == 0 ? '' : $player->getNameDayMonth()}">
                    <option value="">{_team.chooseMonth} ↓</option>
                    <option n:for="$m=1; $m<=12; $m++" value="{$m}" n:attr="selected => $player->getNameDayMonth()==$m">{$m|monthName}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{_team.nameDayDay}:</th><td><input name="nameDayDay" data-value="{$player->getNameDayDay()}" type="number" value="{$player->getNameDayDay()}" min="1" max="31" n:class="col-2, form-control, in_array('nameDayDay',$player->getErrFields()) ? is-invalid" style="min-width: 160px" /></td>
        </tr>
        <tr>
            <th>{_team.language}:</th>
            <td>
                <select n:class="col-3, form-control, in_array('language',$player->getErrFields()) ? is-invalid"  style="min-width: 120px" name="language" data-value="{$player->getLanguage()}">
                    <option value="CZ" n:attr="selected => $player->getLanguage()=='CZ'">Česky</option>
                    <option value="EN" n:attr="selected => $player->getLanguage()=='EN'">English</option>
                    <option value="FR" n:attr="selected => $player->getLanguage()=='FR'">Le français</option>
                    <option value="PL" n:attr="selected => $player->getLanguage()=='PL'">Polski</option>
                </select>
            </td>
        </tr>
         */
        if ($user) {
            $gender->setValue($user->getGender());
        }

        $form->onSuccess[] = $onSuccess;

        $form->addSubmit("save");

        return $form;
    }
}
