<?php

namespace Tapi;

/**
 * Description of MultiaccountAddResource
 *
 * @author kminekmatej, 23.11.2018
 */
class MultiaccountAddResource extends MultiaccountResource {

    /** @var String */
    private $team;

    /** @var String */
    private $username;

    /** @var String */
    private $password;

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setTeam(NULL);
        $this->setUsername(NULL);
        $this->setPassword(NULL);
        return $this;
    }

    protected function preProcess() {
        if ($this->getTeam() == null)
            throw new APIException('Team is missing', self::BAD_REQUEST);
        if ($this->getUsername() == null)
            throw new APIException('Username is missing', self::BAD_REQUEST);
        if ($this->getPassword() == null)
            throw new APIException('Password is missing', self::BAD_REQUEST);

        $this->setUrl("multiaccount/" . $this->getTeam());
        $this->setRequestData((object) [
                    "login" => $this->getUsername(),
                    "password" => $this->getPassword()
        ]);
    }

    protected function postProcess() {
        $this->clearCache();
    }

    public function getTeam() {
        return $this->team;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setTeam(String $team = NULL) {
        $this->team = $team;
        return $this;
    }

    public function setUsername(String $username = NULL) {
        $this->username = $username;
        return $this;
    }

    public function setPassword(String $password = NULL) {
        $this->password = $password;
        return $this;
    }

}
