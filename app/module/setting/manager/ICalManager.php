<?php

namespace Tymy\Module\Settings\Manager;

use Nette\Database\IRow;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Settings\Mapper\ICalMapper;
use Tymy\Module\Settings\Model\ICal;
use Tymy\Module\Settings\Model\ICalItem;

/**
 * Description of ICalManager
 *
 * @author kminekmatej, 11. 9. 2022, 15:49:12
 */
class ICalManager extends BaseManager
{
    protected function getClassName(): string
    {
        return ICal::class;
    }

    protected function getScheme(): array
    {
        return ICalMapper::scheme();
    }

    /**
     * @param ICal $entity
     * @param int $userId
     * @return bool
     */
    public function canEdit(BaseModel $entity, int $userId): bool
    {
        /* @var $entity ICal */
        return $entity->getUserId() == $userId;
    }

    public function canRead(BaseModel $entity, int $userId): bool
    {
        return $this->canEdit($entity, $userId);
    }

    public function map(?IRow $row, $force = false): ?BaseModel
    {
        if (!$row) {
            return null;
        }

        /* @var $iCal ICal */
        $iCal = parent::map($row, $force);

        $iCal->setStatusIds($row->related(ICalItem::TABLE)->fetchPairs(null, "status_id"));

        return $iCal;
    }

    /**
     * Load calendar of specific user
     * @param int $userId
     * @return ICal|null
     */
    public function getByUserId(int $userId): ?ICal
    {
        return $this->map($this->database->table($this->getTable())->where("user_id", $userId)->fetch());
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $data["hash"] = bin2hex(random_bytes(16));
        $data["userId"] = $this->user->getId();

        $this->checkInputs($data);

        return $this->map(parent::createByArray($data));
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        /* @var $iCal ICal */
        $iCal = $this->getById($resourceId);

        if ($iCal->getUserId() !== $this->user->getId()) {
            $this->respondForbidden();
        }

        return parent::deleteRecord($resourceId);
    }

    public function getAllowedReaders(BaseModel $record): array
    {
        /* @var $record ICal */
        return [$record->getUserId()];
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        /* @var $iCal ICal */
        $iCal = $this->getById($resourceId);

        if ($iCal->getUserId() !== $this->user->getId()) {
            $this->respondForbidden();
        }

        return $iCal;
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        /* @var $iCal ICal */
        $iCal = $this->getById($subResourceId);

        if ($iCal->getUserId() !== $resourceId) {
            $this->respondForbidden();
        }

        if ($resourceId !== $this->user->getId()) {
            $this->respondForbidden();
        }

        foreach (["hash", "userId"] as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }

        $this->updateByArray($subResourceId, $data);

        if (array_key_exists("items", $data)) {
            $this->updateItems($iCal, $data["items"]);
        }

        return $this->getById($subResourceId, true);
    }

    /**
     * Update statuses which events this ical shall display
     * @param int $exportId
     * @param int[] $statusIds
     * @return void
     */
    public function updateItems(ICal $iCal, array $statusIds): void
    {
        $existingStatuses = $iCal->getStatusIds();
        $newStatuses = $statusIds;

        $statusesToAdd = array_diff($newStatuses, $existingStatuses);
        $statusesToRemove = array_diff($existingStatuses, $newStatuses);

        // DELETE REMOVED
        if (!empty($statusesToRemove)) {
            $this->database->table(ICalItem::TABLE)->where('ical_id', $iCal->getId())->where('status_id IN ?', $statusesToRemove)->delete();
        }

        // INSERT NEW
        if (!empty($statusesToAdd)) {
            $inserts = [];

            foreach ($statusesToAdd as $statusToAdd) {
                $inserts[] = [
                    "created_user_id" => $this->user->getId(),
                    "ical_id" => $iCal->getId(),
                    "status_id" => $statusToAdd,
                ];
            }

            $this->database->table(ICalItem::TABLE)->insert($inserts);
        }
    }
}