<?php

namespace Tymy\Module\Core\Presenter;

use Contributte\Translation\Translator;
use Nette\Application\UI\Presenter;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Utils\DateTime;
use Tracy\Debugger;
use Tymy\Module\Core\Model\Version;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\Team\Model\Team;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\User;

use const ROOT_DIR;
use const TEAM_DIR;

/**
 * Description of RootPresenter
 */
abstract class RootPresenter extends Presenter
{
    public const TEAM_CACHE = "tymy-cache";
    private const LOCALES = ["CZ" => "cs", "EN" => "en-gb", "FR" => "fr", "PL" => "pl"];

    protected Team $team;

    #[\Nette\DI\Attributes\Inject]
    public Translator $translator;

    #[\Nette\DI\Attributes\Inject]
    public TeamManager $teamManager;

    #[\Nette\DI\Attributes\Inject]
    public UserManager $userManager;

    #[\Nette\DI\Attributes\Inject]
    public Storage $cacheStorage;
    protected Cache $teamCache;
    protected ?User $tymyUser;

    protected function startup()
    {
        parent::startup();

        $this->team = $this->teamManager->getTeam();
        $this->setLanguage($this->team->getDefaultLanguageCode());
        $timezoneName = str_replace("Europe/Paris", "Europe/Prague", timezone_name_from_abbr("", $this->team->getTimeZone() * 3600, false)); //get tz name from hours of shift but dont display paris, display prague ;)
        date_default_timezone_set($timezoneName);
        $this->teamCache = new Cache($this->cacheStorage, $this->team->getSysName());

        if ($this->getUser()->isLoggedIn()) {
            $this->initUser();
        }
    }

    /**
     * After succesful login, load logged user into tymyUser variable
     */
    protected function initUser(): void
    {
        $this->tymyUser = $this->userManager->getById($this->getUser()->getId());

        if ($this->tymyUser instanceof User && $this->tymyUser->getLanguage()) {
            $this->setLanguage($this->tymyUser->getLanguage());
        }
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

        return $this->teamCache->load("versions", function (): array {
                $dirToCheckVersions = is_dir(ROOT_DIR . '/../develop') ? ROOT_DIR . '/../develop' : ROOT_DIR;
                $gitv = shell_exec('git -C ' . $dirToCheckVersions . ' tag -l --format="%(creatordate:iso8601)|%(refname:short)" --sort=-v:refname');
                $versions = explode("\n", $gitv ?: "");
                $out = [];
            foreach ($versions as $versionStr) {
                if (empty(trim($versionStr))) {
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
     */
    protected function getCurrentVersion(): Version
    {
        if (is_link(TEAM_DIR . "/app")) {
            $cvName = basename(dirname(readlink(TEAM_DIR . "/app"), 1));
        } else {
            $cvName = shell_exec("git rev-parse --abbrev-ref HEAD");
        }

        return $cvName == "master" ? new Version($cvName, null) : ($this->getVersions()[$cvName] ?? new Version($cvName, null));
    }
}
