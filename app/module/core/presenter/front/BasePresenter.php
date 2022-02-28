<?php

namespace Tymy\Module\Core\Presenter\Front;

use Nette\Utils\DateTime;
use Tracy\Debugger;
use Tymy\Bootstrap;
use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Core\Presenter\RootPresenter;

use const ROOT_DIR;

/**
 * Base presenter for all front application presenters.
 */
abstract class BasePresenter extends RootPresenter
{
    /** @persistent */
    public $locale;

    protected string $skin;

    public function beforeRender()
    {
        parent::beforeRender();
        $this->template->componentsDir = Bootstrap::MODULES_DIR . "/core/presenter/templates/components";
        $this->template->setTranslator($this->translator);
        date_default_timezone_set('Europe/Prague');

        $this->template->locale = $this->translator->getLocale();

        $this->template->publicPath = $this->getHttpRequest()->getUrl()->getBasePath() . "public";

        $this->template->js = Debugger::$productionMode ? "min.js" : "js";
        $this->template->css = Debugger::$productionMode ? "min.css" : "css";

        $this->template->team = $this->teamManager->getTeam();

        $this->template->wwwDir = ROOT_DIR . "/www";
        if ($this->getUser()->isLoggedIn()) {
            $this->skin = $this->getUser()->getIdentity()->getData()["skin"];
        } else {
            $this->skin = $this->team->getSkin();
        }
        $this->template->skin = $this->skin;

        $this->template->appver = $this->getCurrentVersion()->getName();

        $this->template->addFilter('monthName', function ($number) {
            return $this->translator->translate("common.months." . strtolower(DateTime::createFromFormat("!m", strval($number))->format("F"))) ;
        });
    }

    public function formatLayoutTemplateFiles(): array
    {
        return [Bootstrap::MODULES_DIR . "/core/presenter/templates/@layout.latte"];
    }

    protected function handleTymyResponse(TymyResponse $tResp)
    {
        $this->flashMessage($tResp->getMessage(), $tResp->getSuccess() ? 'success' : 'warning');
        $this->redrawControl("flashes");
    }
}
