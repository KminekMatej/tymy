<?php

namespace Tymy\Module\User\Manager;

use Exception;
use Kdyby\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Http\Request;
use Nette\InvalidArgumentException;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Tymy\Module\Authentication\Manager\AuthenticationManager;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Service\MailService;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\Team\Model\Team;
use Tymy\Module\User\Mapper\UserMapper;
use Tymy\Module\User\Model\SimpleUser;
use Tymy\Module\User\Model\User;

/**
 * Description of UserManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4. 8. 2020
 */
class UserManager extends BaseManager
{

    public const HASH_LIMIT = 20;
    public const VALIDITYMIN = 10;
    public const MAX_PWD_REQUESTS = 3;
    private const FIELDS_PERSONAL = ["gender", "firstName", "lastName", "phone", "email", "birthDate", "nameDayMonth", "nameDayDay", "language"];
    private const FIELDS_LOGIN = ["callName", "canEditCallName", "login", "password", "canLogin"];
    private const FIELDS_TEAMINFO = ["status", "jerseyNumber"];
    private const FIELDS_ADDRESS = ["street", "city", "zipCode"];

    private MailService $mailService;
    private Request $request;
    private PermissionManager $permissionManager;
    private AuthenticationManager $authenticationManager;
    private TeamManager $teamManager;
    private Translator $translator;
    private ?User $userModel = null;
    private array $userFields;
    private array $userCounts;

    /** @var SimpleUser[] */
    private array $simpleUserCache = [];

    public function __construct(ManagerFactory $managerFactory, MailService $mailService, PermissionManager $permissionManager, AuthenticationManager $authenticationManager, Request $request, Translator $translator, TeamManager $teamManager)
    {
        parent::__construct($managerFactory);
        $this->mailService = $mailService;
        $this->permissionManager = $permissionManager;
        $this->authenticationManager = $authenticationManager;
        $this->translator = $translator;
        $this->request = $request;
        $this->teamManager = $teamManager;
    }

    /**
     * Get simple user based on his id
     *
     * @param int $userId
     * @return SimpleUser
     */
    public function getSimpleUser(int $userId): SimpleUser
    {
        if (!array_key_exists($userId, $this->simpleUserCache)) {
            $userRow = $this->database->table($this->getTable())->get($userId);
            $this->simpleUserCache[$userId] = new SimpleUser($userRow->id, $userRow->user_name, $userRow->call_name, $this->getPictureUrl($userRow->id), ($userRow->sex == "FEMALE" ? "FEMALE" : "MALE"), $userRow->status);
        }

        return $this->simpleUserCache[$userId];
    }

    /**
     * Get simple users by array of ids
     *
     * @param array $userIds
     * @return SimpleUser[]
     */
    public function getSimpleUsers(array $userIds): array
    {
        $userRows = $this->database->table($this->getTable())->where("id", $userIds)->fetchAll();

        $simples = [];
        foreach ($userRows as $userRow) {
            $userId = $userRow->id;
            if (array_key_exists($userId, $this->simpleUserCache)) {
                $simples[] = $this->simpleUserCache[$userId];
            } else {
                $this->simpleUserCache[$userId] = new SimpleUser($userRow->id, $userRow->user_name, $userRow->call_name, $this->getPictureUrl($userRow->id), ($userRow->sex == "FEMALE" ? "FEMALE" : "MALE"), $userRow->status);
                $simples[] = $this->simpleUserCache[$userId];
            }
        }

        return $simples;
    }

    public function isAdmin(int $userId)
    {
        return $this->database->table($this->getTable())->where("id", $userId)->where("roles LIKE %?%", UserEntity::ROLE_SUPER)->count("id") > 0;
    }

    /**
     * Creates new User record and returns it on success
     * @param array $array Values to create
     * @return ActiveRow Created row
     * @throws Exception
     */
    public function createByArray($array)
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
        $array["jerseyNumber"] = isset($array["jerseyNumber"]) ? $array["jerseyNumber"] : "";

        $array["password"] = $this->hashPassword($array["password"]);

        if (array_key_exists("roles", $array) && is_array($array["roles"])) {
            $array["roles"] = join(",", $array["roles"]);
        }

        $createdRow = parent::createByArray($array);

        $this->saveEmail($createdRow->id, $array["email"]);

        return $createdRow;
    }

    /**
     * Function to save email for given user.
     * @param int $userId
     * @param string $email
     * @param string $type Default DEF
     * @throws AbortException
     */
    private function saveEmail(int $userId, string $email, string $type = "DEF"): void
    {
        $updated = $this->database->table(User::TABLE_MAILS)->where("user_id", $userId)->where("type", $type)->update(["email" => $email]);

        if (!$updated) {
            $created = $this->database->table(User::TABLE_MAILS)->insert(
                    [
                        "user_id" => $userId,
                        "type" => $type,
                        "email" => $email,
                    ]
            );

            if (!$created) {
                $this->responder->E4009_CREATE_FAILED(User::MODULE);
            }
        }
    }

    /**
     * Update users last read news to current timestamp
     * 
     * @param int $userId
     * @return void
     */
    public function updateLastReadNews(int $userId): void
    {
        $this->database->query("UPDATE `users` SET `last_read_news`=? WHERE (`id` = ?)", new DateTime(), $userId);
    }

    public function updateByArray(int $id, array $array)
    {
        /* @var $userModel User */
        $userModel = $this->getById($id);

        if ($userModel->getStatus() == User::STATUS_INIT && isset($array["status"]) && $array["status"] != User::STATUS_INIT) {
            if ($array["status"] == User::STATUS_DELETED) {
                $this->mailService->mailLoginDenied($userModel->getFullName(), $userModel->getEmail());
            } else {
                //user status has been changed from INIT - need to notify him about the upgrade
                $array["canLogin"] = 1;
                $this->mailService->mailLoginApproved($userModel->getFullName(), $userModel->getEmail());
            }
        }

        if (array_key_exists("email", $array) && !empty($array["email"])) {
            $this->saveEmail($id, $array["email"]);
        }

        if (array_key_exists("roles", $array) && is_array($array["roles"])) {
            $array["roles"] = join(",", $array["roles"]);
        }

        if (array_key_exists("password", $array)) {
            $array["password"] = $this->hashPassword($array["password"]);
        }

        return parent::updateByArray($id, $array);
    }

    /** @return User */
    public function map(?IRow $row, bool $force = false): ?BaseModel
    {
        /* @var $user User */
        $user = parent::map($row, $force);

        if (!$row) {
            return null;
        }

        $user->setFullName($user->getFirstName() . " " . $user->getLastName());
        $user->setPictureUrl($this->getPictureUrl($row->id));
        $user->setIsNew($user->getCreatedAt() > new DateTime("- 14 days"));

        $emailRow = $row->related(User::TABLE_MAILS, "user_id")->where("type", "DEF")->fetch();

        $user->setEmail($emailRow ? $emailRow["email"] : null);
        
        $user->setWebName($user->getId() . "-" . Strings::webalize($user->getDisplayName()));
        
        $this->addWarnings($user);

        return $user;
    }
    
    private function addWarnings(User $user)
    {
        foreach ($this->supplier->getRequiredFields() as $requiredField) {
            $getter = "get" . ucfirst($requiredField);
            if (empty($user->$getter())) {
                $user->addErrField($requiredField);
                continue;
            }

            //email validation secondary check
            if($requiredField == "email" && filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL) === FALSE){
                $user->addErrField("email");
            }
        }
        
        if ($user->getStatus() == "INIT") {
            $user->addErrField("status");
        }
    }

    private function getPictureUrl(int $userId)
    {
        return "/user_pics/$userId.png";
    }

    /**
     * Get users based on their status
     *
     * @param string $status
     * @return User[]
     */
    public function getByStatus(string $status)
    {
        return $this->mapAll($this->database->table($this->getTable())->where("status", $status)->fetchAll());
    }

    /**
     * Check if login is already taken
     *
     * @param string $login
     * @return bool
     */
    public function loginExists(string $login)
    {
        return $this->database->table($this->getTable())->select("id")->where("user_name", $login)->count("id") > 0;
    }

    /**
     * Check if user limit has been reached
     *
     * @param string $login
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
     * @param Team $team
     * @param string $username
     * @param string $password
     * @return int|null User id if credentials match or null if they dont
     */
    public function checkCredentials(Team $team, string $username, string $password): ?int
    {
        $dbName = $team->getDbName();
        $userRow = $this->database->query("SELECT * FROM $dbName.{$this->getTable()} WHERE user_name = ?", $username)->fetch();

        if (!$userRow) {
            return false;
        }

        $expectedPassword = $userRow->password;

        return $this->authenticationManager->passwordMatch($password, $expectedPassword) ? $userRow->id : null;
    }

    /**
     * Get userId by email
     *
     * @param string $email
     * @return bool
     */
    public function getIdByEmail(string $email): ?int
    {
        $row = $this->database->table(User::TABLE_MAILS)->where("email", $email)->fetch();
        return $row ? $row->user_id : null;
    }

    /**
     * Register user - create user record in INIT status
     * @param array $array
     * @return User
     */
    public function register(array $array): User
    {
        \Tracy\Debugger::barDump($array);
        $this->allowRegister($array);

        $array["status"] = "INIT";
        $array["callName"] = $array["login"];
        $array["canLogin"] = false; //user cannot login after registration

        $createdRow = $this->createByArray($array);

        /* @var $registeredUser User */
        $registeredUser = $this->map($createdRow);

        $allAdmins = $this->getUsersWithPrivilege(Privilege::SYS("USR_UPDATE"));

        foreach ($allAdmins as $admin) {
            /* @var $admin User */
            $this->mailService->mailUserRegistered($admin->getCallName(), $admin->getEmail(), $registeredUser->getLogin(), $registeredUser->getEmail(), $registeredUser->getFirstName(), $registeredUser->getLastName(), $array["note"] ?? null);
        }

        return $registeredUser;
    }

    /**
     * Function selects all users allowed on given permission
     * @param Privilege $privilege
     * @return Selection Selection to operate with
     */
    private function selectUsersByPrivilege(Privilege $privilege): Selection
    {
        $permission = $this->permissionManager->getByTypeName($privilege->getType(), $privilege->getName());

        $usersSelector = $this->database->table($this->getTable());
        $conditions = [];
        $params = [];
        if (!empty($permission->getAllowedRoles())) {
            foreach ($permission->getAllowedRoles() as $allowedRole) {
                $conditions[] = "roles LIKE ?";
                $params[] = "%$allowedRole%";
            }
        }
        if (!empty($permission->getAllowedStatuses())) {
            $conditions[] = "status IN ?";
            $params[] = $permission->getAllowedStatuses();
        }
        if (!empty($permission->getAllowedUsers())) {
            $conditions[] = "id IN ?";
            $params[] = $permission->getAllowedUsers();
        }
        $usersSelector->where("(" . join(") OR (", $conditions) . ")", ...$params);

        //add revokes
        if (!empty($permission->getRevokedRoles())) {
            foreach ($permission->getRevokedRoles() as $revokedRole) {
                $usersSelector->where("roles NOT LIKE ?", "%$revokedRole%");
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
     * @param Privilege $privilege
     * @return int[]|null
     */
    public function getUserIdsWithPrivilege(Privilege $privilege): array
    {
        return $this->selectUsersByPrivilege($privilege)->fetchPairs("id", "id");
    }

    /**
     * Load list of user object, allowed to operate with given privilege
     * @param Privilege $privilege
     * @return User[]|null
     */
    public function getUsersWithPrivilege(Privilege $privilege): array
    {
        return $this->mapAll($this->selectUsersByPrivilege($privilege)->fetchAll());
    }

    protected function getClassName(): string
    {
        return User::class;
    }

    protected function getScheme(): array
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

    public function getAllowedReaders(BaseModel $record): array
    {
        return [];
    }

    /**
     * Hash password 1-20 times
     * @param string $password
     * @return string
     */
    private function hashPassword(string $password): string
    {
        $hash = md5($password);
        for ($index = 1; $index < (rand(0, 1) * self::HASH_LIMIT); $index++) {// when password is being edited, save password hashed 1 - 20 times into database. Starting from and hashing in init makes sure that hash is made at least once
            $hash = md5($password);
        }
        return $hash; //TODO neccessary to update oldPassword to enable login from old gui
    }

    protected function allowCreate(?array &$data = null): void
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("USR_CREATE"))) {
            $this->responder->E4003_CREATE_NOT_PERMITTED(User::MODULE);
        }

        $this->checkInputs($data);

        foreach (["password", "email"] as $neccessaryField) {
            if (!array_key_exists($neccessaryField, $data)) {
                $this->responder->E4013_MISSING_INPUT($neccessaryField);
            }
        }

        if (strlen($data["password"]) < 3) {
            $this->respondBadRequest("Password failure");
        }
        if (!preg_match(BaseModel::MAIL_REGEX, $data["email"])) {
            $this->respondBadRequest("E-mail failure");
        }
        if (strlen($data["login"]) < 3 || strlen($data["login"]) > 20) {
            $this->respondBadRequest("Username failure");
        }
        if ($this->loginExists($data["login"])) {
            $this->respondBadRequest("Username taken");
        }
        if ($this->limitUsersReached()) {
            $this->respondForbidden("User quota limit reached");
        }
        if ($this->getIdByEmail($data["email"])) {
            $this->respondBadRequest("E-mail taken");
        }
    }

    protected function allowDelete(?int $recordId): void
    {
        $this->respondNotAllowed();
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

        $canEditFull = $this->user->isAllowed($this->user->getId(), Privilege::SYS("USR_UPDATE"));
        $editingMyself = $this->userModel->getId() === $this->user->getId();

        if (!$canEditFull && !$editingMyself) {
            $this->responder->E4002_EDIT_NOT_PERMITTED(User::MODULE, $recordId);
        }

        if (!$canEditFull) {
            //editing myself - cannot edit roles, canEditCallName, status, login and callName (when user cannot edit callName)
            if (isset($data["roles"]) && $data["roles"] !== $this->userModel->getRoles()) {
                $this->responder->E4002_EDIT_NOT_PERMITTED(User::MODULE, $recordId);
            }
            if (isset($data["canEditCallName"]) && $data["canEditCallName"] !== $this->userModel->getCanEditCallName()) {
                $this->responder->E4002_EDIT_NOT_PERMITTED(User::MODULE, $recordId);
            }
            if (isset($data["status"]) && $data["status"] !== $this->userModel->getStatus()) {
                $this->responder->E4002_EDIT_NOT_PERMITTED(User::MODULE, $recordId);
            }
            if (isset($data["login"]) && $data["login"] !== $this->userModel->getLogin()) {
                $this->responder->E4002_EDIT_NOT_PERMITTED(User::MODULE, $recordId);
            }
            if (isset($data["callName"]) && !$this->userModel->getCanEditCallName()) {
                $this->responder->E4002_EDIT_NOT_PERMITTED(User::MODULE, $recordId);
            }
        }

        if (array_key_exists("email", $data) && $this->getIdByEmail($data["email"]) !== $this->userModel->getId()) {
            //changing mail to already existing one
            $this->responder->E4002_EDIT_NOT_PERMITTED(User::MODULE, $recordId);
        }

        //changing user status from deleted?
        if ($this->userModel->getStatus() == "DELETED" && array_key_exists("status", $data) && $data["status"] !== "DELETED" && $this->limitUsersReached()) {
            $this->respondForbidden("User quota limit reached");
        }

        //surge that user with ID 1 will always stay Admin
        if ($this->userModel->getId() == 1 && isset($data["roles"]) && !in_array(User::ROLE_SUPER, $data["roles"])) {
            $data["roles"][] = "SUPER";
        }
    }

    /**
     * Check if registration is allowed
     * 
     * @param array $data
     * @return void
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
            throw new InvalidArgumentException("Username taken");
        }

        if (count($inits) > 3) {
            throw new InvalidArgumentException("Registrations limit reached");
        }

        if (strlen($data["password"]) < 3) {
            throw new InvalidArgumentException("Password failure");
        }
        if (!preg_match(BaseModel::MAIL_REGEX, $data["email"])) {
            throw new InvalidArgumentException("E-mail failure");
        }
        if (strlen($data["login"]) < 3 || strlen($data["login"]) > 20) {
            throw new InvalidArgumentException("Username failure");
        }
        if ($this->loginExists($data["login"])) {
            throw new InvalidArgumentException("Username taken");
        }
        if ($this->limitUsersReached()) {
            throw new InvalidArgumentException("User quota limit reached");
        }
        if ($this->getIdByEmail($data["email"])) {
            throw new InvalidArgumentException("E-mail taken");
        }
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $this->allowCreate($data);

        return $this->map($this->createByArray($data));
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->allowDelete($resourceId);

        return parent::deleteRecord($resourceId);
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowRead($resourceId);

        return $this->getById($resourceId);
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
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
     * 
     * @param int $userId
     * @param string $email
     * @param string $hostname
     * @param string $callbackUri
     */
    public function pwdLost(string $email, string $hostname, string $callbackUri)
    {
        $userId = $this->getIdByEmail($email);
        if (empty($userId)) {
            $this->respondNotFound();
        }

        /* @var $user User */
        $user = $this->getById($userId);

        if (!$user->getCanLogin() || !in_array($user->getStatus(), [User::STATUS_PLAYER, User::STATUS_MEMBER, User::STATUS_SICK])) {
            $this->respondBadRequest("Password reset failed");
        }

        if ($this->pwdLostCount($userId) > self::MAX_PWD_REQUESTS) {
            $this->respondBadRequest("Too many tries");
        }

        $resetCode = substr(md5(rand()), 0, 20);

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
     * @param string $resetCode
     * @return string
     */
    public function pwdReset(string $resetCode): string
    {
        $resetRow = $this->database->table(User::TABLE_PWD_RESET)
                ->where("reset_code", $resetCode)
                ->where("requested > NOW() - INTERVAL 1 HOUR")
                ->where("reseted", null)
                ->fetch();
        
        if (!$resetRow) {
            $this->respondBadRequest("Invalid reset code");
        }
        $user = $this->getById($resetRow->user_id);
        if (!$user) {
            $this->respondBadRequest("Invalid user");
        }

        $newPwd = substr(md5($resetCode . rand(0, 100000)), 0, 8);

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
     * 
     * @param int $userId
     * @return int
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
            /* @var $user User */
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
     * @return int
     */
    public function getWarnings(array $users): int
    {
        $count = 0;
        foreach ($users as $user) {
            /* @var $user User */
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
            /* @var $user User */
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
     * 
     * @return array
     */
    public function getAllFields(): array
    {
        if (isset($this->userFields)) {
            return $this->userFields;
        }

        $this->userFields = [
            "PERSONAL" => [],
            "LOGIN" => [],
            "TEAMINFO" => [],
            "ADDRESS" => [],
            "ALL" => []
        ];
        foreach (self::FIELDS_PERSONAL as $field) {
            $caption = $this->translator->translate("team." . $field);
            $this->userFields["PERSONAL"][$field] = $caption;
            $this->userFields["ALL"][$field] = $caption;
        }
        foreach (self::FIELDS_LOGIN as $field) {
            $caption = $this->translator->translate("team." . $field);
            $this->userFields["LOGIN"][$field] = $this->translator->translate("team." . $field);
            $this->userFields["ALL"][$field] = $caption;
        }
        foreach (self::FIELDS_TEAMINFO as $field) {
            $caption = $this->translator->translate("team." . $field);
            $this->userFields["TEAMINFO"][$field] = $this->translator->translate("team." . $field);
            $this->userFields["ALL"][$field] = $caption;
        }
        foreach (self::FIELDS_ADDRESS as $field) {
            $caption = $this->translator->translate("team." . $field);
            $this->userFields["ADDRESS"][$field] = $this->translator->translate("team." . $field);
            $this->userFields["ALL"][$field] = $caption;
        }
        return $this->userFields;
    }
}
