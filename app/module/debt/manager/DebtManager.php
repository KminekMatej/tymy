<?php

namespace Tymy\Module\Debt\Manager;

use Nette\Database\IRow;
use Nette\Utils\Strings;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Field;
use Tymy\Module\Debt\Mapper\DebtMapper;
use Tymy\Module\Debt\Model\Debt;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of DebtManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 29. 11. 2020
 */
class DebtManager extends BaseManager
{
    public const TYPE_USER = "user";
    public const TYPE_TEAM = "team";
    public const TYPE_OTHER = "other";

    private ?Debt $debt = null;

    public function __construct(ManagerFactory $managerFactory, private UserManager $userManager)
    {
        parent::__construct($managerFactory);
    }

    public function map(?IRow $row, $force = false): ?BaseModel
    {
        if ($row === null) {
            return null;
        }

        $debt = parent::map($row, $force);
        assert($debt instanceof Debt);

        $isTeamDebtManager = $this->user->isAllowed($this->user->getId(), Privilege::SYS("DEBTS_TEAM"));

        $isDebtor = //this user is debtor if its his own debt, or if its team debt and he can administrate team debts
                ($debt->getDebtorType() == self::TYPE_USER && $debt->getDebtorId() == $this->user->getId()) ||
                ($debt->getDebtorType() == self::TYPE_TEAM && $isTeamDebtManager);

        $isPayee = $debt->getPayeeType() == self::TYPE_USER && $debt->getPayeeId() == $this->user->getId();

        $debt->setIsTeamDebt($debt->getPayeeType() == self::TYPE_TEAM); //it is considered as team debt, if the receiver is team
        $debt->setCanEdit(($debt->getIsTeamDebt() && $isTeamDebtManager) || $isPayee); //can edit if user is receiver or team is receiver & he can manage team debts
        $debt->setCanRead($debt->getCanEdit() || $isDebtor); //can read if can edit (obviously), but also when he is the actual debtor (read-only)
        $debt->setCanSetSentDate($isDebtor); //can mark debt as sent, if he is debtor
        $debt->setWebName(Strings::webalize($debt->getId() . "-" . $debt->getCaption()));

        $class = $debt->getPayeeId() == $this->user->getId() || (empty($debt->getPayeeId()) && $debt->getDebtorId() != $this->user->getId()) ? "debt incoming" : "debt pending";
        if (!empty($debt->getPaymentReceived())) {
            $class = "debt received";
        } elseif (!empty($debt->getPaymentSent())) {
            $class = "debt sent";
        }
        $debt->setClass($class);

        $debt->setPayee($debt->getPayeeId() > 0 ? $this->userManager->getById($debt->getPayeeId()) : null);
        $debt->setDebtor($debt->getDebtorId() > 0 ? $this->userManager->getById($debt->getDebtorId()) : null);
        $debt->setAuthor($debt->getCreatedUserId() ? $this->userManager->getById($debt->getCreatedUserId()) : null);

        $debt->setDebtorCallName($debt->getDebtorType() == self::TYPE_USER ? $debt->getDebtor()->getDisplayName() : "TÝM");
        $debt->setPayeeCallName($debt->getPayee() ? $debt->getPayee()->getDisplayName() : "TÝM");

        if ($debt->getCanSetSentDate()) {
            $displayString = $debt->getDebtorType() == "team" ? $debt->getDebtorCallName() . " → " . $debt->getPayeeCallName() : "→ " . $debt->getPayeeCallName();
        } else {
            $displayString = $debt->getPayeeType() == "team" ? $debt->getDebtorCallName() . " → " . $debt->getPayeeCallName() : $debt->getDebtorCallName();
        }
        $debt->setDisplayString($displayString);

        if ($isDebtor && empty($debt->getPaymentSent()) && empty($debt->getPaymentReceived())) {
            $debt->setPaymentPending(true);
        }

        return $debt;
    }

    public function getClassName(): string
    {
        return Debt::class;
    }

    /**
     * @return Field[]
     */
    public function getScheme(): array
    {
        return DebtMapper::scheme();
    }

    /**
     * Check edit permission
     * @param Debt $entity
     */
    public function canEdit($entity, int $userId): bool
    {
        return $this->canEditDebtData($entity->getPayeeId(), $entity->getPayeeType());
    }

    /**
     * Inner function to check whether user can edit debt with specified data - to be used before the debt is actually created
     *
     * @param int|null $payeeId (null for team payee)
     * @param string $payeeType
     * @return bool
     */
    private function canEditDebtData(?int $payeeId, string $payeeType): bool
    {
        $isTeamDebt = $payeeType == self::TYPE_TEAM;
        return $isTeamDebt ? $this->user->isAllowed($this->user->getId(), Privilege::SYS("DEBTS_TEAM")) : $payeeId == $this->user->getId();
    }

    /**
     * Check read permission
     * @param Debt $entity
     */
    public function canRead($entity, int $userId): bool
    {
        return ($entity->getDebtorType() == self::TYPE_USER && $entity->getDebtorId() === $userId) || $entity->getCanEdit();
    }

    /**
     * Get user ids allowed to read given debt
     * @param Debt $record
     * @todo when its neccessary
     * @return int[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        assert($record instanceof Debt);
        return [];
    }

    protected function allowDelete(?int $recordId): void
    {
        if (!$this->debt->getCanEdit()) {
            $this->responder->E4004_DELETE_NOT_PERMITTED(Debt::MODULE, $recordId);
        }
    }

    protected function allowRead(?int $recordId = null): void
    {
        if (!$this->debt->getCanRead()) {
            $this->responder->E4001_VIEW_NOT_PERMITTED(Debt::MODULE, $recordId);
        }
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        if (!$this->debt->getCanRead()) {   //check getRead() permissions - cause debtor can be editing paymentSentDate
            $this->responder->E4001_VIEW_NOT_PERMITTED(Debt::MODULE, $recordId);
        }

        $this->autosetType($data, $this->debt);

        if ($this->debt->getCanEdit()) {
            if (!$this->debt->getCanSetSentDate()) {
                unset($data["paymentSent"]); //user can edit everything except payment sent date
            }

            if (array_key_exists("caption", $data) && empty($data["caption"])) { // Cannot set blank caption
                $this->responder->E4014_EMPTY_INPUT("caption");
            }
            if (array_key_exists("amount", $data) && $data["amount"] < 0) { // Amount below zero
                $this->respondBadRequest("Amount cannot be below zero");
            }
        }
    }

    protected function allowCreate(?array &$data = null): void
    {
        $data["createdUserId"] = $this->user->getId();

        $this->autosetType($data);

        if (!$this->canEditDebtData($data["payeeId"], $data["payeeType"])) {
            $this->respondForbidden("Cannot create debts for anyone else than myself");
        }

        $this->checkInputs($data);

        if ($data["amount"] < 0) {
            $this->respondBadRequest("Amount cannot be below zero");
        }
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $this->allowCreate($data);

        $created = parent::createByArray($data);

        return $this->map($created);
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->debt = $this->getById($resourceId);

        $this->allowDelete($resourceId);

        return parent::deleteRecord($resourceId);
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->debt = $this->getById($resourceId);

        $this->allowRead($resourceId);

        return $this->debt;
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->debt = $this->getById($resourceId);

        $this->allowUpdate($resourceId, $data);

        if ($this->debt->getCanEdit()) {
            //full edit
            parent::updateByArray($resourceId, $data);
        } elseif ($this->debt->getCanSetSentDate()) {//user can edit only sent date (hes a debtor)
            //paymentSent can edit only
            if (isset($data["paymentSent"]) && $data["paymentSent"] !== $this->debt->getPaymentSent()) {  //payment sent date has been changed
                parent::updateByArray($resourceId, ["paymentSent" => $data["paymentSent"]]);
            }
        }

        return $this->getById($resourceId);
    }

    /**
     * Autoset debtor id and debtor type
     */
    private function autosetType(array &$debtData, ?Debt $originalDebt = null): void
    {
        if ($originalDebt !== null) {
            $debtData["debtorId"] ??= $originalDebt->getDebtorId();
            $debtData["payeeId"] ??= $originalDebt->getPayeeId();
        }

        $debtData["debtorType"] = empty($debtData["debtorId"]) ? self::TYPE_TEAM : self::TYPE_USER;
        $debtData["payeeType"] = empty($debtData["payeeId"]) ? self::TYPE_TEAM : self::TYPE_USER;

        if ($debtData["debtorType"] === self::TYPE_TEAM) {
            $debtData["debtorId"] = null;
        }
        if ($debtData["payeeType"] === self::TYPE_TEAM) {
            $debtData["payeeId"] = null;
        }
    }

    public function getListUserAllowed()
    {
        $includeTeamDebts = $this->user->isAllowed($this->user->getId(), Privilege::SYS("DEBTS_TEAM"));

        if ($includeTeamDebts) {// debts for me or by me or team
            return $this->mapAll($this->database->table($this->getTable())->whereOr([
                                "debtor_id" => $this->user->getId(),
                                "payee_id" => $this->user->getId(),
                                "payee_type" => self::TYPE_TEAM,
                                "debtor_type" => self::TYPE_TEAM,
                            ])->fetchAll());
        } else {// debts only for me as user or by me as anything
            return $this->mapAll($this->database->table($this->getTable())
                                    ->where("(debtor_id=? AND debtor_type=?) OR (payee_id=? AND payee_type=?)", $this->user->getId(), self::TYPE_USER, $this->user->getId(), self::TYPE_USER)
                                    ->fetchAll());
        }
    }

    /**
     * Get sum of all debts with pending payment
     *
     * @param Debt[] $debts
     */
    public function getWarnings(array $debts): int
    {
        $count = 0;
        foreach ($debts as $debt) {
            assert($debt instanceof Debt);
            if ($debt->getPaymentPending()) {
                $count++;
            }
        }

        return $count;
    }
}
