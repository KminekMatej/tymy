<?php

namespace Tymy\Module\Settings\Manager;

use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Settings\Mapper\ICalMapper;
use Tymy\Module\Settings\Model\ICal;

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
        
        if($iCal->getUserId() !== $this->user->getId()){
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
        
        if($iCal->getUserId() !== $this->user->getId()){
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

        return $this->getById($subResourceId, true);
    }
}
