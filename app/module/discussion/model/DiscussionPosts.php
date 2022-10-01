<?php

namespace Tymy\Module\Discussion\Model;

/**
 * Description of DiscussionPosts
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 17. 9. 2020
 */
class DiscussionPosts implements \JsonSerializable
{
    public function __construct(private Discussion $discussion, private int $currentPage, private int $numberOfPages, private array $posts)
    {
    }

    public function getDiscussion(): Discussion
    {
        return $this->discussion;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getNumberOfPages(): int
    {
        return $this->numberOfPages;
    }

    /**
     * @return mixed[]
     */
    public function getPosts(): array
    {
        return $this->posts;
    }

    /**
     * @return array<string, \Tymy\Module\Discussion\Model\Discussion>|array<string, mixed[]>
     */
    public function jsonSerialize(): array
    {
        return [
            "discussion" => $this->getDiscussion(),
            "paging" => [
                "currentPage" => $this->getCurrentPage(),
                "numberOfPages" => $this->getNumberOfPages(),
            ],
            "posts" => $this->getPosts()
        ];
    }
}
