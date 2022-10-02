<?php

namespace Tymy\Module\User\Manager;

use Kdyby\Translation\Translator;
use Nette\Application\LinkGenerator;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Service\MailService;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Mapper\InvitationMapper;
use Tymy\Module\User\Model\Invitation;

/**
 * Description of InvitationManager
 *
 * @author kminekmatej, 25. 9. 2022, 21:25:38
 */
class InvitationManager extends BaseManager
{
    private UserManager $userManager;
    private MailService $mailService;
    private LinkGenerator $linkGenerator;
    private Translator $translator;

    public function __construct(ManagerFactory $managerFactory, UserManager $userManager, LinkGenerator $linkGenerator, Translator $translator)
    {
        parent::__construct($managerFactory);
        $this->userManager = $userManager;
        $this->linkGenerator = $linkGenerator;
        $this->translator = $translator;
    }

    protected function getClassName(): string
    {
        return Invitation::class;
    }

    protected function getScheme(): array
    {
        return InvitationMapper::scheme();
    }

    public function canEdit(BaseModel $entity, int $userId): bool
    {
        return in_array($userId, $this->userManager->getUserIdsWithPrivilege(Privilege::SYS("USR_CREATE")));
    }

    protected function allowCreate(?array &$data = null): void
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("USR_CREATE"))) {
            $this->responder->E4003_CREATE_NOT_PERMITTED("Invitiation");
        }

        $this->checkInputs($data);

        if (empty($data["firstName"]) && empty($data["lastName"]) || empty($data["email"])) {
            $this->respondBadRequest($this->translator->translate("team.errors.invitationAtLeast"));
        }
    }

    protected function allowDelete(?int $recordId): void
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("USR_CREATE"))) {
            $this->responder->E4004_DELETE_NOT_PERMITTED("Invitiation", $recordId);
        }
    }

    protected function allowRead(?int $recordId = null): void
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("USR_CREATE"))) {
            $this->responder->E4001_VIEW_NOT_PERMITTED("Invitiation", $recordId);
        }
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("USR_CREATE"))) {
            $this->responder->E4002_EDIT_NOT_PERMITTED("Invitiation", $recordId);
        }
    }

    /**
     * 
     * @param BaseModel $entity
     * @param int $userId
     * @return bool
     */
    public function canRead(BaseModel $entity, int $userId): bool
    {
        return in_array($userId, $this->userManager->getUserIdsWithPrivilege(Privilege::SYS("USR_CREATE")));
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $data["code"] = bin2hex(random_bytes(32));
        $data["validUntil"] = new DateTime("+ 30 days");

        $this->allowCreate($data);

        $createdRow = $this->createByArray($data);

        /* @var $invitation Invitation */
        $invitation = $this->map($createdRow);

        if ($invitation->getEmail()) {
            $this->notifyByEmail($invitation);
        }

        return $invitation;
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->allowDelete($resourceId);

        return parent::deleteRecord($resourceId);
    }

    public function getAllowedReaders(BaseModel $record): array
    {
        return $this->userManager->getUserIdsWithPrivilege(Privilege::SYS("USR_CREATE"));
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $invitation = parent::getById($resourceId);

        $this->allowRead($resourceId);

        return $invitation;
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowUpdate($resourceId, $data);

        parent::updateByArray($resourceId, $data);

        return $this->getById($resourceId, true);
    }

    private function notifyByEmail(Invitation $invitation)
    {
        $name = $invitation->getFirstName() || $invitation->getLastName() ? join(" ", [$invitation->getFirstName(), $invitation->getLastName()]) : null;

        $creator = $this->userManager->getById($this->user->getId());

        $this->mailService->mailInvitation($name, $invitation->getEmail(), $creator->getFullName() . "({$creator->getDisplayName()})", $this->linkGenerator->link(":Sign:ByInvite:default", ["invite" => $invitation->getCode()]), $invitation->getValidUntil());
    }

    /**
     * Get Invitation by its code
     * @param string $code
     * @return Invitation|null
     */
    public function getByCode(string $code): ?Invitation
    {
        return $this->map($this->database->table($this->getTable())->where("code", $code)->fetch());
    }
}
