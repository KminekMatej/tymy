<?php

namespace Tapi;

use Tapi\Exception\APIException;

/**
 * Description of MultiaccountRemoveResource
 *
 * @author kminekmatej, 23.11.2018
 */
class MultiaccountRemoveResource extends MultiaccountResource {

    /** @var String */
    private $team;

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
        $this->setTeam(NULL);
        return $this;
    }

    protected function preProcess() {
        if ($this->getTeam() == null)
            throw new APIException('Team is missing', self::BAD_REQUEST);

        $this->setUrl("multiaccount/" . $this->getTeam());
    }

    protected function postProcess() {
        $this->clearCache();
    }

    public function getTeam() {
        return $this->team;
    }

    public function setTeam(String $team = NULL) {
        $this->team = $team;
        return $this;
    }

}
