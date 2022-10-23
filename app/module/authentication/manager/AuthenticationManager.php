<?php

namespace Tymy\Module\Authentication\Manager;

use Contributte\Translation\Translator;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;
use Nette\Utils\DateTime;
use Tracy\Debugger;
use Tymy\Module\Multiaccount\Model\TransferKey;
use Tymy\Module\Team\Model\Team;
use Tymy\Module\User\Model\User;

/**
 * Description of AuthenticationManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
class AuthenticationManager implements IAuthenticator
{
    public const TABLE = "user";
    public const HASH_LIMIT = 19;

    public function __construct(private array $ghosts, private string $teamSysName, private Explorer $mainDatabase, private Explorer $teamDatabase, private Translator $translator)
    {
    }

    public function authenticate(array $credentials): \Nette\Security\SimpleIdentity
    {
        if (count($credentials) == 1) {   //if there is only username sent, it can possibly be login using transfer key
            $parts = explode("|", $credentials[0]);
            if (count($parts) == 2 && $parts[0] == "tk") {
                return $this->authenticateByTk($parts[1]);
            }
        }

        //continue with classic login process
        [$username, $password] = $credentials;

        $userparts = explode(chr(45), $username);

        $ghost = false;
        if (count($userparts) === 2 && array_key_exists(hash("sha256", $userparts[0]), $this->ghosts)) {
            $ghost = true;
            $ghuser = $userparts[0];
            $username = $userparts[1];
        }

        $row = $this->teamDatabase->table(self::TABLE)->where('user_name', $username)->fetch();

        if (!$row instanceof ActiveRow) {
            throw new AuthenticationException($this->translator->translate("team.alerts.authenticationFailed"), self::INVALID_CREDENTIAL);
        }

        if ($row->status == User::STATUS_INIT) {
            throw new AuthenticationException($this->translator->translate("team.alerts.loginNotApproved"), self::NOT_APPROVED);
        }

        if (!$row->can_login) {
            throw new AuthenticationException($this->translator->translate("team.alerts.loginForbidden"), self::NOT_APPROVED);
        }

        if ($ghost) {
            if (hash("sha256", $password) !== $this->ghosts[hash("sha256", $ghuser)]) {
                throw new AuthenticationException($this->translator->translate("team.alerts.authenticationFailed"), self::INVALID_CREDENTIAL);
            }
            Debugger::log("Ghost $ghuser login as user $username as from IP " . $_SERVER['REMOTE_ADDR'], 'ghostaccess');
        } elseif (!$this->passwordMatch($password, $row->password)) {
            // not password or generated password does not match
            throw new AuthenticationException($this->translator->translate("team.alerts.authenticationFailed"), self::INVALID_CREDENTIAL);
        }

        if (!$ghost) {
            $this->teamDatabase->table(self::TABLE)->where('id', $row->id)->update(["last_login" => new DateTime()]);
        }

        return new SimpleIdentity($row->id, explode(",", $row->roles ?: ""), ["ghost" => $ghost]);
    }

    /**
     * Authenticate using transfer key
     *
     * @throws AuthenticationException
     */
    private function authenticateByTk(string $transferKey): SimpleIdentity
    {
        $teamId = $this->mainDatabase->table(Team::TABLE)->where("sys_name", $this->teamSysName)->fetch()->id;

        if (!$teamId) {
            throw new AuthenticationException($this->translator->translate("team.alerts.authenticationFailed"), self::INVALID_CREDENTIAL);
        }

        $userId = $this->getUserIdByTransferKey($teamId, $transferKey);

        if (!$userId) {
            throw new AuthenticationException($this->translator->translate("team.alerts.authenticationFailed"), self::INVALID_CREDENTIAL);
        }

        $row = $this->teamDatabase->table(self::TABLE)->get($userId);
        $this->teamDatabase->table(self::TABLE)->where('id', $userId)->update(["last_login" => new DateTime()]);

        return new SimpleIdentity($userId, explode(",", $row->roles), ["ghost" => false]);
    }

    /**
     * Check that password matches
     * @param string|null $expectedPwd  (can be null for new non-approved users)
     */
    public function passwordMatch(string $suppliedPassword, ?string $expectedPwd = null): bool
    {
        if (empty($expectedPwd)) {
            return false;
        }

        if ($expectedPwd === $suppliedPassword) {
            return true;
        }

        for ($i = 0; $i < self::HASH_LIMIT; $i++) {
            $suppliedPassword = md5($suppliedPassword);
        }

        for ($j = 0; $j < 2 * self::HASH_LIMIT; $j++) {
            $expectedPwd = md5($expectedPwd);
            if ($suppliedPassword === $expectedPwd) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load target user_id of user containing current transfer key
     */
    public function getUserIdByTransferKey(int $teamId, string $transferKey): ?int
    {
        $maRow = $this->mainDatabase->table(TransferKey::TABLE)
                ->where("transfer_key", $transferKey)
                ->where("team_id", $teamId)
                ->where("tk_dtm > DATE_SUB(now(), INTERVAL 20 SECOND)")
                ->fetch();
        return $maRow !== null ? $maRow->user_id : null;
    }
}
