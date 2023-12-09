<?php

namespace Tymy\Module\Settings\Manager;

use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Settings\Mapper\ICalItemMapper;
use Tymy\Module\Settings\Model\ICalItem;

/**
 * Description of ICalItemManager
 *
 * @author kminekmatej, 11. 9. 2022, 21:07:29
 */
class ICalItemManager extends BaseManager
{
    public function getClassName(): string
    {
        return ICalItem::class;
    }

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    public function getScheme(): array
    {
        return ICalItemMapper::scheme();
    }

    public function canEdit(BaseModel $entity, int $userId): bool
    {
        return false; //permissions are handled on parent element
    }

    public function canRead(BaseModel $entity, int $userId): bool
    {
        return false; //permissions are handled on parent element
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        return $this->map(parent::createByArray($data));
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        return parent::deleteRecord($subResourceId);
    }

    /**
     * @return mixed[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        return [];
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        return $this->getById($subResourceId);
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        parent::updateByArray($subResourceId, $data);
        return $this->getById($subResourceId, true);
    }
}
