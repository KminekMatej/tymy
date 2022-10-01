<?php

namespace Tymy\Module\Multiaccount\Manager;

use Kdyby\Translation\Translator;
use Nette\NotImplementedException;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Multiaccount\Model\TransferKey;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\Team\Model\SimpleTeam;
use Tymy\Module\Team\Model\Team;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of MultiaccountManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 06. 02. 2021
 */
class MultiaccountManager extends BaseManager
{
    public function __construct(ManagerFactory $managerFactory, private UserManager $userManager, private TeamManager $teamManager, private Translator $translator)
    {
        parent::__construct($managerFactory);
        $this->database = $this->mainDatabase;
        $this->idCol = null;    //there is no simple primary key column in database - so avoid errors from BaseManager
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
        return false;   //there is actually no entity
    }

    public function canRead($entity, $userId): bool
    {
        return false;   //there is actually no entity
    }

    public function getAllowedReaders(BaseModel $record): array
    {
        return [];
    }

    /**
     * Generate new transfer key to jump to desired account and return it as object
     *
     * @param int $resourceId
     * @return TransferKey
     */
    public function read($resourceId, ?int $subResourceId = null): BaseModel
    {
        return $this->generateNewTk($resourceId);
    }

    public function create(array $data, $resourceId = null): BaseModel
    {
        //create new multi account
        $targetTeam = $this->teamManager->getBySysname($resourceId);
        $sourceTeam = $this->teamManager->getTeam();
        $sourceUserId = $this->user->getId();

        if (!$targetTeam instanceof \Tymy\Module\Team\Model\Team) {
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
            $this->respondUnauthorized($this->translator->translate("team.alerts.authenticationFailed"));
        }

        $sourceAccountId = $this->getAccountId();

        $existingAccountRow = $this->mainDatabase->table($this->getTable())
            ->where("user_id", $userId)
            ->where("team_id", $targetTeam->getId())
            ->fetch();

        $targetAccountId = $existingAccountRow !== null ? $existingAccountRow->account_id : null;

        if ($sourceAccountId && $targetAccountId == $sourceAccountId) {
            $this->responder->E400_BAD_REQUEST($this->translator->translate("team.alerts.targetTeamExists"));
        }

        //four scenarios can happen now:

        if ($targetAccountId && !$sourceAccountId) {  //1) target team already has account, but source team doesnt (add source team id into target team account)
            $this->addTeamUnderAccount($sourceTeam->getId(), $sourceUserId, $targetAccountId);
        } elseif (!$targetAccountId && $sourceAccountId) { //2) target team doesnt have account, but source team does (add target team into existing account id)
            $this->addTeamUnderAccount($targetTeam->getId(), $userId, $sourceAccountId);
        } elseif (!$targetAccountId && !$sourceAccountId) {    //3) no multiaccount exists (CREATE NEW)
            $accountId = $this->addTeamUnderAccount($sourceTeam->getId(), $sourceUserId);
            $this->addTeamUnderAccount($targetTeam->getId(), $userId, $accountId);
        } else { //4) both of them already exists (MERGE strategy)
            $this->mainDatabase->table($this->getTable())->where("account_id", $targetAccountId)->update([
                "account_id" => $sourceAccountId,
            ]);
        }

        return $targetTeam;
    }

    /**
     * Add team underneath account id
     */
    private function addTeamUnderAccount(int $teamId, int $userId, ?int $accountId = null): int
    {
        if (!$accountId) {
            $accountId = $this->mainDatabase->table($this->getTable())->select("MAX(account_id) + 1 AS nextId")->fetch()->nextId;
        }

        $this->mainDatabase->table($this->getTable())->insert([
            "account_id" => $accountId,
            "user_id" => $userId,
            "team_id" => $teamId,
        ]);

        return $accountId;
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new NotImplementedException("Not implemented yet");
    }

    public function delete($resourceId, ?int $subResourceId = null): int
    {
        $accountId = $this->getAccountId();

        if (!$accountId) {
            $this->respondNotFound();
        }

        //delete multi account
        $targetTeam = $this->teamManager->getBySysname($resourceId);

        if (!$targetTeam instanceof \Tymy\Module\Team\Model\Team) {
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
     * Get list of users multiaccounts
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
     */
    private function getAccountId(): ?int
    {
        $accountRow = $this->mainDatabase->table(TransferKey::TABLE)
            ->where("team_id", $this->teamManager->getTeam()->getId())
            ->where("user_id", $this->user->getId())
            ->fetch();

        return $accountRow !== null ? $accountRow->account_id : null;
    }

    /**
     * Generate new transfer key to current user's multiaccount and stores it into database for future login
     */
    private function generateNewTk(string $targetTeamSysName): TransferKey
    {
        $targetTeam = $this->teamManager->getBySysname($targetTeamSysName);

        if (!$targetTeam instanceof \Tymy\Module\Team\Model\Team) {
            $this->respondNotFound();
        }

        $accountId = $this->getAccountId();

        if (!$accountId) {
            $this->respondNotFound();
        }

        $targetUserId = $this->getTargetUserId($accountId, $targetTeam->getId());

        $newTk = sha1($accountId . random_int(0, 100000));
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
     */
    private function getTargetUserId(int $accountId, int $teamId): int
    {
        $row = $this->mainDatabase->table(TransferKey::TABLE)
            ->where("account_id", $accountId)
            ->where("team_id", $teamId)
            ->fetch();

        if (!$row instanceof \Nette\Database\Table\ActiveRow) {
            $this->responder->E4005_OBJECT_NOT_FOUND(Team::MODULE, $teamId);
        }

        return $row->user_id;
    }
}
