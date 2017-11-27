<?php

namespace App\Presenters;

class SettingsPresenter extends SecuredPresenter {
    
    /** @var \Tymy\Discussion @inject */
    public $discussion;
        
    /** @var \Tymy\Event @inject */
    public $event;
        
    /** @var \Tymy\Events @inject */
    public $events;
        
    /** @var \Tymy\EventTypes @inject */
    public $eventTypes;
    
            
    /** @var \App\Model\Supplier @inject */
    public $supplier;
        
    protected function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => "Nastavení", "link" => $this->link("Settings:")]]);
        $this->template->addFilter("typeColor", function ($type) {
            $color = $this->supplier->getEventColors();
            return $color[$type];
        });
    }

    public function actionDefault() {
        //TODO
    }

    public function actionDiscussions($discussion = NULL) {
        $this->setLevelCaptions(["2" => ["caption" => "Diskuze", "link" => $this->link("Settings:discussions")]]);
        if(!is_null($discussion)){
            $this->setView("discussion");
        } else {
            $this->template->isNew = false;
            $discussions = $this->discussions->reset()->getData(); // get all events
            $this->template->discussions = $discussions;
            $this->template->discussionsCount = count($discussions);
        }
    }

    public function actionEvents($event = NULL, $page = 1) {
        $this->setLevelCaptions(["2" => ["caption" => "Události", "link" => $this->link("Settings:events")]]);
        if(!is_null($event)){
            $this->setView("event");
        } else {
            $this->template->isNew = false;
            $page = is_numeric($page) ? $page : 1;
            $limit = \Tymy\Events::PAGING_EVENTS_PER_PAGE;
            $offset = ($page-1)*$limit;
            $events = $this->events->reset()->setLimit($limit)->setOffset($offset)->getData(); // get all events
            $this->template->events = $events;
            $allEventsCount = $this->events->getAllEventsCount();
            $this->template->eventsCount = $allEventsCount;
            $this->template->currentPage = $page;
            $this->template->lastPage = ceil($allEventsCount / $limit);
            $this->template->pagination = $this->pagination($allEventsCount, $limit, $page, 5);
        }
    }

    public function actionPolls($poll = NULL) {
        $this->setLevelCaptions(["2" => ["caption" => "Ankety", "link" => $this->link("Settings:polls")]]);
        if(!is_null($poll)){
            $this->setView("poll");
        } else {
            //TODO render list
        }
    }

    public function actionReports() {
        //TODO
        $this->setLevelCaptions(["2" => ["caption" => "Reporty", "link" => $this->link("Settings:reports")]]);
    }

    public function actionPermissions() {
        //TODO
        $this->setLevelCaptions(["2" => ["caption" => "Oprávnění", "link" => $this->link("Settings:permissions")]]);
    }

    public function actionApp() {
        $this->setLevelCaptions(["2" => ["caption" => "Aplikace", "link" => $this->link("Settings:app")]]);
        $this->template->version = $this->supplier->getVersion(0);
        $this->template->previousVersion = $this->supplier->getVersion(1);
    }
    
    public function renderDiscussion_new() {
        $this->setLevelCaptions([
            "2" => ["caption" => "Diskuze", "link" => $this->link("Settings:discussions")],
            "3" => ["caption" => "Nová"]
            ]);
        $this->template->isNew = true;
            
        $discussions = [(object)[
            "id" => 0,
            "caption" => "",
            "description" => "",
            "publicRead" => FALSE,
            "editablePosts" => TRUE,
            "order" => 0,
        ]];
        $this->template->discussions = $discussions;
        
        $this->setView("discussions");
    }
    
    public function renderDiscussion($discussion) {
        //RENDERING DISCUSSION DETAIL
        $discussionId = $this->discussions->getIdFromWebname($discussion);
        $discussionObj = $this->discussion->reset()->recId($discussionId)->getData()->discussion;
        $this->setLevelCaptions(["3" => ["caption" => $discussionObj->caption, "link" => $this->link("Settings:discussions", $discussionObj->webName)]]);
        $this->template->discussion = $discussionObj;
    }
    
    public function renderEvent_new() {
        $this->setLevelCaptions([
            "2" => ["caption" => "Události", "link" => $this->link("Settings:events")],
            "3" => ["caption" => "Nová"]
            ]);
        $this->template->isNew = true;
        
        $events = [(object)[
            "id" => 0,
            "caption" => "",
            "description" => "",
            "startTime" => date("c"),
            "endTime" => date("c"),
            "closeTime" => date("c"),
            "place" => "",
            "link" => "",
        ]];
        $this->template->events = $events;
        $this->template->eventTypes = $this->eventTypes->getData();
        
        $this->setView("events");
    }
    
    public function renderEvent($event) {
        //RENDERING EVENT DETAIL
        $eventId = $this->parseIdFromWebname($event);
        $eventObj = $this->event->reset()->recId($eventId)->getData();
        $this->setLevelCaptions(["3" => ["caption" => $eventObj->caption, "link" => $this->link("Settings:events", $eventObj->webName)]]);
        $this->template->event = $eventObj;
        $this->template->eventTypes = $this->eventTypes->getData();
    }
    
    public function renderPoll($poll) {
        //RENDERING POLL DETAIL
        $pollId = $this->parseIdFromWebname($poll);
        $pollObj = $this->event->reset()->recId($pollId)->getData();
        $this->setLevelCaptions(["3" => ["caption" => $pollObj->caption, "link" => $this->link("Settings:polls", $pollObj->webName)]]);
        $this->template->poll = $pollObj;
    }
    
    public function renderDefault(){
        $this->template->accessibleSettings = $this->getAccessibleSettings();
    }
    
    public function handleEventsEdit(){
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        foreach ($binders as $bind) {
            $this->editEvent($bind);
        }
    }
    
    public function handleEventsCreate(){
        $post = $this->getRequest()->getPost();
        try {
            $this->event->create($post, $this->eventTypes->getData());
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
        $this->redirect('Settings:events');
    }
    
    public function handleEventEdit(){
        $bind = $this->getRequest()->getPost();
        $this->editEvent($bind);
    }
    
    public function handleEventDelete(){
        $bind = $this->getRequest()->getPost();
        try {
            $this->event
                    ->recId($bind["id"])
                    ->delete();
            $this->redirect("Settings:events");
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
    }
    
    public function handleDiscussionsEdit(){
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        foreach ($binders as $bind) {
            $this->editDiscussion($bind);
        }
    }
    
    public function handleDiscussionCreate(){
        $discussionData = $this->getRequest()->getPost()[1]; // new discussion is always as ID 1
        try {
            $this->discussions->create($discussionData);
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex, "Settings:discussions");
        }
        $this->redirect('Settings:discussions');
    }
    
    public function handleDiscussionEdit(){
        $bind = $this->getRequest()->getPost();
        $this->editDiscussion($bind);
    }
    
    public function handleDiscussionDelete() {
        $bind = $this->getRequest()->getPost();
        try {
            $this->discussions
                    ->recId($bind["id"])
                    ->delete();
            $this->redirect("Settings:discussions");
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
    }

    private function editEvent($bind) {
        if(array_key_exists("startTime", $bind["changes"])) $bind["changes"]["startTime"] = gmdate("Y-m-d\TH:i:s\Z", strtotime($bind["changes"]["startTime"]));
        if(array_key_exists("endTime", $bind["changes"])) $bind["changes"]["endTime"] = gmdate("Y-m-d\TH:i:s\Z", strtotime($bind["changes"]["endTime"]));
        if(array_key_exists("closeTime", $bind["changes"])) $bind["changes"]["closeTime"] = gmdate("Y-m-d\TH:i:s\Z", strtotime($bind["changes"]["closeTime"]));
        try {
            $this->event
                    ->recId($bind["id"])
                    ->edit($bind["changes"]);
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
    }
    
    private function editDiscussion($bind) {
        try {
            $this->discussions
                    ->recId($bind["id"])
                    ->edit($bind["changes"]);
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
    }
    
}
