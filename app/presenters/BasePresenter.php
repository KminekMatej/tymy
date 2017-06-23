<?php

namespace App\Presenters;

use Nette;
use App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {
    
    /** @var \App\Model\Supplier @inject */
    public $supplier;
    
    public function beforeRender() {
        parent::beforeRender();
        
        date_default_timezone_set('Europe/Prague');
        
        $this->template->js = \Tracy\Debugger::$productionMode ? "min.js" : "js";
        $this->template->css = \Tracy\Debugger::$productionMode ? "min.css" : "css";
        
        $this->template->tym = $this->supplier->getTym();
        $this->template->tymyRoot = $this->supplier->getTymyRoot();
        $this->template->apiRoot = $this->supplier->getApiRoot();
        $this->template->sysapiRoot = $this->supplier->getSysapiRoot();
        
        $this->template->addFilter('monthName', function ($number) {
            switch ($number) {
                case 1: return 'Leden';
                case 2: return 'Únor';
                case 3: return 'Březen';
                case 4: return 'Duben';
                case 5: return 'Květen';
                case 6: return 'Červen';
                case 7: return 'Červenec';
                case 8: return 'Srpen';
                case 9: return 'Září';
                case 10: return 'Říjen';
                case 11: return 'Listopad';
                case 12: return 'Prosinec';
            }
        });
    }
    
}