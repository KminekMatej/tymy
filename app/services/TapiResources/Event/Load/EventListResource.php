<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of EventDetailResource
 *
 * @author kminekmatej created on 24.12.2017, 18:17:02
 */
class EventListResource extends EventResource {
    
    const PAGING_EVENTS_PER_PAGE = 15;
    const EVENT_COUNT_CACHE_KEY = "EVENT_COUNT_CACHE_KEY";
    
    private $from;
    private $to;
    private $order;
    private $limit;
    private $offset;
    private $asArray;
    private $asMonthArray;
    private $allEventsCount;
    private $withMyAttendance;
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setWithMyAttendance(TRUE);
    }

    protected function preProcess() {
        $this->setUrl($this->withMyAttendance ? "events/withMyAttendance" : "events");
        
        $filter = [];
        if($this->from)
            $filter[] = "startTime>" . $this->from;
        if($this->to)
            $filter[] = "startTime<" . $this->to;
            
        if(count($filter)){
            $this->setRequestParameter("filter", join("~", $filter));
        }
        
        if($this->order){
            $this->setRequestParameter("order", $this->order);
        }
        
        if($this->limit){
            $this->setRequestParameter("limit", $this->limit);
        }
        
        if($this->offset){
            $this->setRequestParameter("offset", $this->offset);
        }
    }
    
    protected function postProcess() {
        $this->asArray = []; //eventJSObject
        $this->asMonthArray = []; //eventsMonthly
        
        if($this->data == null)
            return null;
        
        if($this->getLimit() == null && $this->getOffset() == null && $this->getFrom() == null && $this->getTo() == null){
            $this->setAllEventsCount(count($this->data));
        }
        
        foreach ($this->data as $event) {
            parent::postProcessEvent($event);
            $eventColors = $this->getEventColors($event);
            $eventProps = [
                "id" => $event->id,
                "title" => $event->caption,
                "start" => $event->startTime,
                "end" => $event->endTime,
                "webName" => $event->webName
            ];
            $this->asArray[] = (object)array_merge($eventProps, $eventColors);
            $month = date("Y-m", strtotime($event->startTime));
            $this->asMonthArray[$month][] = $event;
        }
    }
    
    public function getFrom() {
        return $this->from;
    }

    public function getTo() {
        return $this->to;
    }

    public function getOrder() {
        return $this->order;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function getOffset() {
        return $this->offset;
    }

    public function setFrom($from) {
        $this->from = $from;
        return $this;
    }

    public function setTo($to) {
        $this->to = $to;
        return $this;
    }

    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
        return $this;
    }
    
    public function setHalfYearFrom($date = NULL, $direction = NULL){
        $this->setFrom(date("Ym", strtotime("-6 months")) . "01");
        $this->setTo(date("Ym", strtotime("+6 months")) . "01");

        if ($direction == 1) {
            $this->setTo(date("Ym", strtotime("$date-01 +6 months")) . "01");
        } elseif ($direction == -1) {
            $this->setFrom(date("Ym", strtotime("$date-01 -6 months")) . "01");
        }
        
        $this->setOrder("startTime");
        
        return $this;
    }

    public function getAsArray() {
        return $this->asArray;
    }

    public function getAsMonthArray() {
        return $this->asMonthArray;
    }

    public function getAllEventsCount() {
        $allEventsCount = $this->cacheService->load(self::EVENT_COUNT_CACHE_KEY);
        if($allEventsCount == null){
            $listAllEvents = new EventListResource($this->supplier, $this->tapiAuthenticator, $this->user, $this->cacheService);
            $allEventsCount = count($listAllEvents->setWithMyAttendance(FALSE)->getData());
            $this->setAllEventsCount($allEventsCount);
        }
        return $this->allEventsCount;
    }

    public function setAllEventsCount($allEventsCount) {
        $this->cacheService->save(self::EVENT_COUNT_CACHE_KEY, $allEventsCount, CacheService::TIMEOUT_NONE);
        $this->allEventsCount = $allEventsCount;
        return $this;
    }

    public function getWithMyAttendance() {
        return $this->withMyAttendance;
    }

    public function setWithMyAttendance($withMyAttendance) {
        $this->withMyAttendance = $withMyAttendance;
        return $this;
    }

}
