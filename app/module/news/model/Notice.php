<?php

namespace Tymy\Module\News\Model;

use JsonSerializable;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\News\Mapper\NewsMapper;

/**
 * Description of Notice
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 29. 11. 2020
 */
class Notice extends BaseModel implements JsonSerializable
{
    public const TABLE = "news";
    public const MODULE = "news";

    private ?string $caption = null;
    private ?string $description = null;
    private ?string $languageCode = null;
    private DateTime $created;
    private ?string $team = null;

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getTeam(): ?string
    {
        return $this->team;
    }

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;
        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function setLanguageCode(?string $languageCode): static
    {
        $this->languageCode = $languageCode;
        return $this;
    }

    public function setCreated(DateTime $created): static
    {
        $this->created = $created;
        return $this;
    }

    public function setTeam(?string $team): static
    {
        $this->team = $team;
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
        return NewsMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
