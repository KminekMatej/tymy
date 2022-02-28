<?php

namespace Tymy\Module\Attendance\Manager;

use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Tymy\Module\Attendance\Mapper\StatusMapper;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Attendance\Model\StatusSet;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Team\Manager\TeamManager;

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
    private TeamManager $teamManager;

    public function __construct(ManagerFactory $managerFactory, TeamManager $teamManager)
    {
        parent::__construct($managerFactory);
        $this->teamManager = $teamManager;
    }

    protected function getClassName(): string
    {
        return Status::class;
    }

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
     * Check if this status code is used in some attendance
     * @param string $statusCode
     * @return bool
     */
    public function isUsed(string $statusCode): bool
    {
        return $this->database->table(Attendance::TABLE)->whereOr([
                    "pre_status" => $statusCode,
                    "post_status" => $statusCode,
                ])->count() > 0;
    }

    /**
     * Compose and return correct folder of status set, using its ID
     *
     * @param int $statusSetId
     * @return string
     */
    public function getStatusSetFolder(int $statusSetId): string
    {
        return $this->teamManager->getTeamFolder() . "/attend_pics/$statusSetId";
    }

    /**
     * Get all status unique status codes
     * @return array
     */
    public function getAllStatusCodes(): array
    {
        return $this->database->table(Status::TABLE)->select("DISTINCT(code) AS codes")->fetchPairs(null, "codes");
    }

    /**
     * Get all status unique status codes
     * @return Status[] where key is code
     */
    public function getByStatusCode(): array
    {
        $statuses = [];
        foreach ($this->database->table(Status::TABLE)->fetchAll() as $row) {
            $statuses[$row->code] = $this->map($row);
        }
        return $statuses;
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
     *
     * @param int $statusSetId
     * @param string $code
     * @param string $imgB64
     */
    private function saveStatusImage(int $statusSetId, string $code, string $imgB64): void
    {
        $image = Image::fromString(base64_decode($imgB64));

        $image->resize(self::ICON_WIDTH, self::ICON_HEIGHT);

        $image->save($this->getStatusSetFolder($statusSetId) . "/$code.png");
    }

    public function getListUserAllowed($userId): array
    {
        //reading is not restricted
        return $this->mapAll($this->database->table($this->getTable())->fetchAll());
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $this->allowCreate($data);

        $data["updatedById"] = $this->user->getId();

        $createdRow = parent::createByArray($data);

        if (isset($data["image"])) {
            $this->saveStatusImage($data["statusSetId"], $data["code"], $data["image"]);
        }

        return $this->map($createdRow);
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->status = $this->getById($resourceId);

        $this->allowDelete($resourceId);

        $deleted = parent::deleteRecord($resourceId, $this->getTable());

        if ($deleted) {
            FileSystem::delete($this->getStatusSetFolder($this->status->getStatusSetId()) . "/{$this->status->getCode()}.png");
        }

        return $deleted;
    }

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

        $updated = parent::updateByArray($resourceId, $data);

        if (isset($data["image"])) {
            $code = $data["code"] ?? $this->status->getCode();
            $statusSetId = $data["statusSetId"] ?? $this->status->getStatusSetId();
            $this->saveStatusImage($statusSetId, $code, $data["image"]);
        }

        return $this->getById($resourceId);
    }
}
