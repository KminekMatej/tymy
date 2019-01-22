<?php

namespace Tapi;
use \Nette\Utils\Strings;
use Nette\Caching\Cache;

/**
 * Project: tymy_v2
 * Description of EventResource
 *
 * @author kminekmatej created on 22.12.2017, 21:04:05
 */
abstract class EventResource extends TapiObject {
    
    protected function postProccessEventHistory($history){
        $this->timeLoad($history->updatedAt);
    }
    
    protected function postProcessEvent($event){
        if($event == null) TapiService::throwNotFound();
        $event->webName = Strings::webalize($event->id . "-" . $event->caption);
        $event->warning = false;

        $this->timeLoad($event->closeTime);
        $this->timeLoad($event->startTime);
        $this->timeLoad($event->endTime);
        $event->resultsClosed = false;
        $event->isPast = $event->endTime < new \Nette\Utils\DateTime();
        $attendanceStillOpen = $event->closeTime > new \Nette\Utils\DateTime();
        
        if (!property_exists($event, "place"))
            $event->place = ""; //set default value
        if (!property_exists($event, "link"))
            $event->link = ""; //set default value
        if (!property_exists($event, "viewRightName"))
            $event->viewRightName = ""; //set default value
        if (!property_exists($event, "planRightName"))
            $event->planRightName = ""; //set default value
        if (!property_exists($event, "resultRightName"))
            $event->resultRightName = ""; //set default value
        
        $this->addMyAttendance($event);
        if (property_exists($event, "myAttendance") && property_exists($event->myAttendance, "preStatus")) {
            $eventClassMap = [
                "YES" => "success",
                "LAT" => "warning",
                "NO" => "danger",
                "DKY" => "danger",
                "UNKNOWN" => "secondary",
            ];
            $event->preClass = array_key_exists($event->myAttendance->preStatus, $eventClassMap) ? $eventClassMap[$event->myAttendance->preStatus] : "primary";
            
            if ($event->myAttendance->preStatus == "UNKNOWN" && $attendanceStillOpen) {
                $event->warning = true;
            }
        }
    }
    
    protected function getEventColors($event) {
        $colorList = $this->supplier->getEventColors();
        $eventColor = [];
        $invertColors = !property_exists($event, 'myAttendance') || !property_exists($event->myAttendance, 'preStatus');
        if(!array_key_exists($event->type, $colorList)) return ["borderColor" => 'blue',"backgroundColor" => 'blue',"textColor" => 'white'];
        $eventColor["borderColor"] = $colorList[$event->type];
        $eventColor["backgroundColor"] = $invertColors ? 'white' : $colorList[$event->type];
        $eventColor["textColor"] = $invertColors ? $colorList[$event->type] : '';
        return $eventColor;
    }
    
    private function addMyAttendance(&$event) {
        if (property_exists($event, "myAttendance")){
            if(!property_exists($event->myAttendance, "postStatus")){
                $event->myAttendance->postStatus = "UNKNOWN";
                $event->myAttendance->postDescription = "";
            }
            if(!property_exists($event->myAttendance, "preStatus")){
                $event->myAttendance->preStatus = "UNKNOWN";
                $event->myAttendance->preDescription = "";
            }
            return;
        }
        
        $myAttendance = new \stdClass();
        $myAttendance->preStatus = "UNKNOWN";
        $myAttendance->postStatus = "UNKNOWN";
        $myAttendance->preDescription = "";
        $myAttendance->postDescription = "";

        if (property_exists($event, "attendance")) {
            foreach ($event->attendance as $att) {
                if (!property_exists($att, "preStatus"))
                    $att->preStatus = "UNKNOWN"; //set default value
                if (!property_exists($att, "preDescription"))
                    $att->preDescription = ""; //set default value
                if (!property_exists($att, "postStatus")) {
                    $att->postStatus = "UNKNOWN"; //set default value
                } else {
                    $event->resultsClosed = true;
                }
                if (!property_exists($att, "postDescription"))
                    $att->postDescription = ""; //set default value
                if (property_exists($att, "preDatMod"))
                    $this->timeLoad($att->preDatMod);
                if (property_exists($att, "postDatMod"))
                    $this->timeLoad($att->postDatMod);
                if ($att->userId == $this->user->getId()) {
                    $myAttendance = $att;
                }
            }
        }
        $event->myAttendance = $myAttendance;
    }

    protected function clearCache($id = NULL){
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:events"]);
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:events/withMyAttendance"]);
        if($id != NULL){
            $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:event/$id"]);
        }
    }
}
