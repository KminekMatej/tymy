<?php

namespace Tymy\Module\Discussion\Model;

/**
 * Description of DiscussionPosts
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 17. 9. 2020
 */
class DiscussionPosts implements \JsonSerializable
{
    private Discussion $discussion;
    private int $currentPage;
    private int $numberOfPages;
    private array $posts;

    public function __construct(Discussion $discussion, int $currentPage, int $numberOfPages, array $posts)
    {
        $this->discussion = $discussion;
        $this->currentPage = $currentPage;
        $this->numberOfPages = $numberOfPages;
        $this->posts = $posts;
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

    public function getPosts(): array
    {
        return $this->posts;
    }

    public function jsonSerialize()
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
