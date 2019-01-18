<?php

namespace Tapi;

use Tapi\Exception\APIException;

/**
 * Description of PermissionCreateResource
 *
 * @author kminekmatej, 18.1.2019
 */
class PermissionCreateResource extends PermissionResource {

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setAllowedRoles(NULL);
        $this->setRevokedRoles(NULL);
        $this->setAllowedStatuses(NULL);
        $this->setRevokedStatuses(NULL);
        $this->setAllowedUsers(NULL);
        $this->setRevokedUsers(NULL);
        $this->setName(NULL);
        $this->setType("USR");
        $this->setCaption(NULL);
        return $this;
    }

    protected function preProcess() {
        if ($this->getName() == NULL)
            throw new APIException('Name is missing', self::BAD_REQUEST);
        if ($this->getType() == NULL)
            throw new APIException('Type is missing', self::BAD_REQUEST);
        if ($this->getType() != "USR")
            throw new APIException('Only USR type can be created', self::BAD_REQUEST);

        $this->setUrl("permissions");
        $requestData = [
            "type" => $this->getType(),
            "name" => $this->getName()
        ];
        if ($this->getCaption())
            $requestData["caption"] = $this->getCaption();
        if ($this->getRevokedRoles())
            $requestData["revokedRoles"] = $this->getRevokedRoles();
        elseif ($this->getAllowedRoles())
            $requestData["allowedRoles"] = $this->getAllowedRoles();
        if ($this->getRevokedStatuses())
            $requestData["revokedStatuses"] = $this->getRevokedStatuses();
        elseif ($this->getAllowedStatuses())
            $requestData["allowedStatuses"] = $this->getAllowedStatuses();
        if ($this->getRevokedUsers())
            $requestData["revokedUsers"] = $this->getRevokedUsers();
        elseif ($this->getAllowedUsers())
            $requestData["allowedUsers"] = $this->getAllowedUsers();

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
