<?php

namespace Tymy\App\Presenters;

use Kdyby\Translation\Translator;
use Nette;
use Nette\Utils\DateTime;
use Tapi\Exception\APIException;
use Tracy\Debugger;
use Tymy\App\Model\Supplier;
use Tymy\Module\Team\Manager\TeamManager;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {
    
    const LOCALES = ["CZ" => "cs", "EN" => "en-gb", "FR" => "fr", "PL" => "pl"];
    
    /** @persistent */
    public $locale;

    /** @var Translator @inject */
    public $translator;

    /** @var Supplier @inject */
    public $supplier;
    
    /** @inject */
    public TeamManager $teamManager;
    
    public function beforeRender() {
        parent::beforeRender();
        $this->translator->setDefaultLocale("EN");
        $this->template->setTranslator($this->translator);
        date_default_timezone_set('Europe/Prague');
        
        $this->template->locale = $this->translator->getLocale();
        
        $this->template->js = Debugger::$productionMode ? "min.js" : "js";
        $this->template->css = Debugger::$productionMode ? "min.css" : "css";
        
        $this->template->team = $this->teamManager->getTeam();
        $this->template->tymyRoot = $this->supplier->getTymyRoot();
        $this->template->apiRoot = $this->supplier->getApiRoot();
        
        $this->template->wwwDir = $this->supplier->getWwwDir();
        $this->template->appDir = $this->supplier->getAppDir();
        $this->template->skin = $this->supplier->getSkin();
        
        $this->template->appver = $this->supplier->getVersionCode();
        
        $this->template->addFilter('monthName', function ($number) {
            return $this->translator->translate("common.months." . strtolower(DateTime::createFromFormat("!m", $number)->format("F"))) ;
        });
    }
    
    public function handleTapiException(APIException $ex, $redirect = null, $args = []){
        $this->flashMessage($this->translator->translate("common.alerts.exceptionOccured") . ": " . $ex->getMessage(), "danger");
        if($redirect)
            $this->redirect ($redirect, $args);
        else
            $this->error($this->translator->translate("common.alerts.exceptionOccured") .": " . $ex->getMessage(), $ex->getCode());
    }
    
}