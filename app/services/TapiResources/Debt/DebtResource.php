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
    
    const CURRENCIES = ["CZK" => "Kč", "EUR" => "€"];
    
    protected function postProcessDebt($debt) {
        if($debt== null) TapiService::throwNotFound();
        $debt->webName = Strings::webalize($debt->id . "-" . $debt->caption);
        if(!empty($debt->created)){
            $this->timeLoad($debt->created);
        }
        if(!empty($debt->debtDate)){
            $this->timeLoad($debt->debtDate);
        }
        if(!property_exists($debt, "paymentSent")) $debt->paymentSent = null;
        if(!property_exists($debt, "paymentReceived")) $debt->paymentReceived = null;
        
        $debt->class = $debt->payeeId == $this->user->getId() || (empty($data->payeeId) && $debt->debtorId != $this->user->getId()) ? "debt incoming" : "debt pending";
        if (!empty($debt->paymentReceived)) {
            $debt->class = "debt received";
        } elseif (!empty($debt->paymentSent)) {
            $debt->class = "debt sent";
        }
        
    }
    
    protected function clearCache($id = NULL){
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:debt"]);
        if($id != NULL){
            $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:debt/$id"]);
            $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:debt/$id"]);
        }
    }

    public function postProcessWithUsers(array $userList, array &$debts) {
        foreach ($debts as &$debt) {
            $debt->payee = $debt->payeeId > 0 ? $userList[$debt->payeeId] : null;
            $debt->debtor = $debt->debtorId > 0 ? $userList[$debt->debtorId] : null;
            $debt->author = $debt->createdUserId ? $userList[$debt->createdUserId] : null;
            
            $debt->debtorCallName = $debt->debtorType == self::TYPE_USER ? $debt->debtor->callName : "TÝM";
            $debt->payeeCallName = $debt->payee ? $debt->payee->callName : "TÝM";

            if ($debt->canSetSentDate) {
                $debt->displayString = $debt->debtorType == "team" ? $debt->debtorCallName . " → " . $debt->payeeCallName : "→ " . $debt->payeeCallName;
            } else {
                $debt->displayString = $debt->payeeType == "team" ? $debt->debtorCallName . " → " . $debt->payeeCallName : $debt->debtorCallName;
            }
        }
    }
}
