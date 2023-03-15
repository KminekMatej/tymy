<?php

namespace Tymy\Module\Attendance\Manager;

use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Tymy\Module\Attendance\Mapper\StatusMapper;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Attendance\Model\StatusSet;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Field;
use Tymy\Module\Event\Model\EventType;

use const TEAM_DIR;

/**
 * Description of StatusManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 8. 10. 2020
 */
class StatusManager extends BaseManager
{
    public const ICON_WIDTH = 250;
    public const ICON_HEIGHT = 250;

    private ?Status $status = null;
    private array $simpleCache = [];

    protected function getClassName(): string
    {
        return Status::class;
    }

    /**
     * @return Field[]
     */
    protected function getScheme(): array
    {
        return StatusMapper::scheme();
    }

    /** @todo */
    public function canEdit($entity, $userId): bool
    {
        return false;
    }

    public function canRead($entity, $userId): bool
    {
        return true;
    }

    private function dropSimpleCache(): void
    {
        $this->simpleCache = [];
    }

    public function map(?IRow $row, $force = false): ?BaseModel
    {
        if ($row === null) {
            return null;
        }
        assert($row instanceof ActiveRow);

        $status = parent::map($row, $force);
        assert($status instanceof Status);

        $status->setStatusSetName($row->ref(StatusSet::TABLE)->name);

        return $status;
    }

    protected function allowCreate(?array &$data = null): void
    {
        $this->allowAdmin();

        $this->checkInputs($data);

        if (!$this->exists($data["statusSetId"], StatusSet::TABLE)) {
            $this->responder->E4005_OBJECT_NOT_FOUND("Status set", $data["statusSetId"]);
        }
        if (strlen($data["code"]) > 3) {
            $this->respondBadRequest("Code must be max 3 chars long");
        }
    }

    /**
     * Compose and return correct folder of status set, using its ID
     */
    public function getStatusSetFolder(int $statusSetId): string
    {
        $dir = TEAM_DIR . "/attend_pics/$statusSetId";
        if (!file_exists($dir) || !is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Get all status unique status codes
     * @return mixed[]
     */
    public function getAllStatusCodes(): array
    {
        return $this->database->table(Status::TABLE)->select("DISTINCT(code) AS codes")->fetchPairs(null, "codes");
    }

    /**
     * Get all pre and post statuses by corresponding event type id
     * @return array In the form of ["pre" => [..array of Status objects..], "post" => [..array of Status objects..]]
     */
    public function getByEventTypeId(int $eventTypeId): array
    {
        if (!array_key_exists($eventTypeId, $this->simpleCache)) {
            $this->simpleCache[$eventTypeId] = [
                StatusSet::PRE => $this->mapAllWithCode($this->database->table(Status::TABLE)->where(".status_set:event_types(pre_status_set).id", $eventTypeId)->order("order")->fetchPairs("code")),
                StatusSet::POST => $this->mapAllWithCode($this->database->table(Status::TABLE)->where(".status_set:event_types(post_status_set).id", $eventTypeId)->order("order")->fetchAll()),
            ];
        }
        return $this->simpleCache[$eventTypeId];
    }

    /**
     * Map all rows to array of statuses where key is status code
     * @param ActiveRow[] $rows
     * @return Status[]
     */
    public function mapAllWithCode(array $rows): array
    {
        $ret = [];
        foreach ($rows as $row) {
            $ret[$row->code] = $this->map($row);
        }
        return $ret;
    }

    /**
     * Get all status unique status codes
     * @return Status[] where key is code
     */
    public function getByStatusCode(): array
    {
        $statuses = [];
        foreach ($this->database->table(Status::TABLE)->order("order")->fetchAll() as $row) {
            $statuses[$row->code] = $this->map($row);
        }
        return $statuses;
    }

    /**
     * Get all statuses, which are used in any event_type for pre-status purposes
     * @return Status[] where key is code
     */
    public function getAllPreStatuses(): array
    {
        $setIds = $this->database->table(EventType::TABLE)->select("DISTINCT(pre_status_set_id) AS setIds")->fetchPairs(null, "setIds");

        $preStatuses = $this->database->table(Status::TABLE)->where("status_set.id", $setIds)->order("order")->fetchAll();

        return $this->mapAll($preStatuses);
    }

    /**
     * Load status ids related to specific event types - first PRE then POST statuses
     * @param int $eventTypeId
     * @return int[]
     */
    public function getStatusIdsOfEventType(int $eventTypeId): array
    {
        return array_merge(
            $this->database->table(StatusSet::TABLE)->select(":status.id AS statusId")->where(":event_types(pre_status_set).id", $eventTypeId)->order(":status.order")->fetchPairs(
                null,
                "statusId"
            ),
            $this->database->table(StatusSet::TABLE)->select(":status.id AS statusId")->where(":event_types(post_status_set).id", $eventTypeId)->order(":status.order")->fetchPairs(
                null,
                "statusId"
            )
        );
    }

    protected function allowRead(?int $recordId = null): void
    {
        $this->status = $this->getById($recordId);

        if (!$this->canRead($this->status, $this->user->getId())) {
            $this->respondForbidden();
        }
    }

    protected function allowDelete(?int $recordId): void
    {
        $this->allowAdmin();

        if (empty($this->status)) {
            $this->responder->E4005_OBJECT_NOT_FOUND("Status", $recordId);
        }

        if ($this->isUsed($recordId)) {
            $this->respondBadRequest("Status set is used, cannot be deleted");
        }
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        $this->allowAdmin();

        if (empty($this->status)) {
            $this->respondNotFound();
        }

        if (isset($data["caption"]) && empty($data["caption"])) {
            $this->responder->E4014_EMPTY_INPUT("caption");
        }
        if (isset($data["statusSetId"]) && !$this->exists($data["statusSetId"], StatusSet::TABLE)) {
            $this->responder->E4005_OBJECT_NOT_FOUND("Status set", $data["statusSetId"]);
        }

        if (isset($data["code"]) && strlen($data["code"]) > 3) {
            $this->respondBadRequest("Code must be max 3 chars long");
        }
    }

    /**
     * Save image of status, specified as base64 string
     */
    private function saveStatusImage(int $statusSetId, string $code, string $imgB64): void
    {
        $image = Image::fromString(base64_decode($imgB64));

        $image->resize(self::ICON_WIDTH, self::ICON_HEIGHT);

        $image->save($this->getStatusSetFolder($statusSetId) . "/$code.png");
    }

    /**
     * @return BaseModel[]
     */
    public function getListUserAllowed($userId): array
    {
        //reading is not restricted
        return $this->mapAll($this->database->table($this->getTable())->order("order")->fetchAll());
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $this->allowCreate($data);

        $data["updatedById"] = $this->user->getId();

        $createdRow = parent::createByArray($data);

        if (isset($data["image"])) {
            $this->saveStatusImage($data["statusSetId"], $data["code"], $data["image"]);
        }

        $this->dropSimpleCache();

        return $this->map($createdRow);
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->status = $this->getById($resourceId);

        $this->allowDelete($resourceId);

        $deleted = parent::deleteRecord($resourceId, $this->getTable());

        if ($deleted !== 0) {
            FileSystem::delete($this->getStatusSetFolder($this->status->getStatusSetId()) . "/{$this->status->getCode()}.png");
        }

        $this->dropSimpleCache();

        return $deleted;
    }

    /**
     * @return int[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        return $this->getAllUserIds(); //everyone can read
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowRead($resourceId);

        return $this->status;
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->status = $this->getById($resourceId);

        $data["updatedById"] = $this->user->getId();

        $this->allowUpdate($resourceId, $data);

        parent::updateByArray($resourceId, $data);

        if (isset($data["image"])) {
            $code = $data["code"] ?? $this->status->getCode();
            $statusSetId = $data["statusSetId"] ?? $this->status->getStatusSetId();
            $this->saveStatusImage($statusSetId, $code, $data["image"]);
        }

        $this->dropSimpleCache();

        return $this->getById($resourceId);
    }

    /**
     * Check this status set is used, by checking if any of its statuses is used
     */
    public function isUsed(int $statusId): bool
    {
        return $this->database->table(Attendance::TABLE)->whereOr([
                "pre_status_id" => $statusId,
                "post_status_id" => $statusId,
            ])->count() > 0;
    }
}
