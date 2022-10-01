<?php

namespace Tymy\Module\Discussion\Model;

use JsonSerializable;
use Nette\Utils\DateTime;

/**
 * Description of NewInfo
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 16. 9. 2020
 */
class NewInfo implements JsonSerializable
{
    public function __construct(private int $discussionId, private int $newsCount, private ?\Nette\Utils\DateTime $lastVisit = null)
    {
    }

    public function getDiscussionId(): int
    {
        return $this->discussionId;
    }

    public function getNewsCount(): int
    {
        return $this->newsCount;
    }

    public function getLastVisit(): ?DateTime
    {
        return $this->lastVisit;
    }

    public function jsonSerialize()
    {
        return [
            "discussionId" => $this->getDiscussionId(),
            "newsCount" => $this->getNewsCount(),
            "lastVisit" => $this->getLastVisit(),
        ];
    }
}
