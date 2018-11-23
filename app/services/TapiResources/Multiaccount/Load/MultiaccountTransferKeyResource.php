<?php

namespace Tapi;

/**
 * Description of MultiaccountTransferKeyResource
 *
 * @author kminekmatej, 22.11.2018
 */
class MultiaccountTransferKeyResource extends MultiaccountResource {

    /** @var String */
    private $transferKey;

    /** @var String */
    private $team;

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        return $this;
    }

    protected function preProcess() {
        $this->setUrl("multiaccount/" . $this->getTeam());
    }

    protected function postProcess() {
        if ($this->data == null)
            return null;
        \Tracy\Debugger::barDump($this->data->transferKey);
        $this->setTransferKey($this->data->transferKey);
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

}
