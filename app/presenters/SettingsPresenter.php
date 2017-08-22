<?php

namespace App\Presenters;

class SettingsPresenter extends SecuredPresenter {
        
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
            //TODO render list
        }
    }

    public function actionEvents($event = NULL) {
        $this->setLevelCaptions(["2" => ["caption" => "Události", "link" => $this->link("Settings:events")]]);
        if(!is_null($event)){
            $this->setView("event");
        } else {
            $this->template->events = $this->events->reset()->setLimit(15)->getData(); // get all events
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
        //TODO
        $this->setLevelCaptions(["2" => ["caption" => "Aplikace", "link" => $this->link("Settings:app")]]);
    }
    
    
    public function renderDiscussion($discussion) {
        //RENDERING DISCUSSION DETAIL
        $discussionId = $this->parseIdFromWebname($discussion);
        $discussionObj = $this->event->reset()->recId($discussionId)->getData();
        $this->setLevelCaptions(["3" => ["caption" => $discussionObj->caption, "link" => $this->link("Settings:discussions", $discussionObj->webName)]]);
        $this->template->discussion = $discussionObj;
    }
    
    public function renderEvent($event) {
        //RENDERING EVENT DETAIL
        $eventId = $this->parseIdFromWebname($event);
        $eventObj = $this->event->reset()->recId($eventId)->getData();
        $this->setLevelCaptions(["3" => ["caption" => $eventObj->caption, "link" => $this->link("Settings:events", $eventObj->webName)]]);
        $this->template->event = $eventObj;
        $eventProps = [];
        $eventProps[] = (object)["name" => "caption", "label" => "Titulek", "type" => "text", "value" => $eventObj->caption];
        $eventProps[] = (object)["name" => "type", "label" => "Typ", "type" => "select", "values"=> $this->eventTypes->getData(), "value" => $eventObj->type, "disabled"=>true];
        $eventProps[] = (object)["name" => "description", "label" => "Popis", "type" => "textarea", "value" => $eventObj->description];
        $eventProps[] = (object)["name" => "startTime", "label" => "Začátek", "type" => "datetime-local", "value" => strftime('%Y-%m-%dT%H:%M:%S', strtotime($eventObj->startTime)) ];
        $eventProps[] = (object)["name" => "endTime", "label" => "Konec", "type" => "datetime-local", "value" => strftime('%Y-%m-%dT%H:%M:%S', strtotime($eventObj->endTime))];
        $eventProps[] = (object)["name" => "closeTime", "label" => "Uzávěrka", "type" => "datetime-local", "value" => strftime('%Y-%m-%dT%H:%M:%S', strtotime($eventObj->closeTime))];
        $eventProps[] = (object)["name" => "place", "label" => "Místo", "type" => "text", "value" => $eventObj->place];
        $eventProps[] = (object)["name" => "link", "label" => "Odkaz", "type" => "text", "value" => $eventObj->link];
        
        $this->template->props = $eventProps;
    }
    
    public function renderPoll($poll) {
        //RENDERING POLL DETAIL
        $pollId = $this->parseIdFromWebname($poll);
        $pollObj = $this->event->reset()->recId($pollId)->getData();
        $this->setLevelCaptions(["3" => ["caption" => $pollObj->caption, "link" => $this->link("Settings:polls", $pollObj->webName)]]);
        $this->template->poll = $pollObj;
    }
    
    public function handleEventsEdit(){
        $post = $this->getRequest()->getPost();
        foreach ($post as $evData) {
            $this->editEvent($evData["id"], $evData);
        }
    }
    
    public function handleEventEdit($eventId){
        $post = $this->getRequest()->getPost();
        $this->editEvent($eventId, $post);
    }
    
    public function handleEventDelete($eventId){
        try {
            $this->event
                    ->recId($eventId)
                    ->delete();
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
    }
    
    private function editEvent($eventId, $data) {
        try {
            $this->event
                    ->recId($eventId)
                    ->edit($data);
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
    }
    
}
