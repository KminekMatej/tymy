<?php

namespace Tymy\App\Presenters;

use Nette\Application\UI\Form;
use Nette\Utils\Strings;
use stdClass;
use Tapi\EventListResource;
use Tapi\UserResource;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\Poll\Manager\OptionManager;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Poll\Model\Option;
use Tymy\Module\Poll\Model\Poll;

class SettingsPresenter extends SecuredPresenter
{

    /** @inject */
    public DiscussionManager $discussionManager;

    /** @inject */
    public EventManager $eventManager;

    /** @inject */
    public PollManager $pollManager;

    /** @inject */
    public OptionManager $optionManager;

    /** @inject */
    public PermissionManager $permissionManager;

    /** @inject */
    public EventTypeManager $eventTypeManager;

    /** @inject */
    public StatusManager $statusManager;

    protected function startup()
    {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("settings.setting", 2), "link" => $this->link("Settings:")]]);
        $this->template->addFilter("typeColor", function ($type) {
            $color = $this->supplier->getEventColors();
            return $color[$type];
        });
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->template->eventTypes = $this->eventTypeManager->getList();
        $this->template->statusList = $this->statusManager->getAllStatusCodes();
        $this->template->userPermissions = $this->permissionManager->getByType(Permission::TYPE_USER);
        $this->template->systemPermissions = $this->permissionManager->getByType(Permission::TYPE_SYSTEM);
    }

    public function actionDiscussions($discussion = NULL)
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("discussion.discussion", 2), "link" => $this->link("Settings:discussions")]]);
        if (!is_null($discussion)) {
            $this->setView("discussion");
        } else {
            $this->template->isNew = false;
            $discussions = $this->discussionManager->getList();
            $this->template->discussions = $discussions;
            $this->template->discussionsCount = count($discussions);
        }
    }

    public function actionEvents($event = NULL, $page = 1)
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("event.event", 2), "link" => $this->link("Settings:events")]]);
        if (!is_null($event)) {
            $this->setView("event");
        } else {
            $this->template->isNew = false;
            $page = is_numeric($page) ? $page : 1;
            $limit = EventListResource::PAGING_EVENTS_PER_PAGE;
            $offset = ($page - 1) * $limit;
            $this->template->events = $this->eventManager->getList(null, "id", $limit, $offset); // get all events
            $allEventsCount = $this->eventManager->countAllEvents();
            $this->template->eventsCount = $allEventsCount;
            $this->template->currentPage = $page;
            $this->template->lastPage = ceil($allEventsCount / $limit);
            $this->template->pagination = $this->pagination($allEventsCount, $limit, $page, 5);
        }
    }

    public function actionPolls($poll = NULL)
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("poll.poll", 2), "link" => $this->link("Settings:polls")]]);
        if (!is_null($poll)) {
            $this->setView("poll");
        } else {
            $this->template->isNew = false;
            $this->template->polls = $this->pollManager->getList();
        }
    }

    public function actionReports()
    {
        //TODO
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("report.report", 2), "link" => $this->link("Settings:reports")]]);
    }

    public function actionPermissions($permission = NULL)
    {
        $this->allowSys('IS_ADMIN');

        if (!is_null($permission)) {
            $this->setView("permission");
        } else {
            $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("permission.permission", 2), "link" => $this->link("Settings:permissions")]]);
        }
    }

    public function actionApp()
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("settings.application"), "link" => $this->link("Settings:app")]]);
        $currentVersion = $this->supplier->getVersion(0);
        $this->template->version = $currentVersion;
        $previousPatch = NULL;
        $firstMinor = NULL;
        foreach ($this->supplier->getVersions() as $version) {
            if (empty($previousPatch) && ($currentVersion->major != $version->major || $currentVersion->minor != $version->minor || $currentVersion->patch != $version->patch)) {
                $previousPatch = $version;
            }
            if ($currentVersion->major == $version->major && $currentVersion->minor == $version->minor && $version->patch == 0) {
                $firstMinor = $version;
            }
        }
        if ($previousPatch === NULL)
            $previousPatch = $this->supplier->getVersion(count($this->supplier->getVersions()));
        $this->template->previousPatchVersion = $previousPatch;
        $this->template->firstMinorVersion = $firstMinor;

        $this->template->allSkins = $this->supplier->getAllSkins();
    }

    public function renderDiscussion_new()
    {
        $this->allowSys("DSSETUP");

        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("discussion.discussion", 2), "link" => $this->link("Settings:discussions")],
            "3" => ["caption" => $this->translator->translate("discussion.new")]
        ]);
        $this->template->isNew = true;
        $this->template->discussion = (object) [
                    "id" => -1,
                    "caption" => "",
                    "description" => "",
                    "publicRead" => FALSE,
                    "editablePosts" => TRUE,
                    "order" => 0,
        ];

        $this->setView("discussion");
    }

    public function renderDiscussion($discussion)
    {
        $this->allowSys("DSSETUP");

        //RENDERING DISCUSSION DETAIL
        $discussionObj = $this->discussionManager->getByWebName($discussion);
        if ($discussionObj == NULL) {
            $this->flashMessage($this->translator->translate("discussion.errors.discussionNotExists", NULL, ['id' => $discussionId]), "danger");
            $this->redirect('Settings:events');
        }
        $this->setLevelCaptions(["3" => ["caption" => $discussionObj->getCaption(), "link" => $this->link("Settings:discussions", $discussionObj->getWebName())]]);
        $this->template->isNew = FALSE;
        $this->template->discussion = $discussionObj;
    }

    public function renderPermission_new()
    {
        $this->allowSys("IS_ADMIN");

        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("permission.permission", 2), "link" => $this->link("Settings:permissions")],
            "3" => ["caption" => $this->translator->translate("permission.newPermission")]
        ]);
        $this->template->isNew = true;

        $users = $this->userManager->getIdList();

        $perm = (object) [
                    "id" => -1,
                    "name" => "",
                    "caption" => "",
                    "type" => "USR",
                    "revokedRoles" => [],
                    "revokedStatuses" => [],
                    "revokedUsers" => [],
        ];

        $this->template->allowances = ["allowed" => "Povoleno", "revoked" => "Zakázáno"];
        $this->template->statuses = ["PLAYER" => "Hráč", "SICK" => "Marod", "MEMBER" => "Člen"];
        $this->template->roles = $this->getAllRoles();
        $this->template->users = $users;
        $this->template->perm = $perm;

        $this->template->rolesRule = "revoked";
        $this->template->statusesRule = "revoked";
        $this->template->usersRule = "revoked";

        $this->setView("permission");
    }

    public function renderPermission($permission)
    {
        $this->allowSys("IS_ADMIN");

        $perm = $this->permissionManager->getByWebName($permission);
        if ($perm == NULL) {
            $this->flashMessage($this->translator->translate("permission.errors.permissionNotExists", NULL, ['id' => $permission]), "danger");
            $this->redirect('Settings:events');
        }

        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("permission.permission", 2), "link" => $this->link("Settings:permissions")],
            "3" => ["caption" => $perm->getName(), "link" => $this->link("Settings:permissions", $perm->getWebname())]
        ]);

        $users = $this->userManager->getIdList();

        $this->template->lastEditedUser = $users[$perm->getUpdatedById()] ?? null;
        $this->template->allowances = ["allowed" => "Povoleno", "revoked" => "Zakázáno"];
        $this->template->statuses = ["PLAYER" => "Hráč", "SICK" => "Marod", "MEMBER" => "Člen"];
        $this->template->roles = $this->getAllRoles();

        $this->template->rolesRule = empty($perm->getAllowedRoles()) && empty($perm->getRevokedRoles()) ? null : (empty($perm->getRevokedRoles()) ? "allowed" : "revoked");
        $this->template->statusesRule = empty($perm->getAllowedStatuses()) && empty($perm->getRevokedStatuses()) ? null : (empty($perm->getRevokedStatuses()) ? "allowed" : "revoked");
        $this->template->usersRule = empty($perm->getAllowedUsers()) && empty($perm->getRevokedUsers()) ? null : (empty($perm->getRevokedUsers()) ? "allowed" : "revoked");

        $this->template->users = $users;
        $this->template->perm = $perm;
        $this->template->isNew = false;
    }

    public function renderEvent_new()
    {
        $this->allowSys('EVE_CREATE');

        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("event.event", 2), "link" => $this->link("Settings:events")],
            "3" => ["caption" => $this->translator->translate("event.new", 2)]
        ]);
        $this->template->isNew = true;

        $events = [(object) [
                "id" => -1,
                "caption" => "",
                "description" => "",
                "startTime" => date("c"),
                "endTime" => date("c"),
                "closeTime" => date("c"),
                "place" => "",
                "link" => "",
        ]];
        $this->template->events = $events;

        $this->setView("events");
    }

    public function renderMultiaccount()
    {
        $this->setLevelCaptions(["3" => ["caption" => $this->translator->translate("settings.multiaccount", 1), "link" => $this->link("Settings:multiaccounts")]]);
        $this->template->multiaccounts = $this->multiAccountManager->getList();
    }

    public function renderEvent($event)
    {
        $this->allowSys('EVE_UPDATE');

        //RENDERING EVENT DETAIL
        $eventId = $this->parseIdFromWebname($event);
        /* @var $eventObj Event */
        $eventObj = $this->eventManager->getById($eventId);
        if ($eventObj == NULL) {
            $this->flashMessage($this->translator->translate("event.errors.eventNotExists", NULL, ['id' => $eventId]), "danger");
            $this->redirect('Settings:events');
        }

        $this->setLevelCaptions(["3" => ["caption" => $eventObj->getCaption(), "link" => $this->link("Settings:events", $eventObj->getWebName())]]);
        $this->template->event = $eventObj;
    }

    public function renderPoll_new()
    {
        $this->allowSys('ASK.VOTE_UPDATE');

        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("poll.poll", 2), "link" => $this->link("Settings:polls")],
            "3" => ["caption" => $this->translator->translate("poll.new")]
        ]);
        $this->template->isNew = true;

        $polls = [(object) [
                "id" => -1,
                "caption" => "",
                "description" => "",
                "status" => "DESIGN",
                "minItems" => "",
                "maxItems" => "",
                "mainMenu" => FALSE,
                "anonymousResults" => FALSE,
                "changeableVotes" => FALSE,
                "showResults" => "NEVER",
                "orderFlag" => 0
        ]];
        $this->template->polls = $polls;

        $this->setView("polls");
    }

    public function renderPoll($poll)
    {
        $this->allowSys('ASK.VOTE_UPDATE');

        //RENDERING POLL DETAIL
        $pollId = $this->parseIdFromWebname($poll);
        /* @var $pollObj Poll */
        $pollObj = $this->pollManager->getById($pollId);
        if ($pollObj == NULL) {
            $this->flashMessage($this->translator->translate("poll.errors.pollNotExists", NULL, ['id' => $pollId]), "danger");
            $this->redirect('Settings:polls');
        }
        if (count($pollObj->getOptions()) == 0) {
            $pollObj->setOptions([(new Option())->setId(-1)->setPollId($pollId)->setCaption("")->setType("TEXT")]);
        }
        $this->setLevelCaptions(["3" => ["caption" => $pollObj->getCaption(), "link" => $this->link("Settings:polls", $pollObj->getWebName())]]);
        $this->template->poll = $pollObj;
    }

    public function renderDefault()
    {
        $this->template->accessibleSettings = $this->getAccessibleSettings();
    }

    public function handleMultiaccountRemove($team)
    {
        $this->multiAccountManager->delete($team);
        $this->flashMessage($this->translator->translate("common.alerts.multiaccountRemoved", NULL, ['team' => $team]), "success");
        $this->redirect("Settings:multiaccount");
    }

    public function handleEventsEdit()
    {
        $this->allowSys('EVE_UPDATE');

        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        foreach ($binders as $bind) {
            $this->editEvent($bind);
        }
    }

    public function handleEventsCreate()
    {
        $this->allowSys('EVE_CREATE');

        $binders = $this->getRequest()->getPost()["binders"];

        foreach ($binders as $bind) {
            $this->eventManager->create($bind["changes"]);
        }

        $this->redirect('Settings:events');
    }

    public function handleEventEdit()
    {
        $this->editEvent($this->getRequest()->getPost());
    }

    public function handleEventDelete()
    {
        $bind = $this->getRequest()->getPost();
        $this->eventManager->delete($bind["id"]);
    }

    public function handleDiscussionsEdit()
    {
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        foreach ($binders as $bind) {
            $this->discussionManager->update($bind["changes"], $bind["id"]);
        }
    }

    public function handleDiscussionCreate()
    {
        $discussionData = (object) $this->getRequest()->getPost()["changes"]; // new discussion is always as ID 1
        $this->discussionManager->create($discussionData);
        $this->redirect('Settings:discussions');
    }

    public function handleDiscussionEdit()
    {
        $bind = $this->getRequest()->getPost();
        $this->discussionManager->update($bind["changes"], $bind["id"]);
    }

    public function handleDiscussionDelete()
    {
        $bind = $this->getRequest()->getPost();
        $this->discussionManager->delete($bind["id"]);
    }

    public function handlePollsEdit()
    {
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        foreach ($binders as $bind) {
            $this->pollManager->update($bind["changes"], $bind["id"]);
        }
    }

    public function handlePollCreate()
    {
        $this->pollManager->create($this->getRequest()->getPost()["changes"]);
        $this->redirect('Settings:polls');
    }

    public function handlePollEdit()
    {
        $bind = $this->getRequest()->getPost();
        $this->pollManager->update($bind["changes"], $bind["id"]);
    }

    public function handlePollDelete()
    {
        $bind = $this->getRequest()->getPost();
        $this->pollManager->delete($bind["id"]);
    }

    public function handlePollOptionsEdit($poll)
    {
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        $pollId = $this->parseIdFromWebname($poll);
        foreach ($binders as $bind) {
            $bind["changes"]["pollId"] = $pollId;
            $this->editPollOption($bind);
        }
    }

    public function handlePollOptionCreate($poll)
    {
        $pollData = $this->getRequest()->getPost()[1]; // new poll option is always as item 1
        $pollId = $this->parseIdFromWebname($poll);
        $pollData["pollId"] = $pollId;
        $this->optionManager->create($pollData);
    }

    public function handlePollOptionEdit($poll)
    {
        $bind = $this->getRequest()->getPost();
        $bind["changes"]["pollId"] = $this->parseIdFromWebname($poll);
        $this->editPollOption($bind);
    }

    public function handlePollOptionDelete($poll)
    {
        $bind = $this->getRequest()->getPost();
        $bind["pollId"] = $this->parseIdFromWebname($poll);
        $this->optionManager->delete($bind["pollId"], $bind["id"]);
    }

    public function handlePermissionCreate()
    {
        $bind = $this->getRequest()->getPost();
        /* @var $createdPermission Permission */
        $createdPermission = $this->permissionManager->create($this->composePermissionData($bind["changes"]));

        $this->redirect("Settings:permissions", [Strings::webalize($createdPermission->getName())]);
    }

    public function handlePermissionEdit()
    {
        $bind = $this->getRequest()->getPost();

        $data = $this->composePermissionData($bind["changes"]);

        $updatedPermission = $this->permissionManager->update($data, $bind["id"]);

        if (array_key_exists("name", $data)) {   //if name has been changed, redirect to a new name is neccessary
            $this->redirect("Settings:permissions", [Strings::webalize($updatedPermission->getName())]);
        }
    }

    public function handlePermissionDelete()
    {
        $bind = $this->getRequest()->getPost();
        $this->permissionManager->delete($bind["id"]);
    }

    private function editEvent($bind)
    {
        if (array_key_exists("startTime", $bind["changes"])) {
            $bind["changes"]["startTime"] = gmdate("Y-m-d\TH:i:s\Z", strtotime($bind["changes"]["startTime"]));
        }

        if (array_key_exists("endTime", $bind["changes"])) {
            $bind["changes"]["endTime"] = gmdate("Y-m-d\TH:i:s\Z", strtotime($bind["changes"]["endTime"]));
        }

        if (array_key_exists("closeTime", $bind["changes"])) {
            $bind["changes"]["closeTime"] = gmdate("Y-m-d\TH:i:s\Z", strtotime($bind["changes"]["closeTime"]));
        }

        $this->eventManager->update($bind["changes"], $bind["id"]);
    }

    /**
     * Update or create poll option
     * 
     * @param array $bind
     * @return Option Created / updated option
     */
    private function editPollOption($bind): Option
    {
        if ($bind["id"] == -1) {
            return $this->optionManager->create($bind["changes"]);
        } else {
            return $this->optionManager->update($bind["changes"], $bind["id"]);
        }
    }

    /**
     * Create input array for permission, containing name, caption, allowedRoles (or revokedRoles), allowedStatuses (or revokedStatuses) and , allowedUsers (or revokedUsers)
     * @param array $changes
     * @return array
     */
    private function composePermissionData(array $changes): array
    {
        if (array_key_exists("name", $changes)) {
            $output["name"] = $changes["name"];
        }
        if (array_key_exists("caption", $changes)) {
            $output["caption"] = $changes["caption"];
        }

        if (array_key_exists("roleAllowance", $changes)) { //set either allowed or revoked roles
            $roles = array_key_exists("roles", $changes) && is_array($changes["roles"]) ? $changes["roles"] : [];
            $output[$changes["roleAllowance"] == "allowed" ? "allowedRoles" : "revokedRoles"] = $roles;
        }

        if (array_key_exists("statusAllowance", $changes)) { //set either allowed or revoked statuses
            $statuses = array_key_exists("statuses", $changes) && is_array($changes["statuses"]) ? $changes["statuses"] : [];
            $output[$changes["statusAllowance"] == "allowed" ? "allowedStatuses" : "revokedStatuses"] = $statuses;
        }

        if (array_key_exists("userAllowance", $changes)) { //set either allowed or revoked users
            $userList = [];
            foreach ($changes as $key => $value) {
                if (strpos($key, "userCheck") !== FALSE && $value == "true") {
                    $userList[] = (int) explode("_", $key)[1];
                }
            }
            $output[$changes["userAllowance"] == "allowed" ? "allowedUsers" : "revokedUsers"] = $userList;
        }

        return $output;
    }

    /**
     * Shortcut for allowing user with specific SYS permission.
     * If user is not allowed to perform such thing, message is shown and gets redirected to Settings homepage
     * 
     * @param string $permissionName
     * @return void
     */
    private function allowSys(string $permissionName): void
    {
        if (!$this->getUser()->isAllowed($this->user->getId(), Privilege::SYS($permissionName))) {
            $this->notAllowed();
        }
    }

    private function notAllowed()
    {
        $this->flashMessage($this->translator->translate("common.alerts.notPermitted"));
        $this->redirect("Settings:");
    }

    public function createComponentAddMaForm()
    {
        $form = new Form();
        $form->addText("sysName", $this->translator->translate("team.team", 1));
        $form->addText("username", $this->translator->translate("sign.username"));
        $form->addPassword("password", $this->translator->translate("sign.password"));
        $form->addSubmit("save");
        $multiAccountManager = $this->multiAccountManager;
        $form->onSuccess[] = function (Form $form, stdClass $values) use ($multiAccountManager) {
            /* @var $multiAccountManager MultiaccountManager */
            $multiAccountManager->create([
                "login" => $values->username,
                "password" => $values->password,
                    ], $values->sysName);

            $this->flashMessage($this->translator->translate("common.alerts.multiaccountAdded", NULL, ["team" => $values->sysName]));
            $this->redirect("Settings:multiaccount");
        };
        return $form;
    }

    public function createComponentUserConfigForm()
    {
        $form = new Form();
        $form->addSelect("skin", "Skin", $this->supplier->getAllSkins())->setValue($this->supplier->getSkin());
        $form->addSubmit("save");
        $form->onSuccess[] = function (Form $form, stdClass $values) {
            $userNeon = $this->supplier->getUserNeon();
            $userNeon->skin = $values->skin;
            $this->supplier->saveUserNeon($this->getUser()->getId(), (array) $userNeon);
            $this->flashMessage($this->translator->translate("common.alerts.configSaved"));
            $this->redirect("Settings:app");
        };
        return $form;
    }

    public function createComponentTeamConfigForm()
    {
        $teamNeon = $this->supplier->getTeamNeon();
        $eventTypes = $this->eventTypeManager->getList();
        $this->statusList->init()->getData();
        $statusList = $this->statusManager->getByStatusCode();
        $team = $this->teamManager->getTeam();

        $form = new Form();
        $form->addText("name", $this->translator->translate("team.name"))->setValue($team->getName());
        $form->addText("sport", $this->translator->translate("team.sport"))->setValue($team->getSport());
        $form->addSelect("defaultLanguage", $this->translator->translate("team.defaultLanguage"), ["CZ" => "Česky", "EN" => "English", "FR" => "Le français", "PL" => "Polski"])->setValue($team->getDefaultLanguageCode() ?: "CZ");
        $form->addSelect("skin", $this->translator->translate("team.defaultSkin"), $this->supplier->getAllSkins())->setValue($teamNeon->skin);
        $form->addMultiSelect("requiredFields", $this->translator->translate("team.requiredFields"), UserResource::getAllFields($this->translator)["ALL"])->setValue($teamNeon->userRequiredFields);

        foreach ($eventTypes as $etype) {
            /* @var $etype EventType */
            $color = isset($teamNeon->event_colors[$etype->getCode()]) ? $teamNeon->event_colors[$etype->getCode()] : "#bababa";

            $form->addText("eventColor_" . $etype->getCode(), $etype->getCaption())->setAttribute("data-toggle", "colorpicker")->setAttribute("data-color", $color)->setValue($color);
        }

        foreach ($statusList as $status) {
            /* @var $status Status */
            $color = $this->supplier->getStatusColor($status->getCode());
            $form->addText("statusColor_" . $status->getCode(), $status->getCaption())->setAttribute("data-toggle", "colorpicker")->setAttribute("data-color", $color)->setValue($color);
        }

        $form->addSubmit("save");

        $form->onSuccess[] = function (Form $form, stdClass $values) {
            $teamNeon = $this->supplier->getTeamNeon();
            $teamNeon->skin = $values->skin;
            $teamNeon->userRequiredFields = $values->requiredFields;
            $eventColors = [];
            $statusColors = [];
            foreach ((array) $values as $name => $value) {
                $valData = explode("_", $name);
                if ($valData[0] == "eventColor") {
                    $eventColors[$valData[1]] = $value;
                }
                if ($valData[0] == "statusColor") {
                    $statusColors[$valData[1]] = $value;
                }
            }
            $teamNeon->event_colors = $eventColors;
            $teamNeon->status_colors = $statusColors;

            $this->supplier->saveTeamNeon((array) $teamNeon);
            $this->statusList->cleanCache();

            //check if there is some TAPI change to be commited
            $teamData = $this->teamManager->getTeam();
            if ($teamData->getName() != $values->name || $teamData->getSport() != $values->sport || $teamData->getDefaultLanguageCode() != $values->defaultLanguage) {
                $this->teamManager->update([
                    "name" => $values->name,
                    "sport" => $values->sport,
                    "defaultLanguageCode" => $values->defaultLanguage,
                        ], $teamData->getId());
            }

            $this->flashMessage($this->translator->translate("common.alerts.configSaved"));
            $this->redirect("Settings:team");
        };
        return $form;
    }

}