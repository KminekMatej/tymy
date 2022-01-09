<?php
namespace Tymy\Module\Core\Presenter;

use Nette\Application\UI\Presenter;

/**
 * Description of RootPresenter
 *
 * @author kminekmatej, 9. 1. 2022, 21:57:52
 */
class RootPresenter extends Presenter
{

    protected Team $team;

    /** @inject */
    public Translator $translator;

    /** @inject */
    public TeamManager $teamManager;

    protected function startup()
    {
        parent::startup();

        $this->team = $this->teamManager->getTeam();
        $this->translator->setLocale(self::LOCALES[$this->team->getDefaultLanguageCode()]);
    }
}
