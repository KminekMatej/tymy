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
        $this->timeLoad($discussion->updatedAt);
    }
    
}
