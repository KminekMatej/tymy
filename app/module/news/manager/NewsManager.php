<?php

namespace Tymy\Module\News\Manager;

use Exception;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\News\Mapper\NewsMapper;
use Tymy\Module\News\Model\Notice;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\User;

/**
 * Description of NewsManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 21. 02. 2021
 */
class NewsManager extends BaseManager
{
    public function __construct(ManagerFactory $managerFactory, private UserManager $userManager)
    {
        parent::__construct($managerFactory);
    }

    protected function getClassName(): string
    {
        return Notice::class;
    }

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    protected function getScheme(): array
    {
        return NewsMapper::scheme();
    }

    /**
     * Check edit permission
     * @param Notice $entity
     */
    public function canEdit($entity, int $userId): bool
    {
        return false;
    }

    /**
     * Check read permission
     * @param Notice $entity
     */
    public function canRead($entity, int $userId): bool
    {
        return true;
    }

    /**
     * Get user ids allowed to read given debt
     * @param Notice $record
     * @todo when its neccessary
     * @return int[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        assert($record instanceof Notice);
        return $this->getAllUserIds();
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        throw new Exception("Not implemented yet");
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        throw new Exception("Not implemented yet");
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new Exception("Not implemented yet");
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * @return \Tymy\Module\Core\Model\BaseModel[]
     */
    public function getListUserAllowed(): array
    {
        $user = $this->userManager->getById($this->user->getId());
        assert($user instanceof User);
        $limit = (new DateTime("2019-01-01"))->setTime(0, 0, 0);

        $news = $this->mapAll(
            $this->mainDatabase->table($this->getTable())
                        ->where("lc", in_array($user->getLanguage(), ["EN", "CZ"]) ? $user->getLanguage() : "CZ")
                        ->where("inserted >= ?", $user->getLastReadNews() < $limit ? $limit : $user->getLastReadNews())
                        ->where("team = ? OR team IS NULL OR FALSE", $this->teamSysName)
                        ->order("inserted")
                        ->fetchAll()
        );

        $this->userManager->updateLastReadNews($this->user->getId());

        return $news;
    }
}
