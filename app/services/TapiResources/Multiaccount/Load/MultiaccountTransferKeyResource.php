<?php

namespace Tapi;

use Tapi\Exception\APIException;

/**
 * Description of MultiaccountTransferKeyResource
 *
 * @author kminekmatej, 22.11.2018
 */
class MultiaccountTransferKeyResource extends MultiaccountResource {

    /** @var String */
    private $transferKey;

    /** @var int */
    private $uid;

    /** @var String */
    private $team;

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        return $this;
    }

    protected function preProcess() {
        if ($this->getTeam() == null)
            throw new APIException('Team is missing', self::BAD_REQUEST);
        $this->setUrl("multiaccount/" . $this->getTeam());
    }

    protected function postProcess() {
        if ($this->data == null)
            return null;
        $this->setTransferKey($this->data->transferKey);
        $this->setUid($this->data->uid);
    }

    public function getTransferKey() {
        return $this->transferKey;
    }

    public function setTransferKey(String $transferKey) {
        $this->transferKey = $transferKey;
        return $this;
    }

    public function getTeam() {
        return $this->team;
    }

    public function setTeam(String $team) {
        $this->team = $team;
        return $this;
    }

    public function getUid() {
        return $this->uid;
    }

    public function setUid($uid) {
        $this->uid = $uid;
        return $this;
    }

}
