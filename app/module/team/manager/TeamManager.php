<?php

namespace Tymy\Module\Team\Manager;

use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\Http\Request;
use Nette\NotImplementedException;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\Team\Mapper\TeamMapper;
use Tymy\Module\Team\Model\SimpleTeam;
use Tymy\Module\Team\Model\Team;

/**
 * Description of TeamManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
class TeamManager extends BaseManager
{
    public const DEFAULT_SKIN = "black-panther";
    private Team $team;

    public function __construct(string $teamFolder, public array $allSkins, ManagerFactory $managerFactory, private Request $httpRequest)
    {
        parent::__construct($managerFactory);
        $this->database = $this->mainDatabase;
    }

    public function map(?IRow $row, $force = false): ?BaseModel
    {
        if ($row === null) {
            return null;
        }

        /* @var $team Team */
        /* @var $row ActiveRow */
        $team = parent::map($row, $force);

        $team->setExtendedSysName(str_replace(".tymy.cz", "", $this->httpRequest->getUrl()->getHost()));

        return $team;
    }

    public function mapSimple($row): SimpleTeam
    {
        $sTeam = new SimpleTeam();

        $sTeam->sysName = $row->sys_name;
        $sTeam->name = $row->name;
        $sTeam->sport = $row->sport;
        $sTeam->languages = explode(",", $row->languages);
        $sTeam->defaultLanguageCode = $row->default_lc;

        return $sTeam;
    }

    /**
     * Get SimpleTeam object from Team
     */
    public function toSimpleTeam(Team $team): SimpleTeam
    {
        return (new SimpleTeam())
                ->setName($team->getName())
                ->setSport($team->getSport())
                ->setSysName($team->getName())
                ->setDefaultLanguageCode($team->getDefaultLanguageCode())
                ->setLanguages($team->getLanguages());
    }

    /**
     * Get team by its sysname
     */
    public function getBySysname(string $sysname): ?Team
    {
        return $this->map($this->database->table(Team::TABLE)->where("sys_name", $sysname)->fetch());
    }

    public function getTeam(): Team
    {
        if (!isset($this->team)) {
            $this->team = $this->getBySysname($this->teamSysName);
        }
        return $this->team;
    }

    public function getTeamSimple(): SimpleTeam
    {
        return $this->mapSimple($this->database->table(Team::TABLE)->where("sys_name", $this->teamSysName)->fetch());
    }

    protected function getClassName(): string
    {
        return Team::class;
    }

    protected function getScheme(): array
    {
        return TeamMapper::scheme();
    }

    public function canEdit($entity, $userId): bool
    {
        return true;
    }

    public function canRead($entity, $userId): bool
    {
        return true;
    }

    public function getAllowedReaders(BaseModel $record): array
    {
        return [];
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
        if ($recordId !== $this->getTeam()->getId()) {
            $this->respondForbidden();
        }

        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("TEAM_UPDATE"))) {
            $this->respondForbidden();
        }
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        throw new NotImplementedException("Cannot create team");
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        throw new NotImplementedException("Cannot create team");
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new NotImplementedException("Cannot create team");
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowUpdate($resourceId);

        parent::updateByArray($resourceId, $data);

        return $this->getById($resourceId);
    }

    /**
     * Get maximum available downloads folder size
     */
    public function getMaxDownloadSize(Team $team): int
    {
        $bytesFree = 1024 * 1024 * 10; //10 MB for free teams
        $bytesFull = 1024 * 1024 * 100; //100 MB for full teams

        return str_contains($team->getTariff(), "FULL") ? $bytesFull : $bytesFree;
    }
}
