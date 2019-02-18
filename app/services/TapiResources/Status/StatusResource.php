<?php

namespace Tapi;

use Nette\Utils\Strings;

/**
 * Description of StatusResource
 *
 * @author kminekmatej, 18.2.2019
 */
abstract class StatusResource extends TapiObject {
    
    protected function postProcessStatus($status) {
        $status->color = $this->supplier->getStatusColor($status->code);
        $this->timeLoad($status->updatedAt);
    }
    
    protected function postProcessStatusSet($statusSet) {
        $statusSet->webName = Strings::webalize($statusSet->name);
        $statusSet->statusesByCode = [];
        if (!property_exists($statusSet, "statuses") || empty($statusSet->statuses))
            return;
        foreach ($statusSet->statuses as $status) {
            $this->postProcessStatus($status);
            $statusSet->statusesByCode[$status->code] = $status;
        }
    }

    protected function clearCache($id = NULL){
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:attendanceStatus"]);
    }
}
