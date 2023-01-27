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
    private ?DateTime $updatedAt = null;
    private ?int $updatedById = null;
    private ?int $order = null;
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

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getUpdatedById(): ?int
    {
        return $this->updatedById;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @return mixed[]
     */
    public function getPreStatusSet(): array
    {
        return $this->preStatusSet;
    }

    /**
     * @return mixed[]
     */
    public function getPostStatusSet(): array
    {
        return $this->postStatusSet;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;
        return $this;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function setPreStatusSetId(?int $preStatusSetId): static
    {
        $this->preStatusSetId = $preStatusSetId;
        return $this;
    }

    public function setPostStatusSetId(?int $postStatusSetId): static
    {
        $this->postStatusSetId = $postStatusSetId;
        return $this;
    }

    public function setMandatory(?string $mandatory): static
    {
        $this->mandatory = $mandatory;
        return $this;
    }

    public function setUpdatedAt(?DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setUpdatedById(?int $updatedById): static
    {
        $this->updatedById = $updatedById;
        return $this;
    }

    public function setOrder(?int $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param mixed[] $preStatusSet
     */
    public function setPreStatusSet(array $preStatusSet): static
    {
        $this->preStatusSet = $preStatusSet;
        return $this;
    }

    /**
     * @param mixed[] $postStatusSet
     */
    public function setPostStatusSet(array $postStatusSet): static
    {
        $this->postStatusSet = $postStatusSet;
        return $this;
    }

    public function addPreStatusSet(Status $preStatusSet): static
    {
        $this->preStatusSet[$preStatusSet->getCode()] = $preStatusSet;
        return $this;
    }

    public function addPostStatusSet(Status $postStatusSet): static
    {
        $this->postStatusSet[$postStatusSet->getCode()] = $postStatusSet;
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    public function getScheme(): array
    {
        return EventTypeMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return parent::jsonSerialize() + [
            "preStatusSet" => $this->arrayToJson(array_values($this->preStatusSet)),
            "postStatusSet" => $this->arrayToJson(array_values($this->postStatusSet)),
        ];
    }
}
