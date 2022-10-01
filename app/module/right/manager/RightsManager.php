<?php

namespace Tymy\Module\Right\Manager;

use Nette\NotImplementedException;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Right\Mapper\RightMapper;
use Tymy\Module\Right\Model\Right;

/**
 * Description of RightManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 2. 9. 2020
 */
class RightManager extends BaseManager
{
    /**
     * @return \Tymy\Module\Core\Model\BaseModel[]
     */
    public function getList($idList = null, $idField = "id", ?int $limit = null, ?int $offset = null, ?string $order = null): array
    {
        return $this->mapAll($this->database->query("SELECT " . Permission::TABLE . ".*  FROM " . Right::TABLE
                                . " LEFT JOIN " . Permission::TABLE . " ON " . Right::TABLE . ".right_type=" . Permission::TABLE . ".right_type AND " . Right::TABLE . ".right_name=" . Permission::TABLE . ".name"
                                . " WHERE " . Permission::TABLE . ".right_type = ?", Permission::TYPE_USER)->fetchAll());
    }

    public function getListAllowed(?int $userId = null)
    {
        if (!$userId) {
            return [];
        }

        return $this->mapAll($this->database->query("SELECT " . Permission::TABLE . ".* FROM " . Right::TABLE
                                . " LEFT JOIN " . Permission::TABLE . " ON " . Right::TABLE . ".right_type=" . Permission::TABLE . ".right_type AND " . Right::TABLE . ".right_name=" . Permission::TABLE . ".name"
                                . " WHERE " . Right::TABLE . ".user_id = ? AND " . Permission::TABLE . ".right_type = ? AND " . Right::TABLE . ".allowed = ?", $userId, Permission::TYPE_USER, "YES")->fetchAll());
    }

    protected function getClassName(): string
    {
        return Right::class;
    }

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    protected function getScheme(): array
    {
        return RightMapper::scheme();
    }

    public function canEdit($entity, $userId): bool
    {
        return false;
    }

    public function canRead($entity, $userId): bool
    {
        return true;
    }

    public function getAllowedReaders(BaseModel $record): array
    {
        return false; //todo
    }

    protected function allowCreate(?array &$data = null): void
    {
        //todo
    }

    protected function allowDelete(?int $recordId): void
    {
        //todo
    }

    protected function allowRead(?int $recordId = null): void
    {
        //todo
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        //todo
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        throw new NotImplementedException("Not implemented yet");
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        throw new NotImplementedException("Not implemented yet");
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new NotImplementedException("Not implemented yet");
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new NotImplementedException("Not implemented yet");
    }
}
