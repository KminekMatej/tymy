<?php

namespace Tymy\Module\Team\Manager;

use Nette\Database\Table\ActiveRow;
use Nette\Database\IRow;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
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
    private string $teamFolder;

    public function __construct(string $teamFolder, ManagerFactory $managerFactory)
    {
        parent::__construct($managerFactory);
        $this->database = $this->mainDatabase;
        $this->teamFolder = $teamFolder;
    }

    /**
     *
     * @param ActiveRow $row
     * @param bool $force
     * @return Team
     */
    public function map(?IRow $row, bool $force = false): ?BaseModel
    {
        /* @var $team Team */
        $team = parent::map($row, $force);

        if (!$team) {
            return null;
        }

        $featuresRow = $row->related(Team::TABLE_FEATURES, "team_id")->fetch();

        if ($featuresRow) {
            $team->setFeatures(json_decode($featuresRow->features, true));
        }

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

        $featuresRow = $row->related(Team::TABLE_FEATURES, "team_id")->fetch();

        if ($featuresRow) {
            $features = json_decode($featuresRow->features);
            $sTeam->v2 = $features->v2 ?? false;
        }

        return $sTeam;
    }

    /**
     * Get SimpleTeam object from Team
     *
     * @param Team $team
     * @return SimpleTeam
     */
    public function toSimpleTeam(Team $team): SimpleTeam
    {
        return (new SimpleTeam())
                        ->setName($team->getName())
                        ->setSport($team->getSport())
                        ->setSysName($team->getName())
                        ->setDefaultLanguageCode($team->getDefaultLanguageCode())
                        ->setLanguages($team->getLanguages())
                        ->setV2($team->getFeatures()["v2"]);
    }

    /**
     * Get folder of currently logged team
     * @return string
     */
    public function getTeamFolder(): string
    {
        return sprintf($this->teamFolder, $this->getTeam()->getSysName());
    }

    /**
     * Get team by its sysname
     * @param string $sysname
     * @return Team|null
     */
    public function getBySysname(string $sysname): ?Team
    {
        return $this->map($this->database->table(Team::TABLE)->where("sys_name", $sysname)->fetch());
    }

    public function getTeam(): Team
    {
        return $this->getBySysname($this->teamSysName);
    }

    public function getTeamSimple(): SimpleTeam
    {
        return $this->mapSimple($this->database->table(Team::TABLE)->where("sys_name", $this->teamSysName)->fetch());
    }
    
    /**
     * Test whether team feature is allowed or not
     * 
     * @param string $feature
     * @return bool
     */
    public function isFeatureAllowed(string $feature): bool
    {
        return $this->getTeam()->getFeatures()[$feature];
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
        //todo
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
    }
}