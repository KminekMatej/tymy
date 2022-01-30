<?php
namespace Tymy\Module\Event\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Event\Mapper\EventTypeMapper;

/**
 * Description of EventType
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 8. 10. 2020
 */
class EventType extends BaseModel
{

    public const TABLE = "event_types";
    public const MODULE = "event";

    private string $code;
    private ?string $caption = null;
    private string $color = "827f76";   //default color if not set
    private ?int $preStatusSetId = null;
    private ?int $postStatusSetId = null;
    private ?string $mandatory = "FREE";
    private DateTime $updatedAt;
    private ?int $updatedById = null;
    private array $preStatusSet = [];
    private array $postStatusSet = [];

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getPreStatusSetId(): ?int
    {
        return $this->preStatusSetId;
    }

    public function getPostStatusSetId(): ?int
    {
        return $this->postStatusSetId;
    }

    public function getMandatory(): ?string
    {
        return $this->mandatory;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getUpdatedById(): ?int
    {
        return $this->updatedById;
    }

    public function getPreStatusSet(): array
    {
        return $this->preStatusSet;
    }

    public function getPostStatusSet(): array
    {
        return $this->postStatusSet;
    }

    public function setCode(string $code)
    {
        $this->code = $code;
        return $this;
    }

    public function setCaption(?string $caption)
    {
        $this->caption = $caption;
        return $this;
    }

    public function setColor(?string $color)
    {
        $this->color = $color;
        return $this;
    }

    public function setPreStatusSetId(?int $preStatusSetId)
    {
        $this->preStatusSetId = $preStatusSetId;
        return $this;
    }

    public function setPostStatusSetId(?int $postStatusSetId)
    {
        $this->postStatusSetId = $postStatusSetId;
        return $this;
    }

    public function setMandatory(?string $mandatory)
    {
        $this->mandatory = $mandatory;
        return $this;
    }

    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setUpdatedById(?int $updatedById)
    {
        $this->updatedById = $updatedById;
        return $this;
    }

    public function setPreStatusSet(array $preStatusSet)
    {
        $this->preStatusSet = $preStatusSet;
        return $this;
    }

    public function setPostStatusSet(array $postStatusSet)
    {
        $this->postStatusSet = $postStatusSet;
        return $this;
    }

    public function addPreStatusSet(Status $preStatusSet)
    {
        $this->preStatusSet[$preStatusSet->getCode()] = $preStatusSet;
        return $this;
    }

    public function addPostStatusSet(Status $postStatusSet)
    {
        $this->postStatusSet[$postStatusSet->getCode()] = $postStatusSet;
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return EventTypeMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            "preStatusSet" => $this->arrayToJson($this->preStatusSet),
            "postStatusSet" => $this->arrayToJson($this->postStatusSet),
        ];
    }
}
