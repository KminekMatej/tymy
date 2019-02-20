<?php

namespace Tapi;

/**
 * Description of ConfigResource
 *
 * @author kminekmatej, 20.2.2019
 */
class ConfigResource extends TapiObject {

    private $name;
    private $sport;
    private $defaultLanguageCode;

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
        return $this;
    }

    protected function preProcess() {
        $this->setUrl("config");

        $data = [];
        if (isset($this->name))
            $data["name"] = $this->name;
        if (isset($this->sport))
            $data["sport"] = $this->sport;
        if (isset($this->defaultLanguageCode))
            $data["defaultLanguageCode"] = $this->defaultLanguageCode;

        if (!count($data))
            throw new APIException('Nothing to update', self::BAD_REQUEST);

        $this->setRequestData((object) $data);
    }

    protected function postProcess() {
        //nothing to do in here
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setSport($sport) {
        $this->sport = $sport;
        return $this;
    }

    public function setDefaultLanguageCode($defaultLanguageCode) {
        $this->defaultLanguageCode = $defaultLanguageCode;
        return $this;
    }

}
