<?php
namespace Tymy\Module\Core\Manager;

use Nette\Database\Explorer;
use Nette\Security\User;
use Tymy\Module\Team\Manager\TeamManager;

/**
 * Description of StringsManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 9. 9. 2020
 */
class StringsManager
{

    public const TABLE = "strings";
    public const LC = ["CZ" => "cs", "EN" => "en", "FR" => "fr", "PL" => "pl"];

    private TeamManager $teamManager;
    private Explorer $database;
    private User $user;

    public function __construct(Explorer $mainDatabase, User $user, TeamManager $teamManager)
    {
        $this->database = $mainDatabase;
        $this->user = $user;
        $this->teamManager = $teamManager;
    }

    public function translate($message, ...$parameters): string
    {
        $parts = explode(".", $message);
        if (count($parts) != 2) {
            return $message;
        }

        $domain = $parts[0];
        $code = $parts[1];

        return $this->translateBy($domain, $code, $parameters);
    }

    /**
     * Get language code - either from registered user, or system default
     * @return string
     */
    private function getLc(): string
    {
        $code = $this->user->isLoggedIn() ? $this->user->getIdentity()->getData()["language"] : $this->teamManager->getTeam()->getDefaultLanguageCode();
        return self::LC[$code];
    }

    /**
     * Translate by domain and code
     * 
     * @param string $domain
     * @param string $code
     * @param mixed $parameters
     * @return string
     */
    public function translateBy(string $domain, string $code, ...$parameters): string
    {
        $value = $this->database->table(self::TABLE)->where("domain", $domain)->where("code", $code)->where("language", $this->getLc())->limit(1)->fetch()->value;

        return sprintf($value, ...$parameters);
    }
}