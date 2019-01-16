<?php

namespace Tapi;

use Tapi\Exception\APIException;

/**
 * Description of PermissionDetailResource
 *
 * @author kminekmatej, 15.1.2019
 */
class PermissionDetailResource extends PermissionResource {

    private $name;

    public function init() {
        parent::globalInit();
        $this->setCachingTimeout(TapiObject::CACHE_TIMEOUT_LARGE);
        return $this;
    }

    protected function preProcess() {
        if ($this->getId() == null && $this->getName() == null)
            throw new APIException('Either ID or NAME must be specified', self::BAD_REQUEST);

        if ($this->getId())
            $this->setUrl("permissions/" . $this->getId());
        else
            $this->setUrl("permissionName/" . $this->getName());
    }

    protected function postProcess() {
        parent::postProcessPermission($this->data);
    }

    public function getName() {
        return $this->options->name;
    }

    public function setName($name) {
        $this->options->name = $name;
        return $this;
    }

}
