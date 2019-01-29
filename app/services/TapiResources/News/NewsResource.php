<?php

namespace Tapi;

use Nette\Utils\DateTime;
use Nette\Utils\Strings;

/**
 * Description of NewsResource
 *
 * @author kminekmatej, 29.1.2019
 */
abstract class NewsResource extends TapiObject {

    protected function postProcessNotice($notice) {
        $notice->webName = Strings::webalize($notice->id . "-" . $notice->caption);
        $notice->created = new DateTime($this->timeLoad($notice->created));
        $notice->isGlobal = empty($notice->team);
    }

}
