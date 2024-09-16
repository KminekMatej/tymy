<?php

namespace Tymy\Module\User\Manager;

use Contributte\Translation\Translator;
use Exception;
use Nette\Application\AbortException;
use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\InvalidArgumentException;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Tymy\Module\Core\Exception\MissingInputException;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Helper\ArrayHelper;
use Tymy\Module\Core\Helper\CURLHelper;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Field;
use Tymy\Module\Core\Service\MailService;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\Team\Model\Team;
use Tymy\Module\User\Mapper\UserMapper;
use Tymy\Module\User\Model\Invitation;
use Tymy\Module\User\Model\SimpleUser;
use Tymy\Module\User\Model\User;

use function count;

use const TEAM_DIR;

/**
 * @extends BaseManager<User>
 */
class UserManager extends BaseManager
{
    public const HASH_LIMIT = 20;
    public const VALIDITYMIN = 10;
    public const MAX_PWD_REQUESTS = 3;
    private const FIELDS_PERSONAL = ["gender", "firstName", "lastName", "phone", "email", "birthDate", "nameDayMonth", "nameDayDay"];
    private const FIELDS_LOGIN = ["callName", "canEditCallName", "login", "password", "canLogin"];
    private const FIELDS_UI = ["language", "skin", "hideDiscDesc"];
    private const FIELDS_TEAMINFO = ["status", "jerseyNumber"];
    private const FIELDS_ADDRESS = ["street", "city", "zipCode"];
    private const TABS = [
        "PERSONAL" => self::FIELDS_PERSONAL,
        "LOGIN" => self::FIELDS_LOGIN,
        "UI" => self::FIELDS_UI,
        "TEAMINFO" => self::FIELDS_TEAMINFO,
        "ADDRESS" => self::FIELDS_ADDRESS,
    ];
    private ?User $userModel = null;
    private array $userFields;
    private array $userCounts;

    /** @var SimpleUser[] */
    private array $simpleUserCache = [];

    public function __construct(ManagerFactory $managerFactory, private MailService $mailService, private PermissionManager $permissionManager, private Translator $translator, private TeamManager $teamManager)
    {
        parent::__construct($managerFactory);
    }

    /**
     * Filter only players from input array.
     * @param User[]|int[] $users Either array of User's or array of user ids
     * @return User[]|int[] Only users having status = PLAYER
     */
    public function playersOnly(array $users): array
    {
        if (is_int($users[array_key_first($users)])) {    //this is an array of ids, not an array of Users
            $playerIds = $this->database->table($this->getTable())->where("status", User::STATUS_PLAYER)->fetchPairs(null, "id");
            return array_intersect($users, $playerIds);
        } else {
            return ArrayHelper::filter($users, "status", User::STATUS_PLAYER); /* @phpstan-ignore-line Dont know how to properly specify returning same type as on input */
        }
    }

    /**
     * Get simple user based on his id or null if hasnt been found
     */
    public function getSimpleUser(int $userId): ?SimpleUser
    {
        if (empty($this->simpleUserCache) || !array_key_exists($userId, $this->simpleUserCache)) {
            $allRows = $this->database->table(User::TABLE)->fetchAll();
            foreach ($allRows as $userRow) {
                $this->simpleUserCache[$userRow->id] = new SimpleUser($userRow->id, $userRow->user_name, $userRow->call_name, $this->getPictureUrl($userRow->id), (strtoupper($userRow->sex) == "FEMALE" ? "FEMALE" : "MALE"), $userRow->status, $userRow->email);
            }
        }

        return $this->simpleUserCache[$userId] ?? null;
    }

    /**
     * Get simple users by array of ids
     *
     * @param array|null $userIds Null returns all users
     * @return SimpleUser[]
     */
    public function getSimpleUsers(?array $userIds = null): array
    {
        $selector = $this->database->table(User::TABLE);
        if ($userIds) {
            $selector->where("id", $userIds);
        }

        $simples = [];
        foreach ($selector->fetchAll() as $userRow) {
            $userId = $userRow->id;
            if (array_key_exists($userId, $this->simpleUserCache)) {
                $simples[$userId] = $this->simpleUserCache[$userId];
            } else {
                $this->simpleUserCache[$userId] = new SimpleUser($userRow->id, $userRow->user_name, $userRow->call_name, $this->getPictureUrl($userRow->id), ($userRow->sex == "FEMALE" ? "FEMALE" : "MALE"), $userRow->status, $userRow->email);
                $simples[$userId] = $this->simpleUserCache[$userId];
            }
        }

        return $simples;
    }

    public function isAdmin(?int $userId = null): bool
    {
        if (empty($userId)) {
            return $this->user->isInRole(User::ROLE_SUPER);
        } else {
            return $this->database->table($this->getTable())->where("id", $userId)->where("roles LIKE %?%", User::ROLE_SUPER)->count("id") > 0;
        }
    }

    /**
     * Creates new User record and returns it on success
     * @param array $array Values to create
     * @return ActiveRow Created row
     * @throws Exception
     */
    public function createByArray(array $array): ActiveRow
    {
        if (isset($array["firstName"]) && strlen($array["firstName"]) > 20) {
            $array["firstName"] = substr($array["firstName"], 0, 20);
        }
        if (isset($array["lastName"]) && strlen($array["lastName"]) > 20) {
            $array["lastName"] = substr($array["lastName"], 0, 20);
        }
        if (isset($array["callName"]) && strlen($array["callName"]) > 30) {
            $array["callName"] = substr($array["callName"], 0, 30);
        }

        $array["login"] = strtoupper($array["login"]);

        $array["gender"] = isset($array["gender"]) && $array["gender"] == "FEMALE" ? "FEMALE" : "MALE";

        $array["password"] = $this->hashPassword($array["password"]);

        if (array_key_exists("roles", $array) && is_array($array["roles"])) {
            $array["roles"] = implode(",", $array["roles"]);
        }

        return parent::createByArray($array);
    }

    /**
     * Update users last read news to current timestamp
     */
    public function updateLastReadNews(int $userId): void
    {
        $this->database->table(User::TABLE)->where("id", $userId)->update(["last_read_news" => new DateTime()]);
    }

    public function updateByArray(int $id, array $array): int
    {
        parent::toBoolData($array, ["anonymousResults", "changeableVotes"]);

        $userModel = $this->getById($id);

        if ($userModel->getStatus() == User::STATUS_INIT && isset($array["status"]) && $array["status"] != User::STATUS_INIT) {
            //user status has been changed from INIT - need to notify him about the upgrade
            $array["canLogin"] = 1;
            $this->mailService->mailLoginApproved($userModel->getFullName(), $userModel->getEmail());
        }

        if (array_key_exists("roles", $array) && is_array($array["roles"])) {
            $array["roles"] = implode(",", $array["roles"]);
        }

        if (isset($array["password"]) && empty($array["password"])) {
            unset($array["password"]); //do not change password if input is empty
        }

        if (isset($array["password"])) { //changing password requested
            if (strlen($array["password"]) < 3) {
                $this->respondBadRequest($this->translator->translate("team.alerts.passwordTooShort"));
            }
            $array["password"] = $this->hashPassword($array["password"]);
        }

        return parent::updateByArray($id, $array);
    }

    /**
     * @return User|null */
    public function map(?IRow $row, bool $force = false): ?BaseModel
    {
        if ($row === null) {
            return null;
        }

        assert($row instanceof ActiveRow);

        $user = parent::map($row, $force);
        assert($user instanceof User);

        $user->setFullName($user->getFirstName() . " " . $user->getLastName());
        $user->setPictureUrl($this->getPictureUrl($row->id));
        $user->setIsNew($user->getCreatedAt() > new DateTime("- 14 days"));

        $user->setWebName($user->getId() . "-" . Strings::webalize($user->getDisplayName()));

        $this->addWarnings($user);

        return $user;
    }

    private function addWarnings(User $user): void
    {
        foreach ($this->teamManager->getTeam()->getRequiredFields() as $requiredField) {
            $getter = "get" . ucfirst($requiredField);
            if (empty($user->$getter())) {
                $user->addErrField($requiredField);
                continue;
            }

            //email validation secondary check
            if ($requiredField == "email" && filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL) === false) {
                $user->addErrField("email");
            }
        }

        if ($user->getStatus() == "INIT") {
            $user->addErrField("status");
        }
    }

    /**
     * Get url of users avatar.
     * Hint: appends file modification time, so the image gets dropped from browser cache after avatar upload
     */
    private function getPictureUrl(int $userId): string
    {
        $lastMod = 0;
        $filename = TEAM_DIR . "/user_pics/$userId.png";
        if (file_exists($filename)) {
            $lastMod = filemtime($filename) ?: 0;
        }

        return "/user_pics/$userId.png?lastmod=$lastMod";
    }

    /**
     * Get users based on their status
     *
     * @return User[]
     */
    public function getByStatus(string $status): array
    {
        return $this->mapAll($this->database->table($this->getTable())->where("status", $status)->fetchAll());
    }

    /**
     * Check if login is already taken
     */
    public function loginExists(string $login): bool
    {
        return $this->database->table($this->getTable())->select("id")->where("user_name", $login)->count("id") > 0;
    }

    /**
     * Load logins that already exists - for form validation
     * @return string[]
     */
    public function getExistingLoginsExcept(?string $exceptLogin = null): array
    {
        $selector = $this->database->table($this->getTable());
        if ($exceptLogin) {
            $selector->where("user_name != ?", $exceptLogin);
        }
        return $selector->fetchPairs(null, "user_name");
    }

    /**
     * Load e-mails that already exists - for form validation
     * @return string[]
     */
    public function getExistingEmails(): array
    {
        return $this->database->table(User::TABLE)->fetchPairs(null, "email");
    }

    /**
     * Check if user limit has been reached
     * @return bool
     */
    public function limitUsersReached(): bool
    {
        $limit = $this->teamManager->getTeam()->getMaxUsers();
        $currentCount = $this->getCounts($this->getList())["ACTIVE"];
        return $currentCount >= $limit;
    }

    /**
     * Check credentials of user in specified team and returns its id if exists and credentials are valid
     *
     * @return int|null User id if credentials match or null if they dont
     */
    public function checkCredentials(Team $team, string $username, string $password): ?int
    {
        $userId = CURLHelper::get("https://tymy.cz/api/check-credentials?" . http_build_query([
                    "username" => $username,
                    "teamId" => $team->getId(),
                    "password" => $password,
        ]));    //this request is accessible from localhost only

        return $userId && $userId !== "null" ? intval($userId) : null;
    }

    /**
     * Get userId by email
     */
    public function getIdByEmail(string $email): ?int
    {
        $row = $this->database->table(User::TABLE)->where("email", $email)->fetch();
        return $row !== null ? $row->id : null;
    }

    /**
     * Register user - create user record in INIT status
     */
    public function register(array $array, ?Invitation $invitation = null): ?User
    {
        $this->allowRegister($array);

        $array["status"] = "INIT";
        $array["callName"] = $array["login"];

        if ($invitation !== null) {
            $array["status"] = "PLAYER";
            $array["canLogin"] = true;
            $array["canEditCallName"] = true;
        }

        $createdRow = $this->createByArray($array);

        $registeredUser = $this->map($createdRow);
        assert($registeredUser instanceof User);

        $allAdmins = $this->getUsersWithPrivilege("SYS:USR_UPDATE");

        if ($invitation === null) { //send registration email only if this is blank registration from web, not from invitation
            foreach ($allAdmins as $admin) {
                assert($admin instanceof User);
                if (empty($admin->getEmail())) {  //skip admins without email
                    continue;
                }
                $this->mailService->mailUserRegistered($admin->getCallName(), $admin->getEmail(), $registeredUser->getLogin(), $registeredUser->getEmail(), $registeredUser->getFirstName(), $registeredUser->getLastName(), $array["note"] ?? null);
            }
        } else {    //mark invitation request as accepted. Cannot from invitationManager, since that would caus circullar reference
            $this->database->table(Invitation::TABLE)
                ->where("id", $invitation->getId())
                ->update(["user_id" => $registeredUser->getId()]);
        }

        return $registeredUser;
    }

    /**
     * Get user by its ID or null if not found
     * @param int $id
     * @param bool $force
     * @return User|null
     */
    public function getById(int $id, bool $force = false): ?User
    {
        $user = parent::getById($id, $force);
        return $user instanceof User ? $user : null;
    }

    /**
     * Function selects all users allowed on given permission
     * @param string $privilege String representation of privilege
     * @return Selection Selection to operate with
     */
    private function selectUsersByPrivilege(string $privilege): Selection
    {
        $privParts = explode(":", $privilege);
        $type = array_shift($privParts);
        $name = join(":", $privParts); //join by colon back in case some user permission would contain it

        $permission = $this->permissionManager->getByTypeName($type, $name);

        $usersSelector = $this->database->table($this->getTable());
        $conditions = [];
        $params = [];
        if (!empty($permission->getAllowedRoles())) {
            foreach ($permission->getAllowedRoles() as $allowedRole) {
                $conditions[] = "FIND_IN_SET(?, roles) > 0";
                $params[] = $allowedRole;
            }
            //administrator is allowed event if its not exactly specified
            $conditions[] = "FIND_IN_SET(?, roles) > 0";
            $params[] = User::ROLE_SUPER;
        }
        if (!empty($permission->getAllowedStatuses())) {
            $conditions[] = "status IN ?";
            $params[] = $permission->getAllowedStatuses();
        }
        if (!empty($permission->getAllowedUsers())) {
            $conditions[] = "id IN ?";
            $params[] = $permission->getAllowedUsers();
        }
        $usersSelector->where("(" . implode(") OR (", $conditions) . ")", ...$params);

        //add revokes
        if (!empty($permission->getRevokedRoles())) {
            foreach ($permission->getRevokedRoles() as $revokedRole) {
                $usersSelector->where("FIND_IN_SET(?, roles) = 0", $revokedRole);
            }
        }
        if (!empty($permission->getRevokedStatuses())) {
            $usersSelector->where("status NOT IN ?", $permission->getRevokedStatuses());
        }
        if (!empty($permission->getRevokedUsers())) {
            $usersSelector->where("id NOT IN ?", $permission->getRevokedUsers());
        }

        return $usersSelector;
    }

    /**
     * Load list of user ids, allowed to operate with given privilege
     * @return mixed[]
     */
    public function getUserIdsWithPrivilege(string $privilege): array
    {
        return $this->selectUsersByPrivilege($privilege)->fetchPairs("id", "id");
    }

    /**
     * Load list of user object, allowed to operate with given privilege
     * @return BaseModel[]
     */
    public function getUsersWithPrivilege(string $privilege): array
    {
        return $this->mapAll($this->selectUsersByPrivilege($privilege)->fetchAll());
    }

    public function getClassName(): string
    {
        return User::class;
    }

    /**
     * @return Field[]
     */
    public function getScheme(): array
    {
        return UserMapper::scheme();
    }

    public function canEdit($entity, $userId): bool
    {
        return true;
    }

    public function canRead($entity, $userId): bool
    {
        return true;
    }

    /**
     * @return mixed[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        return [];
    }

    /**
     * Hash password 1-20 times
     */
    private function hashPassword(string $password): string
    {
        $hash = md5($password);
        for ($index = 1; $index < (random_int(0, 1) * self::HASH_LIMIT); $index++) {// when password is being edited, save password hashed 1 - 20 times into database. Starting from and hashing in init makes sure that hash is made at least once
            $hash = md5($password);
        }
        return $hash; //TODO neccessary to update oldPassword to enable login from old gui
    }

    protected function allowCreate(?array &$data = null): void
    {
        if (!$this->user->isAllowed((string) $this->user->getId(), "SYS:USR_CREATE")) {
            $this->responder->E4003_CREATE_NOT_PERMITTED(User::MODULE);
        }

        $this->checkInputs($data);

        foreach (["password", "email"] as $neccessaryField) {
            if (!array_key_exists($neccessaryField, $data)) {
                $this->responder->E4013_MISSING_INPUT($neccessaryField);
            }
        }

        if (strlen($data["password"]) < 3) {
            $this->respondBadRequest($this->translator->translate("team.alerts.passwordTooShort"));
        }
        if (!preg_match(BaseModel::MAIL_REGEX, $data["email"])) {
            $this->respondBadRequest($this->translator->translate("team.alerts.emailFailure"));
        }
        if (strlen($data["login"]) < 3 || strlen($data["login"]) > 20) {
            $this->respondBadRequest($this->translator->translate("team.alerts.usernameFailure"));
        }
        if ($this->loginExists($data["login"])) {
            $this->respondBadRequest($this->translator->translate("team.alerts.loginExists"));
        }
        if ($this->limitUsersReached()) {
            $this->respondForbidden($this->translator->translate("team.alerts.userQuotaReached"));
        }
        if ($this->getIdByEmail($data["email"])) {
            $this->respondBadRequest($this->translator->translate("team.alerts.emailExists"));
        }
    }

    protected function allowDelete(?int $recordId): void
    {
        $this->userModel = $this->getById($recordId);
        if (empty($this->userModel)) {
            $this->respondNotFound();
        }

        if (!$this->user->isAllowed((string) $this->user->getId(), "SYS:USR_UPDATE")) {
            $this->responder->E4004_DELETE_NOT_PERMITTED(User::MODULE, $recordId);
        }
    }

    protected function allowRead(?int $recordId = null): void
    {
        //when user is logged in, everyone can read details about any other user
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        $this->userModel = $this->getById($recordId);

        if (empty($this->userModel)) {
            $this->respondNotFound();
        }

        //only administrators can change user roles
        if (isset($data["roles"]) && $data["roles"] !== $this->userModel->getRoles() && !$this->user->isAllowed((string) $this->user->getId(), "SYS:IS_ADMIN")) {
            $this->responder->E403_FORBIDDEN($this->translator->translate("team.alerts.changingRolesForbidden"));
        }

        $canEditFull = $this->user->isAllowed((string) $this->user->getId(), "SYS:USR_UPDATE");
        $editingMyself = $this->userModel->getId() === $this->user->getId();

        if (!$canEditFull && !$editingMyself) {
            $this->responder->E4002_EDIT_NOT_PERMITTED(User::MODULE, $recordId);
        }

        if (!$canEditFull) {
            //editing myself - canEditCallName, status, login and callName (when user cannot edit callName)
            if (isset($data["canEditCallName"]) && $data["canEditCallName"] !== $this->userModel->getCanEditCallName()) {
                $this->responder->E403_FORBIDDEN($this->translator->translate("team.alerts.changingCanEditCallnameForbidden"));
            }
            if (isset($data["status"]) && $data["status"] !== $this->userModel->getStatus()) {
                $this->responder->E403_FORBIDDEN($this->translator->translate("team.alerts.changingStatusForbidden"));
            }
            if (isset($data["login"]) && $data["login"] !== $this->userModel->getLogin()) {
                $this->responder->E403_FORBIDDEN($this->translator->translate("team.alerts.changingLoginForbidden"));
            }
            if (isset($data["callName"]) && $data["callName"] !== $this->userModel->getCallName() && !$this->userModel->getCanEditCallName()) {
                $this->responder->E403_FORBIDDEN($this->translator->translate("team.alerts.editingCallNameForbidden"));
            }
        }

        if (isset($data["email"])) {
            $userIdWithThatEmail = $this->getIdByEmail($data["email"]);
            if ($userIdWithThatEmail && $userIdWithThatEmail !== $this->userModel->getId()) { //changing mail to already existing one
                $this->responder->E403_FORBIDDEN($this->translator->translate("team.alerts.emailExists"));
            }
        }

        //changing user status from deleted?
        if ($this->userModel->getStatus() == "DELETED" && array_key_exists("status", $data) && $data["status"] !== "DELETED" && $this->limitUsersReached()) {
            $this->respondForbidden($this->translator->translate("team.alerts.userQuotaReached"));
        }

        //surge that user with ID 1 will always stay Admin
        if ($this->userModel->getId() == 1 && isset($data["roles"]) && !in_array(User::ROLE_SUPER, $data["roles"])) {
            $data["roles"][] = "SUPER";
        }
    }

    /**
     * Check if registration is allowed
     *
     * @throws InvalidArgumentException
     */
    private function allowRegister(array &$data): void
    {
        if (empty($data)) {
            throw new InvalidArgumentException("Invalid data");
        }

        $inits = $this->getByStatus("INIT");

        foreach (["password", "email"] as $neccessaryField) {
            if (!array_key_exists($neccessaryField, $data)) {
                throw new MissingInputException($neccessaryField);
            }
        }

        if ($this->loginExists($data["login"])) {
            throw new InvalidArgumentException($this->translator->translate("team.alerts.loginExists"));
        }
        if (count($inits) > 3) {
            throw new InvalidArgumentException($this->translator->translate("team.alerts.registrationsLimitReached"));
        }
        if (strlen($data["password"]) < 3) {
            throw new InvalidArgumentException($this->translator->translate("team.alerts.passwordTooShort"));
        }
        if (!preg_match(BaseModel::MAIL_REGEX, $data["email"])) {
            throw new InvalidArgumentException($this->translator->translate("team.alerts.emailFailure"));
        }
        if (strlen($data["login"]) < 3 || strlen($data["login"]) > 20) {
            throw new InvalidArgumentException($this->translator->translate("team.alerts.usernameFailure"));
        }
        if ($this->limitUsersReached()) {
            throw new InvalidArgumentException($this->translator->translate("team.alerts.userQuotaReached"));
        }
        if ($this->getIdByEmail($data["email"])) {
            throw new InvalidArgumentException($this->translator->translate("team.alerts.emailExists"));
        }
    }

    /**
     * @return User
     */
    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        parent::toBoolData($data, ["canEditCallName", "canLogin", "hideDiscDesc"]);

        $this->allowCreate($data);

        return $this->map($this->createByArray($data));
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->allowDelete($resourceId);

        if ($this->userModel == User::STATUS_INIT) {
            $this->mailService->mailLoginDenied($this->userModel->getFullName(), $this->userModel->getEmail());
        }

        return parent::deleteRecord($resourceId);
    }

    public function read(int $resourceId, ?int $subResourceId = null): User
    {
        $this->allowRead($resourceId);

        return $this->getById($resourceId);
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): User
    {
        parent::toBoolData($data, ["canEditCallName", "canLogin", "hideDiscDesc"]);

        $this->allowUpdate($resourceId, $data);

        $this->updateByArray($resourceId, $data);

        return $this->getById($resourceId);
    }

    /**
     * Get array of currently live SimpleUsers
     *
     * @return SimpleUser[]
     */
    public function getLiveUsers(): array
    {
        $liveUserIds = $this->database->table("live")->group("user_id")->where("time > NOW() - INTERVAL ? MINUTE", self::VALIDITYMIN)->fetchPairs("user_id", "user_id");

        return empty($liveUserIds) ? [] : $this->getSimpleUsers($liveUserIds);
    }

    /**
     * Checks prerequisities, generate reset code, store it into database and send informational mail to the resetting user
     * @param string $email
     * @param string $hostname
     * @param string $callbackUri
     * @return void
     */
    public function pwdLost(string $email, string $hostname, string $callbackUri): void
    {
        $userId = $this->getIdByEmail($email);
        if (empty($userId)) {
            $this->respondNotFound(User::MODULE);
        }

        $user = $this->getById($userId);

        if (!$user->getCanLogin() || !in_array($user->getStatus(), [User::STATUS_PLAYER, User::STATUS_MEMBER, User::STATUS_SICK])) {
            $this->respondBadRequest($this->translator->translate("common.alerts.pwdResetFailed"));
        }

        if ($this->pwdLostCount($userId) > self::MAX_PWD_REQUESTS) {
            $this->respondBadRequest($this->translator->translate("common.alerts.tooManyTries"));
        }

        $resetCode = substr(md5(random_int(0, mt_getrandmax())), 0, 20);

        $this->database->table(User::TABLE_PWD_RESET)->insert([
            "from_host" => $hostname,
            "requested" => new DateTime(),
            "reset_code" => $resetCode,
            "user_id" => $userId,
        ]);

        $this->mailService->mailPwdReset($user->getFullName(), $user->getEmail(), $callbackUri, $hostname, $resetCode);
    }

    /**
     * Check conditions, reset password and return the new password
     *
     * @return string New password
     */
    public function pwdReset(string $resetCode): string
    {
        $resetRow = $this->database->table(User::TABLE_PWD_RESET)
                ->where("reset_code", $resetCode)
                ->where("requested > NOW() - INTERVAL 1 HOUR")
                ->where("reseted", null)
                ->fetch();

        if (!$resetRow instanceof ActiveRow) {
            $this->respondBadRequest($this->translator->translate("common.alerts.invalidResetCode"));
        }
        $user = $this->getById($resetRow->user_id);
        if (!$user instanceof BaseModel) {
            $this->respondBadRequest($this->translator->translate("common.alerts.invalidResetCode"));
        }

        $newPwd = substr(md5($resetCode . random_int(0, 100000)), 0, 8);

        $this->database->table(User::TABLE)
                ->where("id", $user->getId())
                ->update([
                    "password" => $this->hashPassword($newPwd)
        ]);

        $this->database->table(User::TABLE_PWD_RESET)
                ->where("id", $resetRow->id)
                ->update(["reseted" => new DateTime()]);

        return $newPwd;
    }

    /**
     * Count password reset requests in last hour
     */
    private function pwdLostCount(int $userId): int
    {
        return $this->database->table(User::TABLE_PWD_RESET)
                        ->where("user_id", $userId)
                        ->where("reseted", null)
                        ->where("requested > NOW() - INTERVAL 1 HOUR")
                        ->count("id");
    }

    /**
     * Get counts of supplied users, based on (mostly) status criteria
     *
     * @param User[] $users
     * @return array in the form of ["ALL" => (int),"NEW" => (int),"PLAYER" => (int),"NEW:PLAYER" => (int),"MEMBER" => (int),"SICK" => (int),"DELETED" => (int),"INIT" => (int)]
     */
    public function getCounts(array $users): array
    {
        if (isset($this->userCounts)) {
            return $this->userCounts;
        }

        $this->userCounts = [
            "ALL" => 0,
            "ACTIVE" => 0,
            "NEW" => 0,
            "PLAYER" => 0,
            "NEW:PLAYER" => 0,
            "MEMBER" => 0,
            "SICK" => 0,
            "DELETED" => 0,
            "INIT" => 0,
        ];

        foreach ($users as $user) {
            assert($user instanceof User);
            $this->userCounts["ALL"]++;
            $this->userCounts[$user->getStatus()]++;
            if ($user->getStatus() !== "DELETED") {
                $this->userCounts["ACTIVE"]++;
            }

            if ($user->getIsNew()) {
                $this->userCounts["NEW"]++;
                if ($user->getStatus() == User::STATUS_PLAYER) {
                    $this->userCounts["NEW:PLAYER"]++;
                }
            }
        }

        return $this->userCounts;
    }

    /**
     * Get sum of all warnings of desired users
     *
     * @param User[] $users
     */
    public function getWarnings(array $users): int
    {
        $count = 0;
        foreach ($users as $user) {
            assert($user instanceof User);
            $count += $user->getWarnings();
        }

        return $count;
    }

    /**
     * Return array of all users, categorized by status and id
     * @return array in the form of [$status][$id] = $user
     */
    public function getByStatusAndId(): array
    {
        $users = $this->getList();
        $byTypeAndId = [];

        foreach ($users as $user) {
            assert($user instanceof User);
            if (!array_key_exists($user->getStatus(), $byTypeAndId)) {
                $byTypeAndId[$user->getStatus()] = [];
            }
            $byTypeAndId[$user->getStatus()][$user->getId()] = $user;
        }

        return $byTypeAndId;
    }

    /**
     * Return array of all users, categorized by status and id
     * @return array in the form of [$status][$id] = $user
     */
    public function getByIdWithTeam(): array
    {
        $userList = [$this->mockTeamUser()];

        foreach ($this->getList() as $user) {
            $userList[$user->getId()] = $user;
        }

        return $userList;
    }

    public function mockTeamUser()
    {
        return (new User())->setId(0)->setCallName("*** TEAM ***");
    }

    /**
     * Get array of user fields
     * @return mixed[]|array<string, array<string, string>>
     */
    public function getAllFields(): array
    {
        if (isset($this->userFields)) {
            return $this->userFields;
        }

        $this->userFields = [
            "PERSONAL" => [],
            "LOGIN" => [],
            "UI" => [],
            "TEAMINFO" => [],
            "ADDRESS" => [],
            "ALL" => []
        ];
        foreach (self::TABS as $tabName => $fields) {
            foreach ($fields as $field) {
                $caption = $this->translator->translate("team." . $field);
                $this->userFields[$tabName][$field] = $caption;
                $this->userFields["ALL"][$field] = $caption;
            }
        }
        return $this->userFields;
    }

    /**
     * Adds user to live table or update time if user is already there.
     * Also deletes old records
     * @param int $userId
     * @return void
     */
    public function setUserLive(int $userId): void
    {
        $this->database->query("INSERT INTO `live`(`user_id`) VALUES (?) ON DUPLICATE KEY UPDATE `time` = CURRENT_TIMESTAMP()", $userId);
        $this->database->query("DELETE FROM `live` WHERE `time` < NOW() - INTERVAL ? MINUTE", self::VALIDITYMIN);
    }
}
