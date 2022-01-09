<?php

namespace Tymy\Module\Core\Presenter\Front;

use Kdyby\Translation\Translator;
use Nette;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Utils\DateTime;
use Tracy\Debugger;
use Tymy\Bootstrap;
use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Core\Model\Supplier;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\Team\Model\Team;
use const ROOT_DIR;


/**
 * Base presenter for all front application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {
    
    const LOCALES = ["CZ" => "cs", "EN" => "en-gb", "FR" => "fr", "PL" => "pl"];
    
    /** @persistent */
    public $locale;

    /** @inject */
    public Translator $translator;

    /** @inject */
    public Supplier $supplier;
    
    /** @inject */
    public TeamManager $teamManager;
    protected Team $team;
    protected string $skin;

	protected function startup()
    {
        parent::startup();

        $this->team = $this->teamManager->getTeam();
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->template->componentsDir = Bootstrap::MODULES_DIR . "/core/presenter/templates/components";
        $this->template->setTranslator($this->translator);
        $this->translator->setLocale(\Tymy\Module\Core\Model\BaseModel::LANG_LOCALES[$this->team->getDefaultLanguageCode()]);
        date_default_timezone_set('Europe/Prague');

        $this->template->locale = $this->translator->getLocale();

        $this->template->publicPath = $this->getHttpRequest()->getUrl()->getBasePath() . "public";

        $this->template->js = Debugger::$productionMode ? "min.js" : "js";
        $this->template->css = Debugger::$productionMode ? "min.css" : "css";

        $this->template->team = $this->teamManager->getTeam();
        
        $this->template->wwwDir = ROOT_DIR . "/www";
        $this->template->skin = $this->skin = $this->team->getSkin();

        $this->template->appver = $this->supplier->getVersionCode();
        
        $this->template->addFilter('monthName', function ($number) {
            return $this->translator->translate("common.months." . strtolower(DateTime::createFromFormat("!m", strval($number))->format("F"))) ;
        });
    }
    
    public function formatLayoutTemplateFiles(): array
    {
        return [Bootstrap::MODULES_DIR . "/core/presenter/templates/@layout.latte"];
    }

    public function run(Request $request): Response
    {
        try {
            return parent::run($request);
        } catch (TymyResponse $tResp) {
            //convert TymyException into proper flash message
            $this->flashMessage($tResp->getMessage() . "(" . $tResp->getCode() . ")", $tResp->getSuccess() ? "success" : "danger");
        }
    }
}
