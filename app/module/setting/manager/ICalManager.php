<?php

namespace Tymy\Module\Settings\Manager;

use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Poll\Mapper\ICalMapper;
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

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $this->checkInputs($data);

        $data["hash"] = bin2hex(random_bytes(16));

        if ($data["userId"] !== $this->user->getId()) {
            $this->respondForbidden();
        }

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
        $iCal = $this->getById($resourceId);

        if ($iCal->getUserId() !== $this->user->getId()) {
            $this->respondForbidden();
        }

        if ($data["userId"] !== $this->user->getId()) {
            $this->respondForbidden();
        }

        $this->updateByArray($resourceId, $data);

        return $this->getById($resourceId, true);
    }
}
