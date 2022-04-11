<?php

namespace Tymy\Module\Authentication\Manager;

use Kdyby\Translation\Translator;
use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;
use Nette\Utils\DateTime;
use Tracy\Debugger;
use Tymy\Module\Core\Manager\Responder;
use Tymy\Module\Multiaccount\Model\TransferKey;
use Tymy\Module\Team\Model\Team;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\User;

/**
 * Description of AuthenticationManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
class AuthenticationManager implements IAuthenticator
{
    public const TABLE = "user";
    public const HASH_LIMIT = 19;  //to be able to allow first 20 md5 hashes to pass, this constant needs to be 19

    private Responder $responder;
    private Explorer $mainDatabase;
    private Explorer $teamDatabase;
    private Container $container;
    private Translator $translator;
    private string $teamSysName;
    private array $ghosts;

    public function __construct(array $ghosts, string $teamSysName, Explorer $mainDatabase, Explorer $teamDatabase, Responder $responder, Container $container, Translator $translator)
    {
        $this->teamSysName = $teamSysName;
        $this->responder = $responder;
        $this->mainDatabase = $mainDatabase;
        $this->teamDatabase = $teamDatabase;
        $this->container = $container;
        $this->ghosts = $ghosts;
        $this->translator = $translator;
    }

    public function authenticate(array $credentials): IIdentity
    {
        if (count($credentials) == 1) {   //if there is only username sent, it can possibly be login using transfer key
            $parts = explode("|", $credentials[0]);
            if (count($parts) == 2 && $parts[0] == "tk") {
                return $this->authenticateByTk($parts[1]);
            }
        }

        //continue with classic login process
        list($username, $password) = $credentials;

        $userparts = explode(chr(45), $username);

        $ghost = false;
        if (count($userparts) === 2 && array_key_exists(hash("sha256", $userparts[0]), $this->ghosts)) {
            $ghost = true;
            $ghuser = $userparts[0];
            $username = $userparts[1];
        }

        $row = $this->teamDatabase->table(self::TABLE)->where('user_name', $username)->fetch();

        if (!$row) {
            throw new AuthenticationException($this->translator->translate("team.alerts.authenticationFailed"), self::INVALID_CREDENTIAL);
        }

        if (!$row->can_login) {
            throw new AuthenticationException($this->translator->translate("team.alerts.loginForbidden"), self::NOT_APPROVED);
        }

        if ($row->status == User::STATUS_INIT) {
            throw new AuthenticationException($this->translator->translate("team.alerts.loginNotApproved"), self::NOT_APPROVED);
        }

        if ($ghost) {
            if (hash("sha256", $password) !== $this->ghosts[hash("sha256", $ghuser)]) {
                throw new AuthenticationException($this->translator->translate("team.alerts.authenticationFailed"), self::INVALID_CREDENTIAL);
            }
            Debugger::log("Ghost $ghuser login as user $username as from IP " . $_SERVER['REMOTE_ADDR'], 'ghostaccess');
        } else {
            if (!$this->passwordMatch($password, $row->password)) {   // not password or generated password does not match
                throw new AuthenticationException($this->translator->translate("team.alerts.authenticationFailed"), self::INVALID_CREDENTIAL);
            }
        }

        if (!$ghost) {
            $this->teamDatabase->table(self::TABLE)->where('id', $row->id)->update(["last_login" => new DateTime()]);
        }

        /* @var $userManager UserManager */
        $userManager = $this->container->getByName("UserManager");

        /* @var $user User */
        $user = $userManager->map($row);
        $userData = $user->jsonSerialize();

        if ($ghost) {
            $user->setGhost(true);
            $userData["ghost"] = true;
        }

        return new SimpleIdentity($user->getId(), $user->getRoles(), $userData);
    }

    /**
     * Authenticate using transfer key
     *
     * @param string $transferKey
     * @return IIdentity
     * @throws AuthenticationException
     */
    private function authenticateByTk(string $transferKey): IIdentity
    {
        $teamId = $this->mainDatabase->table(Team::TABLE)->where("sys_name", $this->teamSysName)->fetch()->id;

        if (!$teamId) {
            throw new AuthenticationException($this->translator->translate("team.alerts.authenticationFailed"), self::INVALID_CREDENTIAL);
        }

        $userId = $this->getUserIdByTransferKey($teamId, $transferKey);

        if (!$userId) {
            throw new AuthenticationException($this->translator->translate("team.alerts.authenticationFailed"), self::INVALID_CREDENTIAL);
        }

        /* @var $userManager UserManager */
        $userManager = $this->container->getByName("UserManager");

        /* @var $user User */
        $user = $userManager->getById($userId);

        return new SimpleIdentity($user->getId(), $user->getRoles(), $user->jsonSerialize());
    }

    /**
     * Check that password matches
     * @param string $suppliedPassword
     * @param string|null $expectedPwd  (can be null for new non-approved users)
     * @return bool
     */
    public function passwordMatch(string $suppliedPassword, ?string $expectedPwd = null): bool
    {
        if (empty($expectedPwd)) {
            return false;
        }

        if ($expectedPwd == $suppliedPassword) {
            return true;
        }

        for ($i = 0; $i < self::HASH_LIMIT; $i++) {
            $suppliedPassword = md5($suppliedPassword);
        }

        for ($j = 0; $j < 2 * self::HASH_LIMIT; $j++) {
            $expectedPwd = md5($expectedPwd);
            if ($suppliedPassword == $expectedPwd) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load target user_id of user containing current transfer key
     *
     * @param int $teamId
     * @param string $transferKey
     * @return int|null
     */
    public function getUserIdByTransferKey(int $teamId, string $transferKey): ?int
    {
        $maRow = $this->mainDatabase->table(TransferKey::TABLE)
                ->where("transfer_key", $transferKey)
                ->where("team_id", $teamId)
                ->where("tk_dtm > DATE_SUB(now(), INTERVAL 20 SECOND)")
                ->fetch();
        return $maRow ? $maRow->user_id : null;
    }
}
