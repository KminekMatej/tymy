<?php

namespace Tymy\Module\Core\Factory;

use Closure;
use Contributte\Translation\Translator;
use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Manager\StatusSetManager;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Attendance\Model\StatusSet;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Poll\Model\Option;
use Tymy\Module\Poll\Model\Poll;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\Team\Model\Team;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\User;

class FormFactory
{
    use Nette\SmartObject;

    private array $userPermissions;

    public function __construct(private EventTypeManager $eventTypeManager, private Translator $translator, private StatusSetManager $statusSetManager, private TeamManager $teamManager, private UserManager $userManager, private PermissionManager $permissionManager)
    {
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
                assert($userPermission instanceof Permission);
                $this->userPermissions[$userPermission->getName()] = $userPermission->getCaption() ?: $userPermission->getName();
            }
        }

        return $this->userPermissions;
    }

    public function createEventLineForm(array $eventTypesList, Closure $onSuccess, ?Event $event = null): Form
    {
        $eventTypes = [];

        foreach ($eventTypesList as $eventType) {
            assert($eventType instanceof EventType);
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

        if ($event !== null) {
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

    public function createStatusSetForm(Closure $onSuccess): Multiplier
    {
        return new Multiplier(function (string $statusSetId) use ($onSuccess): \Nette\Application\UI\Form {
                $statusSet = $this->statusSetManager->getById((int) $statusSetId);
                assert($statusSet instanceof StatusSet);
                $form = new Form();
                $form->addHidden("id", $statusSetId);
                $form->addText("name", $this->translator->translate("settings.team"))->setValue($statusSet->getName())->setRequired();
                $form->addSubmit("save")->setHtmlAttribute("title", $this->translator->translate("common.save"));

            foreach ($statusSet->getStatuses() as $status) {
                assert($status instanceof Status);
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

    public function createEventTypeForm(Closure $onSuccess): Multiplier
    {
        $ssList = [];

        foreach ($this->statusSetManager->getIdList() as $statusSet) {
            assert($statusSet instanceof StatusSet);
            $ssList[$statusSet->getId()] = $statusSet->getName();
        }

        return new Multiplier(function (string $eventTypeId) use ($onSuccess, $ssList): \Nette\Application\UI\Form {
                $eventType = $this->eventTypeManager->getById((int) $eventTypeId);
                assert($eventType instanceof EventType);
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

    public function createTeamConfigForm(Closure $onSuccess): Form
    {
        $eventTypes = $this->eventTypeManager->getList();
        $team = $this->teamManager->getTeam();

        $form = new Form();
        $form->addText("name", $this->translator->translate("team.name"))->setValue($team->getName());
        $form->addText("sport", $this->translator->translate("team.sport"))->setValue($team->getSport());
        $form->addSelect("defaultLanguage", $this->translator->translate("team.defaultLanguage"), ["CZ" => "Česky", "EN" => "English", "FR" => "Le français", "PL" => "Polski"])->setValue($team->getDefaultLanguageCode() ?: "CZ");
        $form->addSelect("skin", $this->translator->translate("team.defaultSkin"), $this->teamManager->allSkins)->setValue($team->getSkin());
        $form->addMultiSelect("requiredFields", $this->translator->translate("team.requiredFields"), $this->userManager->getAllFields()["ALL"])->setValue($team->getRequiredFields());

        foreach ($eventTypes as $etype) {
            assert($etype instanceof EventType);
            $form->addText("eventColor_" . $etype->getCode(), $etype->getCaption())
                ->setAttribute("type", "color")
                ->setAttribute("data-color", $etype->getColor())
                ->setValue('#' . $etype->getColor());
        }

        $form->addSubmit("save");

        $form->onSuccess[] = $onSuccess;

        return $form;
    }

    public function createPollConfigForm(Closure $onSuccess, ?Poll $poll = null): Form
    {
        $form = new Form();
        $id = $form->addHidden("id");

        $pollStatuses = [
            Poll::STATUS_DESIGN => $this->translator->translate("poll.design"),
            Poll::STATUS_OPENED => $this->translator->translate("poll.opened"),
            Poll::STATUS_CLOSED => $this->translator->translate("poll.closed"),
            Poll::STATUS_HIDDEN => $this->translator->translate("poll.hidden"),
        ];

        $pollResults = [
            Poll::RESULTS_ALWAYS => $this->translator->translate("poll.always"),
            Poll::RESULTS_AFTER_VOTE => $this->translator->translate("poll.afterVote"),
            Poll::RESULTS_WHEN_CLOSED => $this->translator->translate("poll.whenClosed"),
            Poll::RESULTS_NEVER => $this->translator->translate("poll.never"),
        ];
        $optionTypes = [
            Option::TYPE_TEXT => $this->translator->translate("poll.text"),
            Option::TYPE_NUMBER => $this->translator->translate("poll.number"),
            Option::TYPE_BOOLEAN => $this->translator->translate("poll.bool"),
        ];

        $caption = $form->addText("caption", $this->translator->translate("settings.title"))->setRequired();
        $description = $form->addTextArea("description", $this->translator->translate("settings.description"));
        $status = $form->addSelect("status", $this->translator->translate("settings.status"), $pollStatuses)->setDefaultValue(Poll::STATUS_DESIGN)->setPrompt($this->translator->translate("common.chooseState") . " ...")->setRequired();
        $minItems = $form->addInteger("minItems", $this->translator->translate("poll.minItems"))->setHtmlAttribute("min", 0);
        $maxItems = $form->addInteger("maxItems", $this->translator->translate("poll.maxItems"))->setHtmlAttribute("min", 0);

        /*$minItems->addRule(Form::MAX, null, $form['maxItems']);
        $maxItems->addRule(Form::MIN, null, $form['minItems']);
         * Commented out - was causing recursion overflow in live-form-validation
         */

        $anonymousVotes = $form->addCheckbox("anonymousVotes", $this->translator->translate("poll.anonymousVotes"));
        $changeableVotes = $form->addCheckbox("changeableVotes", $this->translator->translate("poll.setChangeableVotes"))->setDefaultValue(true);
        $displayResults = $form->addSelect("displayResults", $this->translator->translate("poll.displayResults"), $pollResults)->setPrompt($this->translator->translate("common.choose") . " ...")->setDefaultValue(Poll::RESULTS_NEVER);

        $canVote = $form->addSelect("voteRightName", $this->translator->translate("poll.canVote"), $this->getUserPermissions())->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");
        $canDisplayResults = $form->addSelect("resultRightName", $this->translator->translate("poll.canDisplayResults"), $this->getUserPermissions())->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");
        $canAlienVote = $form->addSelect("alienVoteRightName", $this->translator->translate("poll.canAlienVote"), $this->getUserPermissions())->setPrompt("-- " . $this->translator->translate("common.noone") . " --");
        $orderFlag = $form->addInteger("orderFlag", $this->translator->translate("settings.order"));


        if ($poll !== null) {
            $id->setValue($poll->getId());
            $caption->setValue($poll->getCaption())->setHtmlAttribute("data-value", $poll->getCaption());
            $description->setValue($poll->getDescription())->setHtmlAttribute("data-value", $poll->getDescription());
            $status->setValue($poll->getStatus())->setHtmlAttribute("data-value", $poll->getStatus());
            $minItems->setValue($poll->getMinItems())->setHtmlAttribute("data-value", $poll->getMinItems());
            $maxItems->setValue($poll->getMaxItems())->setHtmlAttribute("data-value", $poll->getMaxItems());
            $anonymousVotes->setValue($poll->getAnonymousResults())->setHtmlAttribute("data-value", $poll->getAnonymousResults() ? 1 : 0);
            $changeableVotes->setValue($poll->getChangeableVotes())->setHtmlAttribute("data-value", $poll->getChangeableVotes() ? 1 : 0);
            $displayResults->setValue($poll->getShowResults())->setHtmlAttribute("data-value", $poll->getShowResults() !== '' && $poll->getShowResults() !== '0' ? 1 : 0);
            $orderFlag->setValue($poll->getOrderFlag())->setHtmlAttribute("data-value", $poll->getOrderFlag());
            if ($poll->getVoteRightName()) {
                $canVote->setValue($poll->getVoteRightName())->setHtmlAttribute("data-value", $poll->getVoteRightName());
            }
            if ($poll->getResultRightName()) {
                $canDisplayResults->setValue($poll->getResultRightName())->setHtmlAttribute("data-value", $poll->getResultRightName());
            }
            if ($poll->getAlienVoteRightName()) {
                $canAlienVote->setValue($poll->getAlienVoteRightName())->setHtmlAttribute("data-value", $poll->getAlienVoteRightName());
            }

            //add existing items
            foreach ($poll->getOptions() as $option) {
                assert($option instanceof Option);
                $form->addHidden("option_id_" . $option->getId(), $option->getId());
                $form->addText("option_caption_" . $option->getId(), $this->translator->translate("poll.itemCaption"))->setValue($option->getCaption())->setHtmlAttribute("data-value", $option->getCaption());
                $form->addSelect("option_type_" . $option->getId(), $this->translator->translate("settings.type"), $optionTypes)
                    ->setValue($option->getType())
                    ->setHtmlAttribute("data-value", $option->getType());
            }

            //add template row
            $form->addHidden("option_id_0", 0);
            $form->addText("option_caption_0", $this->translator->translate("poll.itemCaption"))->setHtmlAttribute("data-value");
            $form->addSelect("option_type_0", $this->translator->translate("settings.type"), $optionTypes)->setHtmlAttribute("data-value", Option::TYPE_TEXT)->setDefaultValue(Option::TYPE_TEXT);
        }

        $form->onSuccess[] = $onSuccess;

        $form->addSubmit("save")->setHtmlAttribute("title", $this->translator->translate("common.save"));

        return $form;
    }

    public function createUserConfigForm(Closure $onSuccess, ?User $user): Form
    {
        $form = new Form();
        $id = $form->addHidden("id");

        $genderList = [
            "UNKNOWN" => $this->translator->translate("common.chooseSex", 1) . " ↓",
            "MALE" => $this->translator->translate("team.male", 1),
            "FEMALE" => $this->translator->translate("team.female", 1),
        ];

        $statusList = [
            User::STATUS_INIT => $this->translator->translate("team.INIT", 1),
            User::STATUS_PLAYER => $this->translator->translate("team.PLAYER", 1),
            User::STATUS_MEMBER => $this->translator->translate("team.MEMBER", 1),
            User::STATUS_SICK => $this->translator->translate("team.SICK", 1),
            User::STATUS_DELETED => $this->translator->translate("team.DELETED", 1),
        ];

        $rolesList = [
            User::ROLE_SUPER => $this->translator->translate("team.administrator"),
            User::ROLE_USER => $this->translator->translate("team.userAdmin"),
            User::ROLE_ATTENDANCE => $this->translator->translate("team.attendanceAdmin"),
        ];

        $months = [0 => $this->translator->translate("common.choose") . " ↓"];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = $this->translator->translate("common.months." . strtolower(DateTime::createFromFormat('!m', "$m")->format("F")));
        }

        $gender = $form->addSelect("gender", $this->translator->translate("team.gender"), $genderList);
        $firstName = $form->addText("firstName", $this->translator->translate("team.firstName"));
        $lastName = $form->addText("lastName", $this->translator->translate("team.lastName"));
        $phone = $form->addText("phone", $this->translator->translate("team.phone"))->addRule($form::PATTERN, $this->translator->translate("common.errors.valueInvalid"), '^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\./0-9]*$');
        $email = $form->addText("email", $this->translator->translate("team.email"))->addRule($form::EMAIL);
        $birthDate = $form->addText("birthDate", $this->translator->translate("team.birthDate"))->setHtmlType("date");
        $birthCode = $form->addText("birthCode", $this->translator->translate("team.birthCode"));
        $nameDayMonth = $form->addSelect("nameDayMonth", $this->translator->translate("team.nameDayMonth"), $months)->setCaption($this->translator->translate("_team.chooseMonth") . " ↓")->setHtmlAttribute("title", $this->translator->translate("team.nameDayMonth"));
        $nameDayDay = $form->addInteger("nameDayDay", $this->translator->translate("team.nameDayDay"))->addRule($form::MIN, null, 0)->addRule($form::MAX, null, 31)->setHtmlAttribute("title", $this->translator->translate("team.nameDayDay"));
        $language = $form->addSelect("language", $this->translator->translate("team.language"), Team::LANGUAGES);
        $accountNumber = $form->addText("accountNumber", $this->translator->translate("debt.accountNumber"));

        $callName = $form->addText("callName", $this->translator->translate("team.callName"));
        $canEditCallName = $form->addCheckbox("canEditCallName", $this->translator->translate("team.canEditCallName"));
        $login = $form->addText("login", $this->translator->translate("team.login"))
            ->addRule($form::IS_NOT_IN, $this->translator->translate("team.alerts.loginExists"), $this->userManager->getExistingLoginsExcept($user !== null ? $user->getLogin() : null))
            ->addRule($form::MIN_LENGTH, null, 3)
            ->addRule($form::MAX_LENGTH, null, 20);

        $password = $form->addPassword("password", $this->translator->translate("team.password"));
        $newPasswordAgain = $form->addPassword("newPasswordAgain", $this->translator->translate("team.newPasswordAgain"))->addRule($form::EQUAL, $this->translator->translate("team.errors.passwordMismatch"), $form['password']);
        $canLogin = $form->addCheckbox("canLogin");

        $skin = $form->addSelect("skin", "Skin", $this->teamManager->allSkins);
        $hideDiscDesc = $form->addCheckbox("hideDiscDesc", $this->translator->translate("team.hideDiscDesc"));

        $status = $form->addSelect("status", $this->translator->translate("team.status"), $statusList)->setDisabled([User::STATUS_INIT]);
        $jerseyNumber = $form->addInteger("jerseyNumber", $this->translator->translate("team.jerseyNumber"));

        $street = $form->addText("street", $this->translator->translate("team.street"));
        $city = $form->addText("city", $this->translator->translate("team.city"));
        $zipCode = $form->addText("zipCode", $this->translator->translate("team.zipCode"))->addRule($form::PATTERN, $this->translator->translate("common.errors.valueInvalid"), '^[0-9]{5}(?:-[0-9]{4})?$');

        $roles = $form->addCheckboxList("roles", $this->translator->translate("team.roles", 1), $rolesList);

        if ($user !== null) {
            $id->setValue($user->getId());
            $gender->setValue($user->getGender())->setHtmlAttribute("data-value", $user->getGender());
            $firstName->setValue($user->getFirstName())->setHtmlAttribute("data-value", $user->getFirstName());
            $lastName->setValue($user->getLastName())->setHtmlAttribute("data-value", $user->getLastName());
            $phone->setValue($user->getPhone())->setHtmlAttribute("data-value", $user->getPhone());
            $email->setValue($user->getEmail())->setHtmlAttribute("data-value", $user->getEmail());
            if ($user->getBirthDate() !== null) {
                $birthDate->setValue($user->getBirthDate()->format(BaseModel::DATE_ENG_FORMAT))->setHtmlAttribute("data-value", $user->getBirthDate()->format(BaseModel::DATE_ENG_FORMAT));
            }
            $birthCode->setValue($user->getBirthCode())->setHtmlAttribute("data-value", $user->getBirthCode());
            $accountNumber->setValue($user->getAccountNumber())->setHtmlAttribute("data-value", $user->getAccountNumber());
            if ($user->getNameDayMonth() > 0 && $user->getNameDayMonth() <= 12) {
                $nameDayMonth->setValue($user->getNameDayMonth())->setHtmlAttribute("data-value", $user->getNameDayMonth());
            }

            if ($user->getNameDayDay() > 0 && $user->getNameDayDay() <= 31) {
                $nameDayDay->setValue($user->getNameDayDay())->setHtmlAttribute("data-value", $user->getNameDayDay());
            }

            $language->setValue($user->getLanguage())->setHtmlAttribute("data-value", $user->getLanguage());

            $callName->setValue($user->getCallName())->setHtmlAttribute("data-value", $user->getCallName());
            $canEditCallName->setValue($user->getCanEditCallName())->setHtmlAttribute("data-value", $user->getCanEditCallName());
            $login->setValue($user->getLogin())->setHtmlAttribute("data-value", $user->getLogin());
            $canLogin->setValue($user->getCanLogin())->setHtmlAttribute("data-value", $user->getCanLogin());

            $skin->setValue($user->getSkin())->setHtmlAttribute("data-value", $user->getSkin());
            $hideDiscDesc->setValue($user->getHideDiscDesc())->setHtmlAttribute("data-value", $user->getHideDiscDesc());

            $status->setValue($user->getStatus())->setHtmlAttribute("data-value", $user->getStatus());
            $jerseyNumber->setValue($user->getJerseyNumber())->setHtmlAttribute("data-value", $user->getJerseyNumber());

            $street->setValue($user->getStreet())->setHtmlAttribute("data-value", $user->getStreet());
            $city->setValue($user->getCity())->setHtmlAttribute("data-value", $user->getCity());
            $zipCode->setValue($user->getZipCode())->setHtmlAttribute("data-value", $user->getZipCode());

            $roles->setValue(array_intersect($user->getRoles(), array_keys($rolesList)));
        } else {    //creating new user, login and password are mandatory as well
            $login->setRequired();
            $password->setRequired();
            $newPasswordAgain->setRequired();
        }

        foreach ($form->getControls() as $control) {
            assert($control instanceof BaseControl);
            if (in_array($control->getName(), $this->teamManager->getTeam()->getRequiredFields())) {
                $control->setRequired($this->translator->translate("common.errors.teamValueRequired"));
            }
        }


        $form->onSuccess[] = $onSuccess;

        $form->addSubmit("save");

        return $form;
    }
}
