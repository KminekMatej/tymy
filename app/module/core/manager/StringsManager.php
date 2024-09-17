<?php

namespace Tymy\Module\Core\Manager;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Security\User;
use Tracy\Debugger;
use Tracy\ILogger;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Model\User as TymyUser;

use function count;

class StringsManager
{
    public const TABLE = "strings";
    public const LC = ["CZ" => "cs", "EN" => "en", "FR" => "fr", "PL" => "pl"];

    public function __construct(
        private readonly Explorer $database,
        private readonly User $user,
        private readonly TeamManager $teamManager
    ) {
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
     */
    private function getLc(): string
    {
        if ($this->user->isLoggedIn()) {
            $code = $this->database->table(TymyUser::TABLE)->where("id", $this->user->getId())->fetch()->language;
        } else {
            $code = $this->teamManager->getTeam()->getDefaultLanguageCode();
        }
        return self::LC[$code];
    }

    /**
     * Translate by domain and code
     */
    public function translateBy(string $domain, string $code, mixed ...$parameters): string
    {
        $translation = $this->database->table(self::TABLE)->where("domain", $domain)->where("code", $code)->where("language", $this->getLc())->limit(1)->fetch();

        if (!$translation instanceof ActiveRow) {
            Debugger::log("Missing translation: $domain.$code", ILogger::ERROR);
        }

        return $translation !== null ? sprintf($translation->value, ...$parameters) : "Missing translation: $domain.$code";
    }
}
