<?php
namespace Tymy\Module\Core\Presenter;

use Nette\Application\UI\Presenter;
use Symfony\Component\Translation\Translator;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\Team\Model\Team;

/**
 * Description of RootPresenter
 *
 * @author kminekmatej, 9. 1. 2022, 21:57:52
 */
abstract class RootPresenter extends Presenter
{

    private const LOCALES = ["CZ" => "cs", "EN" => "en-gb", "FR" => "fr", "PL" => "pl"];

    protected Team $team;

    /** @inject */
    public Translator $translator;

    /** @inject */
    public TeamManager $teamManager;

    protected function startup()
    {
        parent::startup();

        $this->team = $this->teamManager->getTeam();
        $this->setLanguage($this->team->getDefaultLanguageCode());
    }

    protected function setLanguage(string $languageCode): void
    {
        $this->translator->setLocale(self::LOCALES[$languageCode]);
        $this->template->locale = $this->translator->getLocale();
    }
}
