<?php

namespace Tymy\Module\Multiaccount\Manager;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Multiaccount\Model\TransferKey;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\Team\Model\SimpleTeam;
use Tymy\Module\Team\Model\Team;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\User;

/**
 * Description of MultiaccountManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 06. 02. 2021
 */
class MultiaccountManager extends BaseManager
{
    private UserManager $userManager;
    private TeamManager $teamManager;

    public function __construct(ManagerFactory $managerFactory, UserManager $userManager, TeamManager $teamManager)
    {
        parent::__construct($managerFactory);
        $this->teamManager = $teamManager;
        $this->userManager = $userManager;
    }

    protected function getClassName(): string
    {
        return TransferKey::class;
    }

    protected function getScheme(): array
    {
        return []; //no entity considered here
    }

    public function canEdit($entity, $userId): bool
    {
        //todo
    }

    public function canRead($entity, $userId): bool
    {
        //todo
    }

    public function getAllowedReaders(BaseModel $record): array
    {
        return [];
    }

    /**
     * Generate new transfer key to jump to desired account and return it as object
     *
     * @param int $resourceId
     * @param int|null $subResourceId
     * @return BaseModel
     */
    public function read($resourceId, ?int $subResourceId = null): BaseModel
    {
        return $this->generateNewTk($resourceId);
    }

    public function create(array $data, $resourceId = null): BaseModel
    {
        //create new multi account
        $targetTeam = $this->teamManager->getBySysname($resourceId);

        if (!$targetTeam) {
            $this->respondNotFound();
        }

        //check for emptyness of login and password
        foreach (["login", "password"] as $input) {
            if (!array_key_exists($input, $data)) {
                $this->responder->E4013_MISSING_INPUT($input);
            }
            if (empty($data[$input])) {
                $this->responder->E4014_EMPTY_INPUT($input);
            }
        }

        $username = $data["login"];
        $password = $data["password"];

        $userId = $this->userManager->checkCredentials($targetTeam, $username, $password);
        if (!$userId) {
            $this->respondUnauthorized();
        }

        $accountId = $this->getAccountId();

        $existingAccountRow = $this->mainDatabase->table($this->getTable())
                        ->where("user_id", $userId)
                        ->where("team_id", $targetTeam->getId())->fetch();
        $existingAccountId = $existingAccountRow ? $existingAccountRow->account_id : null;

        if ($accountId && $existingAccountId == $accountId) {
            $this->responder->E400_BAD_REQUEST("Target team already exists in your multiaccount");
        }

        if ($existingAccountId) { //there is already some account id for this user on target team, so - merge these two account ids into one
            $this->mainDatabase->table($this->getTable())->where("account_id", $existingAccountId)->update([
                "account_id" => $accountId,
            ]);

            return $targetTeam;
        }

        if (empty($accountId)) {
            //get next accountId
            $accountId = $this->mainDatabase->table($this->getTable())->select("MAX(account_id) + 1 AS nextId")->fetch()->nextId;

            //create reord for this database
            $inserted = $this->mainDatabase->table($this->getTable())->insert([
                "account_id" => $accountId,
                "user_id" => $this->user->getId(),
                "team_id" => $this->teamManager->getTeam()->getId(),
            ]);
        }

        $inserted = $this->mainDatabase->table($this->getTable())->insert([
            "account_id" => $accountId,
            "user_id" => $userId,
            "team_id" => $targetTeam->getId(),
        ]);

        return $targetTeam;
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        //unused
    }

    public function delete($resourceId, ?int $subResourceId = null): int
    {
        $accountId = $this->getAccountId();

        if (!$accountId) {
            $this->respondNotFound();
        }

        //delete multi account
        $targetTeam = $this->teamManager->getBySysname($resourceId);

        if (!$targetTeam) {
            $this->responder->E4005_OBJECT_NOT_FOUND(Team::MODULE, $resourceId);
        }

        $this->mainDatabase->table($this->getTable())
                ->where("account_id", $accountId)
                ->where("team_id", $targetTeam->getId())
                ->delete();

        //if deleted record was the last one (which means the last record is just pointing to itself), delete all the accountId rows
        if (!empty($accountId) && $accountId) {
            $restRows = $this->mainDatabase->table($this->getTable())->where("account_id", $accountId)->count();

            if ($restRows === 1) {
                $this->mainDatabase->table($this->getTable())->where("account_id", $accountId)->delete();
            }
        }


        return $targetTeam->getId();
    }

    /**
     * Get list of polls, which currently logged user is allowed to vote in
     * @return SimpleTeam[]
     */
    public function getListUserAllowed()
    {
        $accountId = $this->getAccountId();

        if (!$accountId) {
            return [];
        }

        $teamRows = $this->mainDatabase->table(Team::TABLE)->where(":multi_accounts(team).account_id", $accountId)->fetchAll();

        $simpleTeams = [];
        foreach ($teamRows as $teamRow) {
            $simpleTeams[] = $this->teamManager->mapSimple($teamRow);
        }

        return $simpleTeams;
    }

    /**
     * Get account id, related to current team & user
     *
     * @return int|null
     */
    private function getAccountId(): ?int
    {
        $accountRow = $this->mainDatabase->table(TransferKey::TABLE)
                ->where("team_id", $this->teamManager->getTeam()->getId())
                ->where("user_id", $this->user->getId())
                ->fetch();

        return $accountRow ? $accountRow->account_id : null;
    }

    /**
     * Generate new transfer key to current user's multiaccount and stores it into database for future login
     *
     * @param string $targetTeamSysName
     * @return TransferKey
     */
    private function generateNewTk(string $targetTeamSysName): TransferKey
    {
        $targetTeam = $this->teamManager->getBySysname($targetTeamSysName);

        if (!$targetTeam) {
            $this->respondNotFound();
        }

        $accountId = $this->getAccountId();

        if (!$accountId) {
            $this->respondNotFound();
        }

        $targetUserId = $this->getTargetUserId($accountId, $targetTeam->getId());

        $newTk = sha1($accountId . rand(0, 100000));
        $this->mainDatabase->table(TransferKey::TABLE)
                ->where("account_id", $accountId)
                ->where("team_id", $targetTeam->getId())
                ->update([
                    "transfer_key" => $newTk,
                    "tk_dtm" => (new DateTime())->format(BaseModel::DATETIME_ENG_FORMAT)
        ]);

        return new TransferKey($newTk, $targetUserId);
    }

    /**
     * Get id of target user, based on accountId and team
     *
     * @param int $accountId
     * @param int $teamId
     * @return int
     */
    private function getTargetUserId(int $accountId, int $teamId): int
    {
        $row = $this->mainDatabase->table(TransferKey::TABLE)
                ->where("account_id", $accountId)
                ->where("team_id", $teamId)
                ->fetch();

        if (!$row) {
            $this->responder->E4005_OBJECT_NOT_FOUND(Team::MODULE, $teamId);
        }

        return $row->user_id;
    }
}
