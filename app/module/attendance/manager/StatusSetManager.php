<?php

namespace Tymy\Module\Attendance\Manager;

use Nette\Database\IRow;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Tymy\Module\Attendance\Mapper\StatusSetMapper;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Attendance\Model\StatusSet;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Discussion\Model\Discussion;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of StatusSetManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4. 11. 2020
 */
class StatusSetManager extends BaseManager
{
    private ?StatusSet $statusSet;
    private StatusManager $statusManager;
    private UserManager $userManager;

    public function __construct(ManagerFactory $managerFactory, StatusManager $statusManager, UserManager $userManager)
    {
        parent::__construct($managerFactory);
        $this->statusManager = $statusManager;
        $this->userManager = $userManager;
    }

    public function map(?IRow $row, $force = false): ?BaseModel
    {
        /* @var $statusSet StatusSet */
        $statusSet = parent::map($row, $force);

        $statusSet->setWebname($statusSet->getId() . "-" . Strings::webalize($statusSet->getName()));

        foreach ($row->related(Status::TABLE) as $statusRow) {
            $statusSet->addStatus($this->statusManager->map($statusRow));
        }

        return $statusSet;
    }

    protected function getClassName(): string
    {
        return StatusSet::class;
    }

    protected function getScheme(): array
    {
        return StatusSetMapper::scheme();
    }

    protected function allowCreate(?array &$data = null): void
    {
        $this->allowAdmin();

        $this->checkInputs($data);
    }

    protected function allowDelete(?int $recordId): void
    {
        $this->allowAdmin();

        $this->statusSet = $this->getById($recordId);

        if (empty($this->statusSet)) {
            $this->respondNotFound();
        }

        if ($this->isUsed($recordId)) {
            $this->respondBadRequest("Status set is used, cannot be deleted");
        }
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        $this->allowAdmin();

        $this->statusSet = $this->getById($recordId);

        if (empty($this->statusSet)) {
            $this->respondNotFound();
        }

        if (array_key_exists("name", $data) && empty($data["name"])) {
            $this->responder->E4014_EMPTY_INPUT("name");
        }
    }

    /**
     * Check this status set is used, by checking if any of its statuses is used
     *
     * @param int $statusSetId
     * @return bool
     */
    public function isUsed(int $statusSetId): bool
    {
        $ids = $this->database->table(Status::TABLE)->where("status_set_id", $statusSetId)->fetchPairs(null, "id");

        return $this->database->table(Attendance::TABLE)->whereOr([
                "pre_status_id IN (?)" => $ids,
                "post_status_id IN (?)" => $ids,
            ])->count() > 0;
    }

    public function canEdit($entity, $userId): bool
    {
        return $this->userManager->isAdmin($userId);
    }

    public function canRead($entity, $userId): bool
    {
        return true; //everyone logged in can read satus set
    }

    /**
     * Create status set folder
     * @param int $statusSetId
     */
    private function createStatusSetDir(int $statusSetId): void
    {
        FileSystem::createDir($this->statusManager->getStatusSetFolder($statusSetId));
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $this->allowCreate($data);

        $created = parent::createByArray($data);

        if ($created) {
            $this->createStatusSetDir($created->id);
        }

        return $this->map($created);
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->allowDelete($resourceId);

        return parent::deleteRecord($resourceId);
    }

    public function getAllowedReaders(BaseModel $record): array
    {
        return $this->getAllUserIds();
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        return $this->getById($resourceId);
    }

    /**
     * Get array of StatusSet objects which user is allowed to read
     * @param int $userId
     * @return Discussion[]
     */
    public function getListUserAllowed($userId)
    {
        return $this->mapAll($this->database->table($this->getTable())->fetchAll());
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowUpdate($resourceId, $data);

        parent::updateByArray($resourceId, $data);

        $updatedSS = $this->getById($resourceId);

        return $updatedSS;
    }
}
