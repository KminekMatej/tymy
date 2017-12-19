<?php

namespace Tapi;
use Tapi\TapiAbstraction;
use Nette\Utils\Strings;

/**
 * Description of AttendanceResource
 *
 * @author kminekmatej
 */
abstract class DiscussionResource extends TapiAbstraction {
    
    protected function postProcessDiscussion($discussion) {
        $discussion->webName = Strings::webalize($discussion->caption);
        if(!empty($discussion->updatedAt)){
            $this->timeLoad($discussion->updatedAt);
        }
        if(!empty($discussion->newInfo->lastVisit)){
            $this->timeLoad($discussion->newInfo->lastVisit);
        }
    }
    
}
