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
    
    protected function postProcess() {
        $this->getData()->webName = Strings::webalize($this->getData()->caption);    
    }
    
}
