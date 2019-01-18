<?php

namespace Tapi;

use Tapi\Exception\APIException;

/**
 * Description of PermissionEditResource
 *
 * @author kminekmatej, 18.1.2019
 */
class PermissionEditResource extends PermissionResource {

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
        $this->setAllowedRoles(NULL);
        $this->setRevokedRoles(NULL);
        $this->setAllowedStatuses(NULL);
        $this->setRevokedStatuses(NULL);
        $this->setAllowedUsers(NULL);
        $this->setRevokedUsers(NULL);
        $this->setName(NULL);
        $this->setType(NULL);
        $this->setCaption(NULL);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Event ID is missing', self::BAD_REQUEST);

        if ($this->getType() != NULL && $this->getType() != "USR")
            throw new APIException('Cannot change type to another type than USR', self::BAD_REQUEST);

        $this->setUrl("permissions/".$this->getId());
        $requestData = [];
        
        if ($this->getType() !== null)
            $requestData["type"] = $this->getType();
        if ($this->getName() !== null)
            $requestData["name"] = $this->getName();
        if ($this->getCaption() !== null)
            $requestData["caption"] = $this->getCaption();
        if ($this->getRevokedRoles() !== null)
            $requestData["revokedRoles"] = $this->getRevokedRoles();
        elseif ($this->getAllowedRoles() !== null)
            $requestData["allowedRoles"] = $this->getAllowedRoles();
        if ($this->getRevokedStatuses() !== null)
            $requestData["revokedStatuses"] = $this->getRevokedStatuses();
        elseif ($this->getAllowedStatuses() !== null)
            $requestData["allowedStatuses"] = $this->getAllowedStatuses();
        if ($this->getRevokedUsers() !== null)
            $requestData["revokedUsers"] = $this->getRevokedUsers();
        elseif ($this->getAllowedUsers() !== null)
            $requestData["allowedUsers"] = $this->getAllowedUsers();
        \Tracy\Debugger::barDump($this);
        \Tracy\Debugger::barDump($this->getAllowedUsers());
        \Tracy\Debugger::barDump($this->getRevokedUsers());
        \Tracy\Debugger::barDump($requestData, "Rq data");
        
        $this->setRequestData((object) $requestData);
    }

    protected function postProcess() {
        $this->clearCache();
        if ($this->data)
            parent::postProcessPermission($this->data);
    }

    public function getName() {
        return $this->options->name;
    }

    public function getType() {
        return $this->options->type;
    }

    public function getCaption() {
        return $this->options->caption;
    }

    public function getAllowedUsers() {
        return $this->options->allowedUsers;
    }

    public function getRevokedUsers() {
        return $this->options->revokedUsers;
    }

    public function getAllowedStatuses() {
        return $this->options->allowedStatuses;
    }

    public function getRevokedStatuses() {
        return $this->options->revokedStatuses;
    }

    public function getAllowedRoles() {
        return $this->options->allowedRoles;
    }

    public function getRevokedRoles() {
        return $this->options->revokedRoles;
    }

    public function setName($name) {
        $this->options->name = $name;
        return $this;
    }

    public function setType($type) {
        $this->options->type = $type;
        return $this;
    }

    public function setCaption($caption) {
        $this->options->caption = $caption;
        return $this;
    }

    public function setAllowedUsers($allowedUsers) {
        $this->options->allowedUsers = $allowedUsers;
        return $this;
    }

    public function setRevokedUsers($revokedUsers) {
        $this->options->revokedUsers = $revokedUsers;
        return $this;
    }

    public function setAllowedStatuses($allowedStatuses) {
        $this->options->allowedStatuses = $allowedStatuses;
        return $this;
    }

    public function setRevokedStatuses($revokedStatuses) {
        $this->options->revokedStatuses = $revokedStatuses;
        return $this;
    }

    public function setAllowedRoles($allowedRoles) {
        $this->options->allowedRoles = $allowedRoles;
        return $this;
    }

    public function setRevokedRoles($revokedRoles) {
        $this->options->revokedRoles = $revokedRoles;
        return $this;
    }

}
