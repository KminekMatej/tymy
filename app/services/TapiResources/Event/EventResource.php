<?php

namespace Tapi;
use \Nette\Utils\Strings;

/**
 * Project: tymy_v2
 * Description of EventResource
 *
 * @author kminekmatej created on 22.12.2017, 21:04:05
 */
abstract class EventResource extends TapiObject {
    
    protected function postProcessEvent($event){
        if($event == null) TapiService::throwNotFound();
        $event->webName = Strings::webalize($event->id . "-" . $event->caption);
        $event->warning = false;

        $this->timeLoad($event->closeTime);
        $this->timeLoad($event->startTime);
        $this->timeLoad($event->endTime);
        $event->resultsClosed = false;
        
        
        $myAttendance = new \stdClass();
        $myAttendance->preStatus = "UNKNOWN";
        $myAttendance->postStatus = "UNKNOWN";
        $myAttendance->preDescription = "";
        $myAttendance->postDescription = "";
        if (!property_exists($event, "place"))
            $event->place = ""; //set default value
        if (!property_exists($event, "link"))
            $event->link = ""; //set default value
        if (property_exists($event, "attendance"))
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
        $event->myAttendance = $myAttendance;
        
        if(property_exists($event, "myAttendance") && property_exists($event->myAttendance, "preStatus")){
                $eventClassMap = [
                    "YES" => "success",
                    "LAT" => "warning",
                    "NO" => "danger",
                    "DKY" => "danger",
                    "UNKNOWN" => "secondary",
                ];
                $event->preClass = $eventClassMap[$event->myAttendance->preStatus];
            } else {
                $event->warning = true;
                $this->getResult()->menuWarningCount++;
            }
    }
    
    protected function getEventColors($event) {
        $colorList = $this->supplier->getEventColors();
        $eventColor = [];
        $invertColors = !property_exists($event, 'myAttendance') || !property_exists($event->myAttendance, 'preStatus');
        $eventColor["borderColor"] = $colorList[$event->type];
        $eventColor["backgroundColor"] = $invertColors ? 'white' : $colorList[$event->type];
        $eventColor["textColor"] = $invertColors ? $colorList[$event->type] : '';
        return $eventColor;
    }
    
    private function addMyAttendance($event){
        
    }
    
    protected function clearCache($id = NULL){
        $this->cache->remove($this->getCacheKey("GET:events"));
        if($id != NULL){
            $this->cache->remove($this->getCacheKey("GET:event/$id"));
        }
    }
}
