<?php

namespace Tymy\Module\Core\Presenter;

use Nette\Application\UI\Presenter;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Utils\DateTime;
use Symfony\Component\Translation\Translator;
use Tymy\Module\Core\Model\Version;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\Team\Model\Team;
use const ROOT_DIR;
use const TEAM_DIR;

/**
 * Description of RootPresenter
 *
 * @author kminekmatej, 9. 1. 2022, 21:57:52
 */
abstract class RootPresenter extends Presenter
{
    private const LOCALES = ["CZ" => "cs", "EN" => "en-gb", "FR" => "fr", "PL" => "pl"];
    public const TEAM_CACHE = "tymy-cache";

    protected Team $team;
    
    /** @inject */
    public Translator $translator;

    /** @inject */
    public TeamManager $teamManager;

    /** @inject */
    public Storage $cacheStorage;
    protected Cache $teamCache;

    protected function startup()
    {
        parent::startup();

        $this->team = $this->teamManager->getTeam();
        $this->setLanguage($this->team->getDefaultLanguageCode());
        $this->teamCache = new Cache($this->cacheStorage, $this->team->getSysName());
    }

    protected function setLanguage(string $languageCode): void
    {
        $this->translator->setLocale(self::LOCALES[$languageCode]);
        $this->template->locale = $this->translator->getLocale();
    }

    /**
     * Return list of version objects
     *
     * @return Version[]
     */
    protected function getVersions(): array
    {
        $this->teamCache->clean([
            Cache::ALL => true,
        ]);

        return $this->teamCache->load("versions", function () {
                $versions = explode("\n", shell_exec('git -C ' . ROOT_DIR . ' tag -l --format="%(creatordate:iso8601)|%(refname:short)" --sort=-v:refname'));
                $out = [];
                foreach ($versions as $versionStr) {
                    if(empty(trim($versionStr))){
                        continue;
                    }
                    $parts = explode("|", $versionStr);
                    $out[$parts[1]] = new Version($parts[1], new DateTime($parts[0]));
                }
                return $out;
            });
    }

    /**
     * Get current version object
     * @return Version
     */
    protected function getCurrentVersion(): Version
    {
        $cvName = basename(dirname(readlink(TEAM_DIR . "/app"), 1));
        return $cvName == "master" ? new Version($cvName, null) : $this->getVersions()[$cvName];
    }
}
