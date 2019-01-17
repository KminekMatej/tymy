<?php

namespace App\Presenters;

use Tapi\DiscussionCreateResource;
use Tapi\DiscussionDeleteResource;
use Tapi\DiscussionDetailResource;
use Tapi\DiscussionEditResource;
use Tapi\EventCreateResource;
use Tapi\EventDeleteResource;
use Tapi\EventDetailResource;
use Tapi\EventEditResource;
use Tapi\EventListResource;
use Tapi\Exception\APIException;
use Tapi\NoteCreateResource;
use Tapi\NoteDeleteResource;
use Tapi\NoteEditResource;
use Tapi\OptionCreateResource;
use Tapi\OptionDeleteResource;
use Tapi\OptionEditResource;
use Tapi\OptionListResource;
use Tapi\PermissionListResource;
use Tapi\PollCreateResource;
use Tapi\PollDeleteResource;
use Tapi\PollDetailResource;
use Tapi\PollEditResource;


class SettingsPresenter extends SecuredPresenter {
    
    /** @var DiscussionDetailResource @inject */
    public $discussionDetail;
        
    /** @var DiscussionCreateResource @inject */
    public $discussionCreator;
        
    /** @var DiscussionEditResource @inject */
    public $discussionEditor;
        
    /** @var DiscussionDeleteResource @inject */
    public $discussionDeleter;
        
    /** @var EventDetailResource @inject */
    public $eventDetail;
        
    /** @var EventCreateResource @inject */
    public $eventCreator;
        
    /** @var EventEditResource @inject */
    public $eventEditor;
        
    /** @var EventDeleteResource @inject */
    public $eventDeleter;
        
    /** @var PollDetailResource @inject */
    public $pollDetail;
        
    /** @var PollCreateResource @inject */
    public $pollCreator;
    
    /** @var PollEditResource @inject */
    public $pollEditor;
    
    /** @var NoteCreateResource @inject */
    public $noteCreator;
    
    /** @var NoteEditResource @inject */
    public $noteEditor;
    
    /** @var NoteDeleteResource @inject */
    public $noteDeleter;
    
    /** @var PollDeleteResource @inject */
    public $pollDeleter;
        
    /** @var OptionListResource @inject */
    public $pollOptionList;
            
    /** @var OptionCreateResource @inject */
    public $pollOptionCreator;
            
    /** @var OptionEditResource @inject */
    public $pollOptionEditor;
            
    /** @var OptionDeleteResource @inject */
    public $pollOptionDeleter;
    
    /** @var PermissionListResource @inject */
    public $permissionLister;
    
            
    protected function startup() {
        parent::startup();
        parent::showNotes();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("settings.setting", 2), "link" => $this->link("Settings:")]]);
        $this->template->addFilter("typeColor", function ($type) {
            $color = $this->supplier->getEventColors();
            return $color[$type];
        });
    }

    public function actionDiscussions($discussion = NULL) {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("discussion.discussion", 2), "link" => $this->link("Settings:discussions")]]);
        if(!is_null($discussion)){
            $this->setView("discussion");
        } else {
            $this->template->isNew = false;
            $discussions = $this->discussionList->init()->getData();
            $this->template->discussions = $discussions;
            $this->template->discussionsCount = count($discussions);
        }
    }

    public function actionEvents($event = NULL, $page = 1) {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("event.event", 2), "link" => $this->link("Settings:events")]]);
        if(!is_null($event)){
            $this->setView("event");
        } else {
            $this->template->isNew = false;
            $page = is_numeric($page) ? $page : 1;
            $limit = EventListResource::PAGING_EVENTS_PER_PAGE;
            $offset = ($page-1)*$limit;
            $events = $this->eventList->init()->setLimit($limit)->setOffset($offset)->getData(); // get all events
            $this->template->events = $events;
            $allEventsCount = $this->eventList->getAllEventsCount();
            $this->template->eventsCount = $allEventsCount;
            $this->template->currentPage = $page;
            $this->template->lastPage = ceil($allEventsCount / $limit);
            $this->template->pagination = $this->pagination($allEventsCount, $limit, $page, 5);
        }
    }

    public function actionPolls($poll = NULL) {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("poll.poll", 2), "link" => $this->link("Settings:polls")]]);
        if(!is_null($poll)){
            $this->setView("poll");
        } else {
            $this->template->isNew = false;
            $polls = $this->polls->setMenu(FALSE)->getData();
            $this->template->polls = $polls;
        }
    }

    public function actionNotes($note = NULL) {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("note.note", 2), "link" => $this->link("Settings:notes")]]);
        if(!is_null($note)){
            $this->setView("note");
        } else {
            $this->template->isNew = false;
            $notes = $this->noteList->setMenu(FALSE)->getData();
            $this->template->notes = $notes;
        }
    }
    
    public function actionReports() {
        //TODO
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("report.report", 2), "link" => $this->link("Settings:reports")]]);
    }

    public function actionPermissions($permission = NULL) {
        if(!$this->getUser()->isAllowed('permissions','canSetup')) $this->notAllowed();
        if(!is_null($permission)){
            $this->setView("permission");
        } else {
            $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("permission.permission", 2), "link" => $this->link("Settings:permissions")]]);
            $this->permissionLister->init()->getData();
            $this->template->userPermissions = $this->permissionLister->getUsrPermissions();
            $this->template->systemPermissions = $this->permissionLister->getSysPermissions();
        }
        
        
    }

    public function actionApp() {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("settings.application"), "link" => $this->link("Settings:app")]]);
        $currentVersion = $this->supplier->getVersion(0);
        $this->template->version = $currentVersion;
        $previousPatch = NULL;
        $firstMinor = NULL;
        foreach ($this->supplier->getVersions() as $version) {
            if(empty($previousPatch) && ($currentVersion->major != $version->major || $currentVersion->minor != $version->minor || $currentVersion->patch != $version->patch)){
                $previousPatch = $version;
            }
            if($currentVersion->major == $version->major && $currentVersion->minor == $version->minor && $version->patch == 0){
                $firstMinor = $version;
            }
        }
        if($previousPatch === NULL) $previousPatch = $this->supplier->getVersion(count($this->supplier->getVersions()));
        $this->template->previousPatchVersion = $previousPatch;
        $this->template->firstMinorVersion = $firstMinor;
    }
    
    public function renderDiscussion_new() {
        if(!$this->getUser()->isAllowed('discussion','setup')) $this->notAllowed();
        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("discussion.discussion", 2), "link" => $this->link("Settings:discussions")],
            "3" => ["caption" => $this->translator->translate("discussion.new")]
            ]);
        $this->template->isNew = true;
        $this->template->discussion = (object)[
            "id" => -1,
            "caption" => "",
            "description" => "",
            "publicRead" => FALSE,
            "editablePosts" => TRUE,
            "order" => 0,
        ];
        
        $this->setView("discussion");
    }
    
    public function renderPermission($permission){
        if(!$this->getUser()->isAllowed('permissions','canSetup')) $this->notAllowed();
        $perm = $this->permissionLister->getPermissionByWebname($permission);
        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("permission.permission", 2), "link" => $this->link("Settings:permissions")],
            "3" => ["caption" => $perm->name, "link" => $this->link("Settings:permissions", $perm->webName)]
            ]);
        $this->template->allowances = ["allowed" => "Povoleno","revoked" => "Zakázáno"];
        $this->template->statuses = ["PLAYER" => "Hráč","SICK" => "Marod","MEMBER" => "Člen"];
        $this->template->roles = $this->getAllRoles();
        $this->template->users = $this->userList->getData();
        $this->template->perm = $perm;
        $this->template->isNew = false;
    }
    
    public function renderDiscussion($discussion) {
        if(!$this->getUser()->isAllowed('discussion','setup')) $this->notAllowed();
        //RENDERING DISCUSSION DETAIL
        $discussionId = $this->discussionList->init()->getIdFromWebname($discussion, $this->discussionList->getData());
        $discussionObj = $this->discussionDetail->init()->setId($discussionId)->getData();
        if($discussionObj == NULL){
            $this->flashMessage($this->translator->translate("discussion.errors.discussionNotExists", NULL, ['id' => $discussionId]), "danger");
            $this->redirect('Settings:events');
        }
        $this->setLevelCaptions(["3" => ["caption" => $discussionObj->caption, "link" => $this->link("Settings:discussions", $discussionObj->webName)]]);
        $this->template->isNew = FALSE;
        $this->template->discussion = $discussionObj;
    }
    
    public function renderEvent_new() {
        if(!$this->getUser()->isAllowed('event','canCreate')) $this->notAllowed();
        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("event.event", 2), "link" => $this->link("Settings:events")],
            "3" => ["caption" => $this->translator->translate("event.new", 2)]
            ]);
        $this->template->isNew = true;
        
        $events = [(object)[
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
        $this->template->eventTypes = $this->eventTypeList->init()->getData();
        
        $this->setView("events");
    }
    
    public function renderEvent($event) {
        if(!$this->getUser()->isAllowed('event','canUpdate')) $this->notAllowed();
        //RENDERING EVENT DETAIL
        $eventId = $this->parseIdFromWebname($event);
        $eventObj = $this->eventDetail->init()->setId($eventId)->getData();
        if($eventObj == NULL){
            $this->flashMessage($this->translator->translate("event.errors.eventNotExists", NULL, ['id' => $eventId]), "danger");
            $this->redirect('Settings:events');
        }

        $this->setLevelCaptions(["3" => ["caption" => $eventObj->caption, "link" => $this->link("Settings:events", $eventObj->webName)]]);
        $this->template->event = $eventObj;
        $this->template->eventTypes = $this->eventTypeList->init()->getData();
    }
    
    public function renderNote_new() {
        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("note.note", 2), "link" => $this->link("Settings:notes")],
            "3" => ["caption" => $this->translator->translate("note.new")]
            ]);
        $this->template->isNew = true;
        
        $note = (object)[
            "id" => -1,
            "caption" => "",
            "description" => "",
            "specialPage" => "",
            "source" => "",
            "accessType" => "PRIVATE",
            "menuType" => "APP",
            "menuOrder" => 0,
            "canRead" => true,
            "canWrite" => true,
        ];
        $this->template->note = $note;
        
        $this->setView("note");
    }
    
    public function renderNote($note) {
        //RENDERING NOTE DETAIL
        $noteId = $this->parseIdFromWebname($note);
        $this->noteList->init()->getData();
        $noteObj = $this->noteList->getById($noteId);
        if($noteObj == NULL){
            $this->flashMessage($this->translator->translate("note.errors.eventNotExists", NULL, ['id' => $noteId]), "danger");
            $this->redirect('Settings:notes');
        }
        $this->setLevelCaptions(["3" => ["caption" => $noteObj->caption, "link" => $this->link("Settings:note", $noteObj->webName)]]);
        $this->template->note = $noteObj;
        $this->template->isNew = false;
    }
    
    public function renderPoll_new() {
        if(!$this->getUser()->isAllowed('poll','canCreatePoll')) $this->notAllowed();
        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("poll.poll", 2), "link" => $this->link("Settings:polls")],
            "3" => ["caption" => $this->translator->translate("poll.new")]
            ]);
        $this->template->isNew = true;
        
        $polls = [(object)[
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
    
    public function renderPoll($poll) {
        if(!$this->getUser()->isAllowed('poll','canUpdatePoll')) $this->notAllowed();
        //RENDERING POLL DETAIL
        $pollId = $this->parseIdFromWebname($poll);
        $pollObj = $this->pollDetail->init()->setId($pollId)->getData();
        if($pollObj == NULL){
            $this->flashMessage($this->translator->translate("poll.errors.pollNotExists", NULL, ['id' => $pollId]), "danger");
            $this->redirect('Settings:polls');
        }
        if(count($pollObj->options) == 0){
            $pollObj->options[] = (object)["id" => -1, "pollId" => $pollId, "caption" => "", "type" => "TEXT"];
        }
        $this->setLevelCaptions(["3" => ["caption" => $pollObj->caption, "link" => $this->link("Settings:polls", $pollObj->webName)]]);
        $this->template->poll = $pollObj;
    }
    
    public function renderDefault(){
        $this->template->accessibleSettings = $this->getAccessibleSettings();
    }
    
    public function handleEventsEdit(){
        if(!$this->getUser()->isAllowed('event','canUpdate')) $this->notAllowed();
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        foreach ($binders as $bind) {
            $this->editEvent($bind);
        }
    }
    
    public function handleEventsCreate(){
        if(!$this->getUser()->isAllowed('event','canCreate')) $this->notAllowed();
        $binders = $this->getRequest()->getPost()["binders"];
        $events = [];
        foreach ($binders as $bind) {
            $events[] = $bind["changes"];
        }
        try {
            $this->eventCreator->init()->setEventsArray($events)->setEventTypesArray($this->eventTypeList->getData())->perform();
            $this->redirect('Settings:events');
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }
    
    public function handleEventEdit(){
        if(!$this->getUser()->isAllowed('event','canUpdate')) $this->notAllowed();
        $bind = $this->getRequest()->getPost();
        $this->editEvent($bind);
    }
    
    public function handleEventDelete(){
        if(!$this->getUser()->isAllowed('event','canDelete')) $this->notAllowed();
        $bind = $this->getRequest()->getPost();
        try {
            $this->eventDeleter->init()
                    ->setId($bind["id"])
                    ->perform();
            $this->redirect("Settings:events");
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }
    
    public function handleDiscussionsEdit(){
        if(!$this->getUser()->isAllowed('discussion','setup')) $this->notAllowed();
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        foreach ($binders as $bind) {
            $this->editDiscussion($bind);
        }
    }
    
    public function handleDiscussionCreate(){
        if(!$this->getUser()->isAllowed('discussion','setup')) $this->notAllowed();
        $discussionData = (object)$this->getRequest()->getPost()["changes"]; // new discussion is always as ID 1
        try {
            $this->discussionCreator->init()
                    ->setDiscussion($discussionData)
                    ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
        $this->redirect('Settings:discussions');
    }
    
    public function handleDiscussionEdit(){
        if(!$this->getUser()->isAllowed('discussion','setup')) $this->notAllowed();
        $bind = $this->getRequest()->getPost();
        $this->editDiscussion($bind);
    }
    
    public function handleDiscussionDelete() {
        if(!$this->getUser()->isAllowed('discussion','setup')) $this->notAllowed();
        $bind = $this->getRequest()->getPost();
        try {
            $this->discussionDeleter->init()
                    ->setId($bind["id"])
                    ->perform();
            $this->redirect("Settings:discussions");
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }

    public function handlePollsEdit(){
        if(!$this->getUser()->isAllowed('poll','canUpdate')) $this->notAllowed();
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        foreach ($binders as $bind) {
            $this->editPoll($bind);
        }
    }
    
    public function handlePollCreate(){
        if(!$this->getUser()->isAllowed('poll','canCreatePoll')) $this->notAllowed();
        $pollData = $this->getRequest()->getPost()["changes"];
        try {
            $this->pollCreator->init()->setPoll($pollData)->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
        $this->redirect('Settings:polls');
    }
    
    public function handlePollEdit(){
        if(!$this->getUser()->isAllowed('poll','canUpdatePoll')) $this->notAllowed();
        $bind = $this->getRequest()->getPost();
        $this->editPoll($bind);
    }
    
    public function handlePollDelete() {
        if(!$this->getUser()->isAllowed('poll','canDeletePoll')) $this->notAllowed();
        $bind = $this->getRequest()->getPost();
        try {
            $this->pollDeleter->init()->setId($bind["id"])->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }
    
    public function handleNoteCreate() {
        $bind = $this->getRequest()->getPost();
        try {
            $this->noteCreator->init()
                    ->setNote($bind["changes"])
                    ->perform();
            $this->flashMessage($this->translator->translate("poll.alerts.succesfullyCreated"), "success");
            $this->redirect("Settings:notes");
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }
    
    public function handleNoteEdit(){
        $bind = $this->getRequest()->getPost();
        $this->editNote($bind);
    }
    
    public function handleNoteDelete() {
        $bind = $this->getRequest()->getPost();
        try {
            $this->noteDeleter->init()->setId($bind["id"])->perform();
            $this->redirect("Settings:notes");
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }

    public function handlePollOptionsEdit($poll){
        if(!$this->getUser()->isAllowed('poll','canUpdatePoll')) $this->notAllowed();
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        $pollId = $this->parseIdFromWebname($poll);
        foreach ($binders as $bind) {
            $bind["pollId"] = $pollId;
            $this->editPollOption($bind);
        }
    }
    
    public function handlePollOptionCreate($poll){
        if(!$this->getUser()->isAllowed('poll','canUpdatePoll')) $this->notAllowed();
        $pollData = $this->getRequest()->getPost()[1]; // new poll option is always as item 1
        $pollId = $this->parseIdFromWebname($poll);
        try {
            $this->pollOptionCreator->init()
                    ->setId($pollId)
                    ->setPollOptions($pollData)
                    ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }
    
    public function handlePollOptionEdit($poll) {
        if(!$this->getUser()->isAllowed('poll','canUpdatePoll')) $this->notAllowed();
        $bind = $this->getRequest()->getPost();
        $bind["pollId"] = $this->parseIdFromWebname($poll);
        try {
            $this->editPollOption($bind);
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }

    public function handlePollOptionDelete($poll) {
        if(!$this->getUser()->isAllowed('poll','canDeletePoll')) $this->notAllowed();
        $bind = $this->getRequest()->getPost();
        $bind["pollId"] = $this->parseIdFromWebname($poll);
        try {
            $this->pollOptionDeleter->init()
                    ->set($bind["id"])
                    ->recId($bind["pollId"])
                    ->delete();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }
    
    public function handlePermissionEdit(){
        /*if(!$this->getUser()->isAllowed('event','canUpdate')) $this->notAllowed();
        $bind = $this->getRequest()->getPost();
        $this->editEvent($bind);*/
    }
    
    public function handlePermissionDelete(){
        /*if(!$this->getUser()->isAllowed('event','canDelete')) $this->notAllowed();
        $bind = $this->getRequest()->getPost();
        try {
            $this->eventDeleter->init()
                    ->setId($bind["id"])
                    ->perform();
            $this->redirect("Settings:events");
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }*/
    }
    
    public function handleCacheDrop() {
        $this->discussionDetail->cleanCache(); //can use any tapi object
        $this->flashMessage($this->translator->translate("settings.cacheDropped"), "success");
        $this->redirect('this');
    }
    
    private function editEvent($bind) {
        if(array_key_exists("startTime", $bind["changes"])) $bind["changes"]["startTime"] = gmdate("Y-m-d\TH:i:s\Z", strtotime($bind["changes"]["startTime"]));
        if(array_key_exists("endTime", $bind["changes"])) $bind["changes"]["endTime"] = gmdate("Y-m-d\TH:i:s\Z", strtotime($bind["changes"]["endTime"]));
        if(array_key_exists("closeTime", $bind["changes"])) $bind["changes"]["closeTime"] = gmdate("Y-m-d\TH:i:s\Z", strtotime($bind["changes"]["closeTime"]));
        try {
            $this->eventEditor->init()
                    ->setId($bind["id"])
                    ->setEvent($bind["changes"])
                    ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }
    
    private function editNote($bind) {
        try {
            $this->noteEditor->init()
                    ->setId($bind["id"])
                    ->setNote($bind["changes"])
                    ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }
    
    private function editDiscussion($bind) {
        try {
            $this->discussionEditor->init()
                    ->setId($bind["id"])
                    ->setDiscussion($bind["changes"])
                    ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }
    
    private function editPoll($bind) {
        try {
            $this->pollEditor->init()
                    ->setId($bind["id"])
                    ->setPoll($bind["changes"])
                    ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
    }
    
    private function editPollOption($bind) {
        if ($bind["id"] == -1) {
            $this->pollOption->init()
                    ->recId($bind["pollId"])
                    ->create([$bind["changes"]]);
        } else {
            $this->pollOption->init()
                    ->recId($bind["pollId"])
                    ->setOptionId($bind["id"])
                    ->edit($bind["changes"]);
        }
    }

    private function notAllowed(){
        $this->flashMessage($this->translator->translate("common.alerts.notPermitted"));
        $this->redirect("Settings:");
    }
}
