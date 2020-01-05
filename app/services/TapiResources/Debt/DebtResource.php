<?php

namespace Tapi;
use Tapi\TapiObject;
use Nette\Utils\Strings;
use Nette\Caching\Cache;

/**
 * Description of DebtResource
 *
 * @author kminekmatej, 5.1.2020
 */
abstract class DebtResource extends TapiObject {
    
    const TYPE_USER="user";
    const TYPE_TEAM="team";
    
    protected function postProcessDebt($debt) {
        if($debt== null) TapiService::throwNotFound();
        $debt->webName = Strings::webalize($debt->caption);
        if(!empty($debt->created)){
            $this->timeLoad($debt->created);
        }
        if(!empty($debt->debtDate)){
            $this->timeLoad($debt->debtDate);
        }
        if(!property_exists($debt, "paymentSent")) $debt->paymentSent = null;
        if(!property_exists($debt, "paymentReceived")) $debt->paymentReceived = null;
    }
    
    protected function clearCache($id = NULL){
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:debt"]);
        if($id != NULL){
            $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:debt/$id"]);
            $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:debt/$id"]);
        }
    }
}
