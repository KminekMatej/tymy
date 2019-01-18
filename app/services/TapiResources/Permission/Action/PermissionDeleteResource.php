<?php

namespace Tapi;

use Tapi\Exception\APIException;

/**
 * Description of PermissionDeleteResource
 *
 * @author kminekmatej, 18.1.2019
 */
class PermissionDeleteResource extends PermissionResource {

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
        return $this;
    }

    protected function preProcess() {
        if ($this->getId() == NULL)
            throw new APIException('ID is missing', self::BAD_REQUEST);

        $this->setUrl("permissions/".$this->getId());
    }

    protected function postProcess() {
        $this->clearCache();
    }

}
