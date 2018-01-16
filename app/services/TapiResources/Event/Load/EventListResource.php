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
    
    protected function init() {
        $this->setCachingTimeout(CacheService::TIMEOUT_TINY);
        $this->setWithMyAttendance(TRUE);
        $this->setFrom(NULL);
        $this->setTo(NULL);
        $this->setOrder(NULL);
        $this->setLimit(NULL);
        $this->setOffset(NULL);
        $this->options->asArray = NULL;
        $this->options->asMonthArray = NULL;
        $this->options->allEventsCount = NULL;
        $this->options->withMyAttendance = NULL;
    }

    protected function preProcess() {
        $this->setUrl($this->getWithMyAttendance() ? "events/withMyAttendance" : "events");
        
        $filter = [];
        if($this->getFrom())
            $filter[] = "startTime>" . $this->getFrom();
        if($this->getTo())
            $filter[] = "startTime<" . $this->getTo();
            
        if(count($filter)){
            $this->setRequestParameter("filter", join("~", $filter));
        }
        
        if($this->getOrder()){
            $this->setRequestParameter("order", $this->getOrder());
        }
        
        if($this->getLimit()){
            $this->setRequestParameter("limit", $this->getLimit());
        }
        
        if($this->getOffset()){
            $this->setRequestParameter("offset", $this->getOffset());
        }
    }
    
    protected function postProcess() {
        $this->options->asArray = []; //eventJSObject
        $this->options->asMonthArray = []; //eventsMonthly
        
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
            $this->options->asArray[] = (object)array_merge($eventProps, $eventColors);
            $month = date("Y-m", strtotime($event->startTime));
            $this->options->asMonthArray[$month][] = $event;
        }
    }
    
    public function getFrom() {
        return $this->options->from;
    }

    public function getTo() {
        return $this->options->to;
    }

    public function getOrder() {
        return $this->options->order;
    }

    public function getLimit() {
        return $this->options->limit;
    }

    public function getOffset() {
        return $this->options->offset;
    }

    public function setFrom($from) {
        $this->options->from = $from;
        return $this;
    }

    public function setTo($to) {
        $this->options->to = $to;
        return $this;
    }

    public function setOrder($order) {
        $this->options->order = $order;
        return $this;
    }

    public function setLimit($limit) {
        $this->options->limit = $limit;
        return $this;
    }

    public function setOffset($offset) {
        $this->options->offset = $offset;
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
        return $this->options->asArray;
    }

    public function getAsMonthArray() {
        return $this->options->asMonthArray;
    }

    public function getAllEventsCount() {
        $allEventsCount = $this->cacheService->load(self::EVENT_COUNT_CACHE_KEY);
        if($allEventsCount == null){
            $listAllEvents = new EventListResource($this->supplier, $this->user, $this->cacheService, $this->tapiService);
            $allEventsCount = count($listAllEvents->setWithMyAttendance(FALSE)->getData());
            $this->setAllEventsCount($allEventsCount);
        }
        return $this->options->allEventsCount;
    }

    public function setAllEventsCount($allEventsCount) {
        $this->cacheService->save(self::EVENT_COUNT_CACHE_KEY, $allEventsCount, CacheService::TIMEOUT_NONE);
        $this->options->allEventsCount = $allEventsCount;
        return $this;
    }

    public function getWithMyAttendance() {
        return $this->options->withMyAttendance;
    }

    public function setWithMyAttendance($withMyAttendance) {
        $this->options->withMyAttendance = $withMyAttendance;
        return $this;
    }

}
