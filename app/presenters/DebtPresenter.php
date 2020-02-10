<?php

namespace App\Presenters;

use Tapi\DebtDetailResource;
use Tapi\DebtListResource;
use Tapi\Exception\APIException;

/**
 * Description of DebtPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 10. 2. 2020
 */
class DebtPresenter extends SecuredPresenter {

    /** @var DebtListResource @inject */
    public $debtList;
    
    /** @var DebtDetailResource @inject */
    public $debtDetail;
    
    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("debt.debt", 2), "link" => $this->link("Debt:")]]);
    }

    public function renderDefault() {
        try {
            $this->debtList->init()
                    ->getData();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        
        $this->template->debts = $this->debtList->getData();
    }

    public function renderDebt($dluh) {
        try {
            $debtId = $this->parseIdFromWebname($dluh);
            $this->debtDetail->init()
                    ->setId($debtId)
                    ->getData();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        
        $this->template->debt = $this->debtDetail->getData();
    }

}
